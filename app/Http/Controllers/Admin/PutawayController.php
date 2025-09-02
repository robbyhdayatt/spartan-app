<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Rak;
use App\Models\Receiving;
use App\Models\ReceivingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PutawayController extends Controller
{
    // Show a list of receiving records ready for putaway
    public function index()
    {
        $this->authorize('can-putaway');
        $receivings = Receiving::where('status', 'PENDING_PUTAWAY')
            ->with(['purchaseOrder.supplier', 'gudang'])
            ->latest()
            ->paginate(15);

        return view('admin.putaway.index', compact('receivings'));
    }

    // Show the form to assign shelves for a specific receiving record
    public function showPutawayForm(Receiving $receiving)
    {
        $this->authorize('can-putaway');
        if ($receiving->status !== 'PENDING_PUTAWAY') {
            return redirect()->route('admin.putaway.index')->with('error', 'Penerimaan ini sudah diproses.');
        }

        // Get only items that passed QC and haven't been stored yet
        $receiving->load(['details' => function ($query) {
            $query->where('qty_lolos_qc', '>', 0);
        }, 'details.part']);

        // Get shelves from the correct warehouse
        $raks = Rak::where('gudang_id', $receiving->gudang_id)
            ->where('is_active', true)
            ->orderBy('nama_rak')
            ->get();

        return view('admin.putaway.form', compact('receiving', 'raks'));
    }

    // Store the items onto the shelves and update inventory
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
                $detail = ReceivingDetail::findOrFail($detailId);
                $inventory = Inventory::firstOrCreate(
                    ['part_id' => $detail->part_id, 'rak_id' => $data['rak_id']],
                    ['gudang_id' => $receiving->gudang_id, 'quantity' => 0]
                );

                // --- MULAI LOGGING ---
                $stokSebelum = $inventory->quantity;
                $jumlah = $detail->qty_lolos_qc;
                $stokSesudah = $stokSebelum + $jumlah;

                $inventory->quantity = $stokSesudah;
                $inventory->save();

                \App\Models\StockMovement::create([
                    'part_id' => $detail->part_id,
                    'gudang_id' => $receiving->gudang_id,
                    'tipe_gerakan' => 'PEMBELIAN',
                    'jumlah' => $jumlah, // Positif untuk stok masuk
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi' => $receiving->nomor_penerimaan,
                    'user_id' => Auth::id(),
                ]);
                // --- AKHIR LOGGING ---

                $detail->update(['qty_disimpan' => $detail->qty_lolos_qc]);
            }

            $receiving->status = 'COMPLETED';
            $receiving->save();

            DB::commit();
            return redirect()->route('admin.putaway.index')->with('success', 'Barang berhasil disimpan dan stok telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}
