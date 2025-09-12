<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Part; // <-- Tambahkan ini
use App\Models\PurchaseOrderDetail; // <-- Tambahkan ini
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
        $user = Auth::user();

        // Memulai query dasar untuk mengambil data penerimaan yang sudah lolos QC
        $query = \App\Models\Receiving::where('status', 'PENDING_PUTAWAY')
                                    ->with(['purchaseOrder', 'gudang']);

        // Terapkan filter gudang berdasarkan peran
        if (!in_array($user->jabatan->singkatan, ['SA', 'MA'])) {
            $query->where('gudang_id', $user->gudang_id);
        }

        // Ambil data setelah difilter dan diurutkan
        $receivings = $query->latest('qc_at')->paginate(15);

        return view('admin.putaway.index', compact('receivings'));
    }

    // Show the form to assign shelves for a specific receiving record
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
                $detail = ReceivingDetail::with('part')->findOrFail($detailId);
                $part = $detail->part;
                $jumlahMasuk = $detail->qty_lolos_qc;

                if ($jumlahMasuk <= 0) {
                    continue;
                }

                $poDetail = PurchaseOrderDetail::where('purchase_order_id', $receiving->purchase_order_id)
                                            ->where('part_id', $part->id)
                                            ->first();

                // === PERBAIKAN DI SINI ===
                $hargaBeliBaru = $poDetail ? $poDetail->harga_beli : $part->harga_beli_default;

                $stokLama = Part::where('id', $part->id)->withSum('inventories', 'quantity')->first()->inventories_sum_quantity ?? 0;
                $hargaRataRataLama = $part->harga_beli_rata_rata;

                $totalNilaiLama = $stokLama * $hargaRataRataLama;
                $totalNilaiBaru = $jumlahMasuk * $hargaBeliBaru;
                $totalStokBaru = $stokLama + $jumlahMasuk;

                if ($totalStokBaru > 0) {
                    $hargaRataRataBaru = ($totalNilaiLama + $totalNilaiBaru) / $totalStokBaru;
                } else {
                    $hargaRataRataBaru = $hargaBeliBaru;
                }

                $part->harga_beli_rata_rata = $hargaRataRataBaru;
                $part->save();

                $inventory = Inventory::firstOrCreate(
                    ['part_id' => $detail->part_id, 'rak_id' => $data['rak_id']],
                    ['gudang_id' => $receiving->gudang_id, 'quantity' => 0]
                );

                $stokSebelum = $inventory->quantity;
                $stokSesudah = $stokSebelum + $jumlahMasuk;
                $inventory->quantity = $stokSesudah;
                $inventory->save();

                \App\Models\StockMovement::create([
                    'part_id' => $detail->part_id,
                    'gudang_id' => $receiving->gudang_id,
                    'tipe_gerakan' => 'PEMBELIAN',
                    'jumlah' => $jumlahMasuk,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi' => $receiving->nomor_penerimaan,
                    'user_id' => Auth::id(),
                ]);

                $detail->update(['qty_disimpan' => $jumlahMasuk]);
            }

            $receiving->status = 'COMPLETED';
            $receiving->save();

            DB::commit();
            return redirect()->route('admin.putaway.index')->with('success', 'Barang berhasil disimpan, stok, dan harga rata-rata telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}
