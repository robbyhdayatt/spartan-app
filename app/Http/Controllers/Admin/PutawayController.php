<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryBatch;
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
        
        // Security Check: Gudang Access
        $user = Auth::user();
        if (!in_array($user->jabatan->singkatan, ['SA', 'MA']) && $receiving->gudang_id !== $user->gudang_id) {
            abort(403);
        }

        if ($receiving->status !== 'PENDING_PUTAWAY') {
            return redirect()->route('admin.putaway.index')->with('error', 'Status tidak valid.');
        }

        $receiving->load('details.part');

        $raks = Rak::where('gudang_id', $receiving->gudang_id)
                    ->where('is_active', true)
                    ->where('tipe_rak', 'PENYIMPANAN') // Hanya rak penyimpanan (bukan karantina)
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

                if ($jumlahMasuk <= 0) continue;

                // 1. Buat Batch Inventory (FIFO)
                InventoryBatch::create([
                    'part_id'             => $detail->part_id,
                    'rak_id'              => $data['rak_id'],
                    'gudang_id'           => $receiving->gudang_id,
                    'receiving_detail_id' => $detail->id,
                    'quantity'            => $jumlahMasuk,
                ]);

                // 2. Update Harga Beli Rata-rata (Moving Average Cost)
                $poDetail = PurchaseOrderDetail::where('purchase_order_id', $receiving->purchase_order_id)
                                                ->where('part_id', $part->id)
                                                ->first();
                $hargaBeliBaru = $poDetail ? $poDetail->harga_beli : $part->harga_beli_default;

                // Hitung total valuasi stok saat ini sebelum ditambah
                $stokExisting = $part->inventoryBatches()->sum('quantity'); // Ambil real stock dari batches
                $totalNilaiExisting = $stokExisting * $part->harga_beli_rata_rata;
                
                // Hitung total baru
                $totalNilaiBaru = $totalNilaiExisting + ($jumlahMasuk * $hargaBeliBaru);
                $totalStokBaru = $stokExisting + $jumlahMasuk;

                if ($totalStokBaru > 0) {
                    $part->harga_beli_rata_rata = $totalNilaiBaru / $totalStokBaru;
                    $part->save();
                }

                // 3. Catat Pergerakan Stok
                $receiving->stockMovements()->create([
                    'part_id'       => $detail->part_id,
                    'gudang_id'     => $receiving->gudang_id,
                    'rak_id'        => $data['rak_id'],
                    'tipe_gerakan'  => 'INBOUND',
                    'jumlah'        => $jumlahMasuk,
                    'stok_sebelum'  => $stokExisting,
                    'stok_sesudah'  => $totalStokBaru,
                    'user_id'       => Auth::id(),
                    'keterangan'    => 'Putaway dari PO ' . ($receiving->purchaseOrder->nomor_po ?? '-'),
                ]);

                $detail->update(['qty_disimpan' => $jumlahMasuk]);
            }

            $receiving->status = 'COMPLETED';
            $receiving->putaway_by = Auth::id();
            $receiving->putaway_at = now();
            $receiving->save();

            DB::commit();
            return redirect()->route('admin.putaway.index')->with('success', 'Putaway selesai. Stok batch telah terbentuk.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }
}