<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryBatch; // Gunakan model baru
use App\Models\Part;
use App\Models\PurchaseOrderDetail;
use App\Models\Rak;
use App\Models\Receiving;
use App\Models\ReceivingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PutawayController extends Controller
{
    public function index()
    {
        $this->authorize('can-putaway');
        $user = Auth::user();

        $query = Receiving::where('status', 'PENDING_PUTAWAY')
                                ->with(['purchaseOrder', 'gudang']);

        if (!in_array($user->jabatan->singkatan, ['SA', 'MA'])) {
            $query->where('gudang_id', $user->gudang_id);
        }

        $receivings = $query->latest('qc_at')->paginate(15);

        return view('admin.putaway.index', compact('receivings'));
    }

    public function showPutawayForm(Receiving $receiving)
    {
        $this->authorize('can-putaway');
        if ($receiving->status !== 'PENDING_PUTAWAY') {
            return redirect()->route('admin.putaway.index')->with('error', 'Penerimaan ini tidak siap untuk proses Putaway.');
        }

        $receiving->load('details.part');

        $raks = Rak::where('gudang_id', $receiving->gudang_id)
                    ->where('is_active', true)
                    ->where('tipe_rak', 'PENYIMPANAN')
                    ->orderBy('kode_rak')
                    ->get();

        $itemsToPutaway = $receiving->details()->where('qty_lolos_qc', '>', 0)->get();

        return view('admin.putaway.form', compact('receiving', 'itemsToPutaway', 'raks'));
    }

    public function storePutaway(Request $request, Receiving $receiving)
    {
        $this->authorize('can-putaway');
        $request->validate([
            'items' => 'required|array',
            'items.*.rak_id' => 'required|exists:raks,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->items as $detailId => $data) {
                $detail = ReceivingDetail::with('part')->findOrFail($detailId);
                $part = $detail->part;
                $jumlahMasuk = $detail->qty_lolos_qc;

                if ($jumlahMasuk <= 0) {
                    continue;
                }

                // --- LOGIKA BARU: Membuat Inventory Batch ---
                InventoryBatch::create([
                    'part_id'             => $detail->part_id,
                    'rak_id'              => $data['rak_id'],
                    'gudang_id'           => $receiving->gudang_id,
                    'receiving_detail_id' => $detail->id, // Kunci FIFO
                    'quantity'            => $jumlahMasuk,
                ]);
                // --- END LOGIKA BARU ---

                // --- PERBAIKAN KALKULASI HARGA RATA-RATA ---
                $poDetail = PurchaseOrderDetail::where('purchase_order_id', $receiving->purchase_order_id)
                                                ->where('part_id', $part->id)
                                                ->first();
                $hargaBeliBaru = $poDetail ? $poDetail->harga_beli : $part->harga_beli_default;

                // Hitung stok lama dari kedua tabel (inventories untuk karantina & inventory_batches untuk penjualan)
                $stokLamaDariBatches = $part->inventoryBatches()->sum('quantity');
                $stokLamaDariInventories = $part->inventories()->sum('quantity');
                $stokLamaTotal = $stokLamaDariBatches + $stokLamaDariInventories - $jumlahMasuk; // Kurangi dulu jumlah yg baru masuk

                $hargaRataRataLama = $part->harga_beli_rata_rata;
                $totalNilaiLama = $stokLamaTotal * $hargaRataRataLama;
                $totalNilaiBaru = $jumlahMasuk * $hargaBeliBaru;
                $totalStokBaru = $stokLamaTotal + $jumlahMasuk;

                $hargaRataRataBaru = ($totalStokBaru > 0) ? (($totalNilaiLama + $totalNilaiBaru) / $totalStokBaru) : $hargaBeliBaru;

                $part->harga_beli_rata_rata = $hargaRataRataBaru;
                $part->save();
                // --- END PERBAIKAN KALKULASI ---

                // Catat di Stock Movement
                $receiving->stockMovements()->create([
                    'part_id'       => $detail->part_id,
                    'gudang_id'     => $receiving->gudang_id,
                    'rak_id'        => $data['rak_id'],
                    'jumlah'        => $jumlahMasuk,
                    'stok_sebelum'  => $stokLamaTotal,
                    'stok_sesudah'  => $totalStokBaru,
                    'user_id'       => Auth::id(),
                    'keterangan'    => 'Stok masuk dari PO ' . $receiving->purchaseOrder->nomor_po,
                ]);

                $detail->update(['qty_disimpan' => $jumlahMasuk]);
            }

            $receiving->status = 'COMPLETED';
            $receiving->putaway_by = Auth::id();
            $receiving->putaway_at = now();
            $receiving->save();

            DB::commit();
            return redirect()->route('admin.putaway.index')->with('success', 'Barang berhasil disimpan sebagai batch FIFO, stok, dan harga rata-rata telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}
