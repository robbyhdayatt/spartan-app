<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryBatch; // Diubah dari Inventory
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

        if ($mutation->gudang_tujuan_id !== Auth::user()->gudang_id) {
            abort(403, 'AKSI TIDAK DIIZINKAN.');
        }

        $mutation->load(['part', 'gudangAsal', 'createdBy', 'approvedBy']);
        $raks = Rak::where('gudang_id', $mutation->gudang_tujuan_id)
                    ->where('is_active', true)
                    ->where('tipe_rak', 'PENYIMPANAN')
                    ->orderBy('kode_rak')
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

        try {
            DB::transaction(function () use ($mutation, $validated) {
                $jumlahMutasi = $mutation->jumlah;

                // Buat batch baru di rak tujuan
                $newBatch = InventoryBatch::create([
                    'part_id' => $mutation->part_id,
                    'rak_id' => $validated['rak_tujuan_id'],
                    'gudang_id' => $mutation->gudang_tujuan_id,
                    'quantity' => $jumlahMutasi,
                    'receiving_detail_id' => null, // Tidak berasal dari PO
                ]);

                // Hitung total stok sebelumnya di gudang tujuan untuk part ini (untuk laporan kartu stok)
                $stokSebelumTotal = InventoryBatch::where('gudang_id', $mutation->gudang_tujuan_id)
                    ->where('part_id', $mutation->part_id)
                    ->where('id', '!=', $newBatch->id) // Abaikan batch yang baru dibuat
                    ->sum('quantity');

                $stokSesudahTotal = $stokSebelumTotal + $jumlahMutasi;

                // Catat pergerakan stok masuk dengan format yang benar
                StockMovement::create([
                    'part_id' => $mutation->part_id,
                    'gudang_id' => $mutation->gudang_tujuan_id,
                    'rak_id' => $newBatch->rak_id,
                    'jumlah' => $jumlahMutasi,
                    'stok_sebelum' => $stokSebelumTotal, // Stok total di gudang sebelum ditambah
                    'stok_sesudah' => $stokSesudahTotal, // Stok total di gudang setelah ditambah
                    'referensi_type' => get_class($mutation), // Menggunakan polymorphic relation
                    'referensi_id' => $mutation->id,           // Menggunakan polymorphic relation
                    'keterangan' => 'Mutasi Masuk dari ' . $mutation->gudangAsal->kode_gudang,
                    'user_id' => Auth::id(),
                ]);

                // Perbarui status mutasi menjadi selesai
                $mutation->status = 'COMPLETED';
                $mutation->rak_tujuan_id = $validated['rak_tujuan_id'];
                $mutation->received_by = Auth::id();
                $mutation->received_at = now();
                $mutation->save();
            });
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->route('admin.mutation-receiving.index')->with('success', 'Barang mutasi berhasil diterima.');
    }
}
