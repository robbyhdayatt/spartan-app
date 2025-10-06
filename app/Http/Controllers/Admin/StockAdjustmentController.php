<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\InventoryBatch;
use App\Models\Part;
use App\Models\StockMovement;
use App\Models\Gudang;
use App\Models\Rak; // <-- TAMBAHKAN ATAU PASTIKAN BARIS INI ADA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockAdjustment::with(['part', 'gudang', 'rak', 'createdBy', 'approvedBy'])->latest()->get();
        return view('admin.stock_adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $user = auth()->user();
        $parts = \App\Models\Part::where('is_active', true)->get();

        if ($user->gudang_id) {
            $gudangs = \App\Models\Gudang::where('id', $user->gudang_id)->get();
        } else {
            $gudangs = \App\Models\Gudang::where('is_active', true)->get();
        }

        return view('admin.stock_adjustments.create', compact('gudangs', 'parts'));
    }

    // Fungsi API untuk mengambil data rak
    public function getRaksByGudang(Gudang $gudang)
    {
        $raks = Rak::where('gudang_id', $gudang->id)
                   ->whereIn('tipe_rak', ['PENYIMPANAN', 'KARANTINA'])
                   ->where('is_active', true)
                   ->get();
        return response()->json($raks);
    }

    public function store(Request $request)
    {
        $this->authorize('can-manage-stock');
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'gudang_id' => 'required|exists:gudangs,id',
            'rak_id' => 'required|exists:raks,id',
            'tipe' => 'required|in:TAMBAH,KURANG',
            'jumlah' => 'required|integer|min:1',
            'alasan' => 'required|string',
        ]);

        StockAdjustment::create([
            'part_id' => $validated['part_id'],
            'gudang_id' => $validated['gudang_id'],
            'rak_id' => $validated['rak_id'],
            'tipe' => $validated['tipe'],
            'jumlah' => $validated['jumlah'],
            'alasan' => $validated['alasan'],
            'status' => 'PENDING_APPROVAL',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.stock-adjustments.index')->with('success', 'Permintaan adjusment stok berhasil dibuat dan menunggu persetujuan.');
    }

    public function approve(StockAdjustment $stockAdjustment)
    {
        $this->authorize('approve-adjustment', $stockAdjustment);

        if ($stockAdjustment->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        // Menggunakan DB Facade langsung untuk transaksi yang lebih aman
        try {
            DB::transaction(function () use ($stockAdjustment) {
                $part_id = $stockAdjustment->part_id;
                $rak_id = $stockAdjustment->rak_id;
                $gudang_id = $stockAdjustment->gudang_id;
                $jumlahToAdjust = $stockAdjustment->jumlah;
                $tipe = $stockAdjustment->tipe;

                // Dapatkan total stok saat ini dari semua batch untuk pencatatan
                $stokSebelum = InventoryBatch::where('part_id', $part_id)
                    ->where('rak_id', $rak_id)
                    ->sum('quantity');

                $stokSesudah = 0;

                if ($tipe === 'KURANG') {
                    if ($stokSebelum < $jumlahToAdjust) {
                        throw new \Exception('Stok tidak mencukupi. Stok tersedia: ' . $stokSebelum . ', dibutuhkan: ' . $jumlahToAdjust);
                    }

                    // Ambil semua batch yang relevan, urutkan dari yang paling lama (FIFO)
                    $batches = InventoryBatch::where('part_id', $part_id)
                        ->where('rak_id', $rak_id)
                        ->where('quantity', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get();

                    $remainingQtyToReduce = $jumlahToAdjust;

                    foreach ($batches as $batch) {
                        if ($remainingQtyToReduce <= 0) break;

                        $qtyInBatch = $batch->quantity;

                        if ($qtyInBatch >= $remainingQtyToReduce) {
                            // Batch ini cukup untuk memenuhi sisa pengurangan
                            $batch->quantity -= $remainingQtyToReduce;
                            $remainingQtyToReduce = 0;
                        } else {
                            // Habiskan batch ini dan lanjut ke batch berikutnya
                            $remainingQtyToReduce -= $qtyInBatch;
                            $batch->quantity = 0;
                        }

                        if ($batch->quantity == 0) {
                            // Hapus batch jika stoknya habis
                            $batch->delete();
                        } else {
                            $batch->save();
                        }
                    }

                    $stokSesudah = $stokSebelum - $jumlahToAdjust;

                } else { // Tipe 'TAMBAH'
                    // Untuk penambahan, kita buat batch baru. Ini adalah pendekatan paling aman
                    // untuk menjaga integritas data FIFO, meskipun tidak ada referensi ke penerimaan.
                    InventoryBatch::create([
                        'part_id' => $part_id,
                        'rak_id' => $rak_id,
                        'gudang_id' => $gudang_id,
                        'quantity' => $jumlahToAdjust,
                        'receiving_detail_id' => null, // Tidak ada referensi penerimaan
                    ]);

                    $stokSesudah = $stokSebelum + $jumlahToAdjust;
                }

                // Catat pergerakan stok
                StockMovement::create([
                    'part_id' => $part_id,
                    'gudang_id' => $gudang_id,
                    'rak_id' => $rak_id,
                    'jumlah' => ($tipe === 'TAMBAH' ? $jumlahToAdjust : -$jumlahToAdjust),
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi_type' => get_class($stockAdjustment),
                    'referensi_id' => $stockAdjustment->id,
                    'keterangan' => "Adjustment: " . $stockAdjustment->alasan,
                    'user_id' => $stockAdjustment->created_by,
                ]);

                // Update status permintaan adjustment
                $stockAdjustment->status = 'APPROVED';
                $stockAdjustment->approved_by = Auth::id();
                $stockAdjustment->approved_at = now();
                $stockAdjustment->save();
            });

            return redirect()->route('admin.stock-adjustments.index')->with('success', 'Adjusment stok disetujui dan stok telah diperbarui.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, StockAdjustment $stockAdjustment)
    {
        $this->authorize('approve-adjustment', $stockAdjustment);
        $request->validate(['rejection_reason' => 'required|string|min:10']);

        if ($stockAdjustment->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $stockAdjustment->status = 'REJECTED';
        $stockAdjustment->rejection_reason = $request->rejection_reason;
        $stockAdjustment->approved_by = Auth::id();
        $stockAdjustment->approved_at = now();
        $stockAdjustment->save();

        return redirect()->route('admin.stock-adjustments.index')->with('success', 'Permintaan adjusment stok telah ditolak.');
    }
}
