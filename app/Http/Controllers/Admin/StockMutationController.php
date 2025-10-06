<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMutation;
use App\Models\InventoryBatch; // DIUBAH
use App\Models\Part;
use App\Models\Gudang;
use App\Models\Rak;
use App\Models\StockMovement; // DITAMBAHKAN
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

        return view('admin.stock_mutations.create', compact('gudangsAsal', 'gudangsTujuan'));
    }

    // AJAX: Mengambil part yang memiliki stok di gudang tertentu
    public function getPartsWithStock(Gudang $gudang)
    {
        $partIds = InventoryBatch::where('gudang_id', $gudang->id)
            ->where('quantity', '>', 0)
            ->pluck('part_id')
            ->unique();

        $parts = Part::whereIn('id', $partIds)->orderBy('nama_part')->get();
        return response()->json($parts);
    }

    // AJAX: Mengambil detail stok untuk validasi di frontend
    public function getPartStockDetails(Request $request)
    {
        $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
            'part_id' => 'required|exists:parts,id',
        ]);

        $totalStock = InventoryBatch::where('gudang_id', $request->gudang_id)
            ->where('part_id', $request->part_id)
            ->sum('quantity');

        return response()->json(['total_stock' => $totalStock]);
    }


    public function store(Request $request)
    {
        $this->authorize('can-manage-stock');
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'gudang_asal_id' => 'required|exists:gudangs,id',
            // rak_asal_id tidak lagi divalidasi karena akan ditentukan otomatis
            'gudang_tujuan_id' => 'required|exists:gudangs,id|different:gudang_asal_id',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        // Validasi stok total di gudang asal
        $totalStock = InventoryBatch::where('gudang_id', $validated['gudang_asal_id'])
            ->where('part_id', $validated['part_id'])
            ->sum('quantity');

        if ($totalStock < $validated['jumlah']) {
            return back()->with('error', 'Stok total di gudang asal tidak mencukupi. Stok tersedia: ' . $totalStock)->withInput();
        }

        StockMutation::create([
            'nomor_mutasi' => $this->generateMutationNumber(),
            'part_id' => $validated['part_id'],
            'gudang_asal_id' => $validated['gudang_asal_id'],
            'rak_asal_id' => null, // Dikosongkan, akan diisi saat approval jika perlu
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
            return back()->with('error', 'Hanya permintaan PENDING yang bisa diproses.');
        }

        try {
            DB::transaction(function () use ($stockMutation) {
                $jumlahToMutate = $stockMutation->jumlah;

                // Cek ulang total stok sebelum proses
                $totalStock = InventoryBatch::where('gudang_id', $stockMutation->gudang_asal_id)
                    ->where('part_id', $stockMutation->part_id)
                    ->sum('quantity');

                if ($totalStock < $jumlahToMutate) {
                    throw new \Exception('Stok tidak mencukupi. Stok tersedia: ' . $totalStock);
                }

                // Ambil batch tertua (FIFO)
                $batches = InventoryBatch::where('gudang_id', $stockMutation->gudang_asal_id)
                    ->where('part_id', $stockMutation->part_id)
                    ->where('quantity', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();

                $remainingToMutate = $jumlahToMutate;

                foreach ($batches as $batch) {
                    if ($remainingToMutate <= 0) break;

                    $stokSebelum = $batch->quantity;
                    $stokKeluar = min($stokSebelum, $remainingToMutate);

                    $batch->decrement('quantity', $stokKeluar);
                    $remainingToMutate -= $stokKeluar;

                    // Catat pergerakan stok untuk setiap batch yang terpengaruh
                    StockMovement::create([
                        'part_id' => $stockMutation->part_id,
                        'gudang_id' => $stockMutation->gudang_asal_id,
                        'rak_id' => $batch->rak_id,
                        'jumlah' => -$stokKeluar,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $batch->quantity,
                        'referensi_type' => get_class($stockMutation),
                        'referensi_id' => $stockMutation->id,
                        'keterangan' => 'Mutasi Keluar ke ' . $stockMutation->gudangTujuan->kode_gudang,
                        'user_id' => Auth::id(),
                    ]);

                    if ($batch->quantity <= 0) {
                        $batch->delete();
                    }
                }

                $stockMutation->status = 'IN_TRANSIT';
                $stockMutation->approved_by = Auth::id();
                $stockMutation->approved_at = now();
                $stockMutation->save();
            });
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->route('admin.stock-mutations.index')->with('success', 'Permintaan mutasi disetujui dan barang dalam perjalanan.');
    }

    public function show(StockMutation $stockMutation)
    {
        // Memuat semua relasi yang dibutuhkan untuk ditampilkan di view
        $stockMutation->load(['part', 'gudangAsal', 'gudangTujuan', 'rakAsal', 'createdBy', 'approvedBy']);

        return view('admin.stock_mutations.show', compact('stockMutation'));
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

}
