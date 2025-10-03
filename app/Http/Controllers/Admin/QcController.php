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
        $this->authorize('can-qc');
        $user = Auth::user();

        $query = \App\Models\Receiving::where('status', 'PENDING_QC')
                                        ->with(['purchaseOrder', 'gudang']);

        if (!in_array($user->jabatan->singkatan, ['SA', 'MA'])) {
            $query->where('gudang_id', $user->gudang_id);
        }

        $receivings = $query->latest('tanggal_terima')->paginate(15);

        return view('admin.qc.index', compact('receivings'));
    }

    public function showQcForm(Receiving $receiving)
    {
        $this->authorize('can-qc');
        if ($receiving->status !== 'PENDING_QC') {
            return redirect()->route('admin.qc.index')->with('error', 'Penerimaan ini sudah diproses QC.');
        }
        $receiving->load(['details.part']);
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

                $qtyLolos = (int) $data['qty_lolos'];
                $qtyGagal = (int) $data['qty_gagal'];
                $totalInput = $qtyLolos + $qtyGagal;

                if ($totalInput > $detail->qty_terima) {
                    throw new \Exception('Jumlah Lolos & Gagal QC (' . $totalInput . ') melebihi jumlah diterima (' . $detail->qty_terima . ') untuk part ' . $detail->part->nama_part);
                }

                $detail->update([
                    'qty_lolos_qc' => $qtyLolos,
                    'qty_gagal_qc' => $qtyGagal,
                    'catatan_qc' => $data['catatan_qc'],
                ]);

                $totalLolos += $qtyLolos;

                // Logika untuk sisa barang yang tidak di-QC tetap sama
                $sisa = $detail->qty_terima - $totalInput;
                if ($sisa > 0) {
                    $quarantineRak = Rak::where('gudang_id', $receiving->gudang_id)
                                        ->where('kode_rak', 'like', '%-KRN-QC')
                                        ->first();
                    if (!$quarantineRak) {
                        throw new \Exception('Rak karantina (dengan akhiran kode -KRN-QC) tidak ditemukan di gudang ini.');
                    }

                    $inventory = \App\Models\Inventory::firstOrCreate(
                        ['part_id' => $detail->part_id, 'rak_id' => $quarantineRak->id],
                        ['gudang_id' => $receiving->gudang_id, 'quantity' => 0]
                    );

                    $stokSebelum = $inventory->quantity;
                    $inventory->increment('quantity', $sisa);

                    \App\Models\StockMovement::create([
                        'part_id' => $detail->part_id,
                        'gudang_id' => $receiving->gudang_id,
                        'rak_id' => $quarantineRak->id,
                        'tipe_gerakan' => 'KARANTINA_QC',
                        'jumlah' => $sisa,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $inventory->quantity,
                        'referensi' => 'Receiving:' . $receiving->id,
                        'user_id' => Auth::id(),
                        'keterangan' => 'Sisa barang dari proses QC otomatis masuk karantina.',
                    ]);
                }
            }

            // Cek apakah ada barang yang lolos QC.
            if ($totalLolos > 0) {
                // Jika ada, maka siap untuk Putaway.
                $receiving->status = 'PENDING_PUTAWAY';
            } else {
                // Jika tidak ada sama sekali, proses penerimaan ini selesai.
                // Barang yang gagal akan diproses di menu Retur Pembelian.
                $receiving->status = 'COMPLETED';
            }

            $receiving->qc_by = Auth::id();
            $receiving->qc_at = now();
            $receiving->save();

            DB::commit();
            return redirect()->route('admin.qc.index')->with('success', 'Hasil QC berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'GAGAL DISIMPAN: ' . $e->getMessage())->withInput();
        }
    }
}
