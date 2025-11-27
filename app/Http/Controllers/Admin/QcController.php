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
    public function index()
    {
        // Gate 'can-qc' sudah dimapping ke AG di AuthServiceProvider
        $this->authorize('can-qc');
        $user = Auth::user();

        $query = Receiving::where('status', 'PENDING_QC')
                          ->with(['purchaseOrder', 'gudang']);

        // Jika user bukan Super Admin atau Manager, batasi data hanya gudang user tersebut
        if (!in_array($user->jabatan->singkatan, ['SA', 'MA'])) {
            $query->where('gudang_id', $user->gudang_id);
        }

        $receivings = $query->latest('tanggal_terima')->paginate(15);

        return view('admin.qc.index', compact('receivings'));
    }

    public function showQcForm(Receiving $receiving)
    {
        $this->authorize('can-qc');
        
        $user = Auth::user();
        if (!in_array($user->jabatan->singkatan, ['SA', 'MA']) && $receiving->gudang_id !== $user->gudang_id) {
            abort(403, 'Anda tidak memiliki akses ke data gudang ini.');
        }

        if ($receiving->status !== 'PENDING_QC') {
            return redirect()->route('admin.qc.index')->with('error', 'Penerimaan ini sudah diproses QC.');
        }
        
        // PERBAIKAN DISINI: 
        // Filter agar item dengan qty_terima 0 TIDAK DIMUAT ke form
        // Ini mengatasi data "hantu" yang terlanjur tersimpan
        $receiving->load(['details' => function ($query) {
            $query->where('qty_terima', '>', 0);
        }, 'details.part']);

        // Jika ternyata kosong (artinya isinya qty 0 semua), tolak atau auto-close
        if ($receiving->details->isEmpty()) {
             return redirect()->route('admin.qc.index')->with('error', 'Dokumen ini tidak memiliki item valid untuk di-QC (Qty 0).');
        }

        return view('admin.qc.form', compact('receiving'));
    }

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
            $totalLolos = 0;

            foreach ($request->items as $detailId => $data) {
                $detail = ReceivingDetail::findOrFail($detailId);

                if ($detail->qty_terima <= 0) continue; 

                $qtyLolos = (int) $data['qty_lolos'];
                $qtyGagal = (int) $data['qty_gagal'];
                $totalInput = $qtyLolos + $qtyGagal;

                if ($totalInput != $detail->qty_terima) {
                    throw new \Exception("Validasi Gagal pada part {$detail->part->nama_part}: Jumlah input ($totalInput) tidak sesuai qty diterima ({$detail->qty_terima}).");
                }

                $detail->update([
                    'qty_lolos_qc' => $qtyLolos,
                    'qty_gagal_qc' => $qtyGagal,
                    'catatan_qc' => $data['catatan_qc'],
                ]);

                $totalLolos += $qtyLolos;

                if ($qtyGagal > 0) {
                    $this->processQuarantine($detail, $receiving, $qtyGagal);
                }
            }

            $remainingItems = $receiving->details()
                ->where('qty_terima', '>', 0)
                ->whereNull('qty_lolos_qc')
                ->count();

            if ($remainingItems > 0) {
                $receiving->status = 'PENDING_QC';
            } else {
                $hasItemsToPutaway = $receiving->details()
                    ->where('qty_terima', '>', 0)
                    ->where('qty_lolos_qc', '>', 0)
                    ->exists();

                $receiving->status = $hasItemsToPutaway ? 'PENDING_PUTAWAY' : 'COMPLETED';
                $receiving->qc_by = Auth::id();
                $receiving->qc_at = now();
            }
            
            $receiving->save();

            DB::commit();
            return redirect()->route('admin.qc.index')->with('success', 'Hasil QC berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan QC: ' . $e->getMessage())->withInput();
        }
    }

    private function processQuarantine($detail, $receiving, $qtyGagal) {
        $quarantineRak = Rak::where('gudang_id', $receiving->gudang_id)
                            ->where(function($q) {
                                $q->where('kode_rak', 'like', '%-KRN-QC')
                                  ->orWhere('tipe_rak', 'QUARANTINE');
                            })->first();

        if ($quarantineRak) {
             $inventory = \App\Models\InventoryBatch::firstOrCreate(
                [
                    'part_id' => $detail->part_id,
                    'rak_id' => $quarantineRak->id,
                    'receiving_detail_id' => $detail->id,
                    'gudang_id' => $receiving->gudang_id,
                ],
                ['quantity' => 0]
            );
            
            $stokSebelum = $inventory->quantity;
            $inventory->increment('quantity', $qtyGagal);

            \App\Models\StockMovement::create([
                'part_id' => $detail->part_id,
                'gudang_id' => $receiving->gudang_id,
                'rak_id' => $quarantineRak->id,
                'tipe_gerakan' => 'KARANTINA_QC',
                'jumlah' => $qtyGagal,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $inventory->quantity,
                'referensi' => $receiving->nomor_penerimaan,
                'user_id' => Auth::id(),
                'keterangan' => 'Barang gagal QC',
            ]);
        }
    }
}