<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receiving;
use App\Models\ReceivingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Rak;

class QcController extends Controller
{
    // Show a list of receiving records waiting for QC
    public function index()
    {
        $this->authorize('can-qc'); // Good to secure this page too
        $receivings = Receiving::where('status', 'PENDING_QC')
            ->with(['purchaseOrder.supplier', 'gudang'])
            ->latest()
            ->get();

        return view('admin.qc.index', compact('receivings'));
    }

    // Show the form to input QC results for a specific receiving record
    public function showQcForm(Receiving $receiving)
    {
        $this->authorize('can-qc'); // And this one
        if ($receiving->status !== 'PENDING_QC') {
            return redirect()->route('admin.qc.index')->with('error', 'Penerimaan ini sudah diproses QC.');
        }
        $receiving->load(['details.part']);
        return view('admin.qc.form', compact('receiving'));
    }

    // Store the QC results
    public function storeQcResult(Request $request, Receiving $receiving)
    {
        $this->authorize('can-qc');

        $request->validate([
            'items' => 'required|array',
            'items.*.qty_lolos' => 'required|integer|min:0',
            'items.*.qty_gagal' => 'required|integer|min:0',
            'items.*.catatan_qc' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->items as $detailId => $data) {
                $detail = ReceivingDetail::findOrFail($detailId);
                $qtyLolos = $data['qty_lolos'];
                $qtyGagal = $data['qty_gagal'];
                $totalInput = $qtyLolos + $qtyGagal;

                if ($totalInput > $detail->qty_terima) {
                    throw new \Exception('Jumlah Lolos & Gagal QC melebihi jumlah diterima untuk part ' . $detail->part->nama_part);
                }

                $detail->update([
                    'qty_lolos_qc' => $qtyLolos,
                    'qty_gagal_qc' => $qtyGagal,
                    'catatan_qc' => $data['catatan_qc'],
                ]);

                // Handle the remaining "limbo" stock
                $sisa = $detail->qty_terima - $totalInput;
                if ($sisa > 0) {
                    $quarantineRak = Rak::where('gudang_id', $receiving->gudang_id)
                                        ->where('kode_rak', 'like', '%-KRN-QC')
                                        ->firstOrFail();

                    $inventory = \App\Models\Inventory::firstOrCreate(
                        ['part_id' => $detail->part_id, 'rak_id' => $quarantineRak->id],
                        ['gudang_id' => $receiving->gudang_id, 'quantity' => 0]
                    );

                    $stokSebelum = $inventory->quantity;
                    $inventory->quantity += $sisa;
                    $inventory->save();

                    // Log the movement to quarantine
                    \App\Models\StockMovement::create([
                        'part_id' => $detail->part_id, 'gudang_id' => $receiving->gudang_id,
                        'tipe_gerakan' => 'KARANTINA_QC', 'jumlah' => $sisa,
                        'stok_sebelum' => $stokSebelum, 'stok_sesudah' => $inventory->quantity,
                        'referensi' => $receiving->nomor_penerimaan, 'user_id' => Auth::id(),
                        'keterangan' => 'Sisa barang dari proses QC',
                    ]);
                }
            }

            // Update the main receiving record status
            $receiving->status = 'COMPLETED'; // Now it's completed because all stock is accounted for
            $receiving->save();

            DB::commit();
            return redirect()->route('admin.qc.index')->with('success', 'Hasil QC disimpan. Item yang lolos siap untuk Putaway.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}
