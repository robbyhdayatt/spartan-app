<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryBatch;
use App\Models\PurchaseOrderDetail;
use App\Models\Rak;
use App\Models\Receiving;
use App\Models\ReceivingDetail;
use App\Models\StockMovement;
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
        
        $user = Auth::user();
        if (!in_array($user->jabatan->singkatan, ['SA', 'MA']) && $receiving->gudang_id !== $user->gudang_id) {
            abort(403);
        }

        if ($receiving->status !== 'PENDING_PUTAWAY') {
            return redirect()->route('admin.putaway.index')->with('error', 'Status tidak valid.');
        }

        // PERBAIKAN 1: Filter item yang SUDAH disimpan agar tidak muncul lagi
        // Hanya tampilkan item yang qty_disimpan-nya masih kurang dari qty_lolos_qc
        $itemsToPutaway = $receiving->details()
            ->where('qty_lolos_qc', '>', 0)
            ->whereColumn('qty_disimpan', '<', 'qty_lolos_qc') // <--- TAMBAHAN PENTING
            ->with('part')
            ->get();

        // Jika semua item sudah disimpan tapi status masih PENDING, redirect info
        if ($itemsToPutaway->isEmpty()) {
             // Opsional: Bisa otomatis update status di sini jika mau
             return redirect()->route('admin.putaway.index')->with('error', 'Semua item dalam dokumen ini sudah disimpan.');
        }

        $raks = Rak::where('gudang_id', $receiving->gudang_id)
                    ->where('is_active', true)
                    ->where('tipe_rak', 'PENYIMPANAN')
                    ->orderBy('kode_rak')
                    ->get();

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
                $qtyBelumSimpan = $detail->qty_lolos_qc - $detail->qty_disimpan;
                $jumlahMasuk = $qtyBelumSimpan; 

                if ($jumlahMasuk <= 0) continue;

                $stokExisting = $part->inventoryBatches()->sum('quantity'); 
                $totalNilaiExisting = $stokExisting * $part->harga_beli_rata_rata;

                // Create Batch Baru
                InventoryBatch::create([
                    'part_id'             => $detail->part_id,
                    'rak_id'              => $data['rak_id'],
                    'gudang_id'           => $receiving->gudang_id,
                    'receiving_detail_id' => $detail->id,
                    'quantity'            => $jumlahMasuk,
                ]);

                // Update Harga Rata-rata
                $poDetail = PurchaseOrderDetail::where('purchase_order_id', $receiving->purchase_order_id)
                                                ->where('part_id', $part->id)
                                                ->first();
                $hargaBeliBaru = $poDetail ? $poDetail->harga_beli : $part->harga_beli_default;
                
                $totalNilaiBaru = $totalNilaiExisting + ($jumlahMasuk * $hargaBeliBaru);
                $totalStokBaru = $stokExisting + $jumlahMasuk;

                if ($totalStokBaru > 0) {
                    $part->harga_beli_rata_rata = $totalNilaiBaru / $totalStokBaru;
                    $part->save();
                }

                StockMovement::create([
                    'part_id'       => $detail->part_id,
                    'gudang_id'     => $receiving->gudang_id,
                    'rak_id'        => $data['rak_id'],
                    'tipe_gerakan'  => 'INBOUND',
                    'jumlah'        => $jumlahMasuk,
                    'stok_sebelum'  => $stokExisting,
                    'stok_sesudah'  => $totalStokBaru,
                    'referensi'     => $receiving->nomor_penerimaan,
                    'user_id'       => Auth::id(),
                    'keterangan'    => 'Putaway dari PO ' . ($receiving->purchaseOrder->nomor_po ?? '-'),
                ]);

                $detail->increment('qty_disimpan', $jumlahMasuk);
            }
            $receiving->load('details');
            $allDetails = $receiving->details;
            
            $hasPendingItems = false;
            $allDetails = $receiving->details()->get();

            foreach($allDetails as $d) {
                if ($d->qty_terima <= 0) continue;

                if ($d->qty_lolos_qc > 0 && $d->qty_disimpan < $d->qty_lolos_qc) {
                    $hasPendingItems = true;
                    break;
                }
            }

            if ($hasPendingItems) {
                $receiving->update(['status' => 'PENDING_PUTAWAY']);
                $pesan = 'Putaway sebagian berhasil. Masih ada item tersisa.';
            } else {
                $receiving->status = 'COMPLETED';
                $receiving->putaway_by = Auth::id();
                $receiving->putaway_at = now();
                $receiving->save();
                $pesan = 'Putaway selesai sepenuhnya.';
            }

            DB::commit();
            return redirect()->route('admin.putaway.index')->with('success', $pesan);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }
}