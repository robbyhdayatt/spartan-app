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
        
        // Validasi akses gudang
        $user = Auth::user();
        if (!in_array($user->jabatan->singkatan, ['SA', 'MA']) && $receiving->gudang_id !== $user->gudang_id) {
            abort(403, 'Anda tidak memiliki akses ke data gudang ini.');
        }

        if ($receiving->status !== 'PENDING_QC') {
            return redirect()->route('admin.qc.index')->with('error', 'Penerimaan ini sudah diproses QC.');
        }
        
        // --- PERBAIKAN DISINI ---
        // Kita filter relasi 'details'. Hanya ambil item yang qty_terima LEBIH DARI 0.
        // Item yang qty_terima 0 (tidak dikirim supplier) tidak perlu muncul di form QC.
        $receiving->load(['details' => function ($query) {
            $query->where('qty_terima', '>', 0);
        }, 'details.part']);
        // ------------------------

        // Pengecekan tambahan (Opsional):
        // Jika setelah difilter ternyata kosong (artinya semua item qty 0), 
        // kita bisa langsung auto-close atau tampilkan pesan error.
        if ($receiving->details->isEmpty()) {
             // Opsional: Bisa langsung update jadi COMPLETED jika mau
             // $receiving->update(['status' => 'COMPLETED']);
             // return redirect()->route('admin.qc.index')->with('info', 'Penerimaan ini tidak memiliki item untuk di-QC (Qty 0).');
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

                $qtyLolos = (int) $data['qty_lolos'];
                $qtyGagal = (int) $data['qty_gagal'];
                $totalInput = $qtyLolos + $qtyGagal;

                if ($totalInput !== $detail->qty_terima) {
                    $selisih = $detail->qty_terima - $totalInput;
                    $pesanError = $selisih > 0 
                        ? "Masih ada $selisih item yang belum dialokasikan (Lolos/Gagal)." 
                        : "Jumlah input melebihi stok diterima sebanyak " . abs($selisih) . " item.";
                        
                    throw new \Exception("Validasi Gagal pada part {$detail->part->nama_part}: $pesanError");
                }

                $detail->update([
                    'qty_lolos_qc' => $qtyLolos,
                    'qty_gagal_qc' => $qtyGagal,
                    'catatan_qc' => $data['catatan_qc'],
                ]);

                $totalLolos += $qtyLolos;

                if ($qtyGagal > 0) {
                    $quarantineRak = Rak::where('gudang_id', $receiving->gudang_id)
                                        ->where('kode_rak', 'like', '%-KRN-QC')
                                        ->first();
                    
                    if (!$quarantineRak) {
                        $quarantineRak = Rak::where('gudang_id', $receiving->gudang_id)
                                            ->where('tipe_rak', 'QUARANTINE')
                                            ->first();
                    }

                    if (!$quarantineRak) {
                        throw new \Exception('Rak karantina belum disetting di gudang ini.');
                    }

                    $inventory = \App\Models\InventoryBatch::firstOrCreate(
                        [
                            'part_id' => $detail->part_id,
                            'rak_id' => $quarantineRak->id,
                            'receiving_detail_id' => $detail->id,
                        ],
                        [
                            'gudang_id' => $receiving->gudang_id,
                            'quantity' => 0
                        ]
                    );

                    // 3. Update Stok
                    $stokSebelum = $inventory->quantity;
                    $inventory->increment('quantity', $qtyGagal);

                    // 4. Catat Pergerakan Stok (Mutation History)
                    \App\Models\StockMovement::create([
                        'part_id' => $detail->part_id,
                        'gudang_id' => $receiving->gudang_id,
                        'rak_id' => $quarantineRak->id,
                        'tipe_gerakan' => 'KARANTINA_QC',
                        'jumlah' => $qtyGagal,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $inventory->quantity,
                        'referensi' => 'QC Receiving:' . $receiving->id,
                        'user_id' => Auth::id(),
                        'keterangan' => 'Barang gagal QC (Batch ID: ' . $inventory->id . ')',
                    ]);
                }
            }

            $receiving->status = ($totalLolos > 0) ? 'PENDING_PUTAWAY' : 'COMPLETED';
            $receiving->qc_by = Auth::id();
            $receiving->qc_at = now();
            $receiving->save();

            DB::commit();
            return redirect()->route('admin.qc.index')->with('success', 'Hasil QC berhasil disimpan. Stok ' . $totalLolos . ' item siap untuk Putaway.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan QC: ' . $e->getMessage())->withInput();
        }
    }
}