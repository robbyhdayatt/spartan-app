<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMutation;
use App\Models\Inventory;
use App\Models\Part;
use App\Models\Gudang;
use App\Models\Rak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockMutationController extends Controller
{
    public function index()
    {
        $mutations = StockMutation::with(['part', 'gudangAsal', 'gudangTujuan', 'createdBy'])->latest()->paginate(15);
        return view('admin.stock_mutations.index', compact('mutations'));
    }

    public function create()
    {
        $user = Auth::user();
        $gudangsTujuan = Gudang::where('is_active', true)->orderBy('nama_gudang')->get();

        $allowedRoles = ['Super Admin', 'Manajer Area'];

        if (in_array($user->jabatan->nama_jabatan, $allowedRoles)) {
            $gudangsAsal = Gudang::where('is_active', true)->orderBy('nama_gudang')->get();
        } else {
            $gudangsAsal = Gudang::where('id', $user->gudang_id)->get();
        }

        // Kita tidak lagi mengirimkan $parts dari sini
        return view('admin.stock_mutations.create', compact('gudangsAsal', 'gudangsTujuan'));
    }

    // TAMBAHKAN METHOD BARU INI UNTUK AJAX
    /**
     * API to get parts that have stock in a specific warehouse.
     */
    public function getPartsWithStock(Gudang $gudang)
    {
        $partIds = Inventory::where('gudang_id', $gudang->id)
            ->where('quantity', '>', 0)
            ->pluck('part_id')
            ->unique();

        $parts = Part::whereIn('id', $partIds)->orderBy('nama_part')->get();

        return response()->json($parts);
    }

    // API endpoint for dynamic dropdown
    public function getRaksByGudang(Gudang $gudang)
    {
        $raks = Rak::where('gudang_id', $gudang->id)->where('is_active', true)->get();
        return response()->json($raks);
    }

    public function store(Request $request)
    {
        $this->authorize('can-manage-stock');
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'gudang_asal_id' => 'required|exists:gudangs,id',
            'rak_asal_id' => 'required|exists:raks,id',
            'gudang_tujuan_id' => 'required|exists:gudangs,id|different:gudang_asal_id',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        // Check if stock is sufficient
        $sourceInventory = Inventory::where('rak_id', $validated['rak_asal_id'])
            ->where('part_id', $validated['part_id'])->first();

        if (!$sourceInventory || $sourceInventory->quantity < $validated['jumlah']) {
            return back()->with('error', 'Stok di rak asal tidak mencukupi untuk mutasi.')->withInput();
        }

        StockMutation::create([
            'nomor_mutasi' => $this->generateMutationNumber(),
            'part_id' => $validated['part_id'],
            'gudang_asal_id' => $validated['gudang_asal_id'],
            'rak_asal_id' => $validated['rak_asal_id'],
            'gudang_tujuan_id' => $validated['gudang_tujuan_id'],
            'jumlah' => $validated['jumlah'],
            'keterangan' => $validated['keterangan'],
            'status' => 'PENDING_APPROVAL',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.stock-mutations.index')->with('success', 'Permintaan mutasi berhasil dibuat.');
    }

    public function approve(StockMutation $stockMutation)
    {
        $this->authorize('approve-mutation', $stockMutation);

        if ($stockMutation->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Hanya permintaan yang berstatus PENDING APPROVAL yang bisa diproses.');
        }

        DB::beginTransaction();
        try {
            // Cek stok di rak asal
            $sourceInventory = Inventory::where('rak_id', $stockMutation->rak_asal_id)
                ->where('part_id', $stockMutation->part_id)
                ->first();

            if (!$sourceInventory || $sourceInventory->quantity < $stockMutation->jumlah) {
                throw new \Exception('Stok di rak asal tidak mencukupi untuk mutasi.');
            }

            // Kurangi stok dari rak asal
            $stokSebelum = $sourceInventory->quantity;
            $sourceInventory->decrement('quantity', $stockMutation->jumlah);

            // Catat pergerakan stok KELUAR dari rak asal
            // (Kode pencatatan stock movement bisa ditambahkan di sini jika perlu)

            $stockMutation->approved_by = Auth::id();
            $stockMutation->approved_at = now();

            // --- INTI LOGIKA BARU ---
            // Cek apakah ini mutasi internal (dalam satu gudang)
            if ($stockMutation->gudang_asal_id === $stockMutation->gudang_tujuan_id) {

                // Langsung selesaikan mutasi
                $stockMutation->status = 'COMPLETED';
                $stockMutation->received_by = Auth::id(); // Dicatat sebagai diterima oleh approver
                $stockMutation->received_at = now();

                // Tambah stok di rak tujuan
                $destinationInventory = Inventory::firstOrCreate(
                    [
                        'part_id' => $stockMutation->part_id,
                        'rak_id' => $stockMutation->rak_tujuan_id,
                        'gudang_id' => $stockMutation->gudang_tujuan_id,
                    ],
                    ['quantity' => 0]
                );
                $destinationInventory->increment('quantity', $stockMutation->jumlah);

                // Catat pergerakan stok MASUK ke rak tujuan
                // (Kode pencatatan stock movement bisa ditambahkan di sini jika perlu)

            } else {
                // Jika antar gudang, gunakan alur lama
                $stockMutation->status = 'IN_TRANSIT';
            }

            $stockMutation->save();
            DB::commit();

            return redirect()->route('admin.stock-mutations.show', $stockMutation)->with('success', 'Permintaan mutasi berhasil diproses.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, StockMutation $stockMutation) // Tambahkan Request $request
    {
        $this->authorize('approve-mutation', $stockMutation);

        // Validasi bahwa alasan penolakan wajib diisi
        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        if ($stockMutation->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $stockMutation->status = 'REJECTED';
        $stockMutation->rejection_reason = $request->rejection_reason; // Simpan alasan penolakan
        $stockMutation->approved_by = Auth::id(); // Catat siapa yang menolak
        $stockMutation->approved_at = now(); // Catat kapan ditolak
        $stockMutation->save();

        return redirect()->route('admin.stock-mutations.index')->with('success', 'Permintaan mutasi stok telah ditolak.');
    }

    private function generateMutationNumber()
    {
        $date = now()->format('Ymd');
        $latest = StockMutation::whereDate('created_at', today())->count();
        $sequence = str_pad($latest + 1, 4, '0', STR_PAD_LEFT);
        return "MT-{$date}-{$sequence}";
    }

    public function show(StockMutation $stockMutation)
    {
        // Memuat semua relasi yang dibutuhkan untuk ditampilkan di view
        $stockMutation->load(['part', 'gudangAsal', 'gudangTujuan', 'rakAsal', 'createdBy', 'approvedBy']);

        return view('admin.stock_mutations.show', compact('stockMutation'));
    }

    public function getPartStockDetails(Request $request)
    {
        $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
            'part_id' => 'required|exists:parts,id',
        ]);

        $stockDetails = Inventory::with('rak')
            ->where('gudang_id', $request->gudang_id)
            ->where('part_id', $request->part_id)
            ->where('quantity', '>', 0)
            ->get();

        // Hanya kembalikan data yang dibutuhkan oleh dropdown
        $formattedStock = $stockDetails->map(function($inventory) {
            return [
                'rak_id' => $inventory->rak->id,
                'rak_label' => "{$inventory->rak->kode_rak} (Stok: {$inventory->quantity})",
                'max_qty' => $inventory->quantity,
            ];
        });

        return response()->json($formattedStock);
    }
}
