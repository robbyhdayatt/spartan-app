<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Rak;
use App\Models\StockMovement;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MutationReceivingController extends Controller
{
    /**
     * Menampilkan daftar mutasi yang sedang dalam perjalanan ke gudang pengguna.
     */
    public function index()
    {
        $user = Auth::user();
        $this->authorize('can-receive'); // Memakai izin yang sama dengan penerimaan PO

        $pendingMutations = StockMutation::where('gudang_tujuan_id', $user->gudang_id)
            ->where('status', 'IN_TRANSIT')
            ->with(['part', 'gudangAsal', 'createdBy'])
            ->latest('approved_at')
            ->paginate(15);

        return view('admin.mutation_receiving.index', compact('pendingMutations'));
    }

    /**
     * Menampilkan form untuk menerima mutasi.
     */
    public function show(StockMutation $mutation)
    {
        $this->authorize('can-receive');

        // Pastikan mutasi ini ditujukan untuk gudang user yang sedang login
        if ($mutation->gudang_tujuan_id !== Auth::user()->gudang_id) {
            abort(403, 'AKSI TIDAK DIIZINKAN.');
        }

        $mutation->load(['part', 'gudangAsal', 'rakAsal', 'createdBy', 'approvedBy']);
        $raks = Rak::where('gudang_id', $mutation->gudang_tujuan_id)
                    ->where('is_active', true)
                    ->orderBy('nama_rak')
                    ->get();

        return view('admin.mutation_receiving.show', compact('mutation', 'raks'));
    }

    /**
     * Memproses penerimaan barang mutasi.
     */
    public function receive(Request $request, StockMutation $mutation)
    {
        $this->authorize('can-receive');

        if ($mutation->gudang_tujuan_id !== Auth::user()->gudang_id) {
            abort(403, 'AKSI TIDAK DIIZINKAN.');
        }

        $validated = $request->validate([
            'rak_tujuan_id' => 'required|exists:raks,id',
        ]);

        DB::beginTransaction();
        try {
            // --- PROSES GUDANG TUJUAN (STOCK IN) ---
            $destinationInventory = Inventory::firstOrCreate(
                ['part_id' => $mutation->part_id, 'rak_id' => $validated['rak_tujuan_id']],
                ['gudang_id' => $mutation->gudang_tujuan_id, 'quantity' => 0]
            );

            $stokSebelumTujuan = $destinationInventory->quantity;
            $jumlahMutasi = $mutation->jumlah;

            // Tambahkan stok ke inventaris tujuan
            $destinationInventory->quantity += $jumlahMutasi;
            $destinationInventory->save();

            // Catat pergerakan stok masuk
            StockMovement::create([
                'part_id' => $mutation->part_id,
                'gudang_id' => $mutation->gudang_tujuan_id,
                'tipe_gerakan' => 'MUTASI_MASUK',
                'jumlah' => $jumlahMutasi,
                'stok_sebelum' => $stokSebelumTujuan,
                'stok_sesudah' => $destinationInventory->quantity,
                'referensi' => $mutation->nomor_mutasi,
                'keterangan' => 'Penerimaan dari Gudang ' . $mutation->gudangAsal->kode_gudang,
                'user_id' => Auth::id(),
            ]);

            // --- PERBARUI STATUS MUTASI ---
            $mutation->status = 'COMPLETED';
            $mutation->rak_tujuan_id = $validated['rak_tujuan_id'];
            $mutation->received_by = Auth::id();
            $mutation->received_at = now();
            $mutation->save();

            DB::commit();

            return redirect()->route('admin.mutation-receiving.index')->with('success', 'Barang mutasi berhasil diterima.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
