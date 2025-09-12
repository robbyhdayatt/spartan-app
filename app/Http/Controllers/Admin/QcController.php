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
        $this->authorize('can-qc');
        $user = Auth::user();

        // Memulai query dasar untuk mengambil data penerimaan yang menunggu QC
        $query = \App\Models\Receiving::where('status', 'PENDING_QC')
                                    ->with(['purchaseOrder', 'gudang']);

        // Terapkan filter gudang berdasarkan peran
        if (!in_array($user->jabatan->singkatan, ['SA', 'MA'])) {
            $query->where('gudang_id', $user->gudang_id);
        }

        // Ambil data setelah difilter dan diurutkan
        $receivings = $query->latest('tanggal_terima')->paginate(15);

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

    // Validasi input dari form
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

            $qtyLolos = (int) $data['qty_lolos'];
            $qtyGagal = (int) $data['qty_gagal'];
            $totalInput = $qtyLolos + $qtyGagal;

            if ($totalInput > $detail->qty_terima) {
                throw new \Exception('Jumlah Lolos & Gagal QC (' . $totalInput . ') melebihi jumlah diterima (' . $detail->qty_terima . ') untuk part ' . $detail->part->nama_part);
            }

            // Update receiving_details dengan hasil QC
            $detail->update([
                'qty_lolos_qc' => $qtyLolos,
                'qty_gagal_qc' => $qtyGagal,
                'catatan_qc' => $data['catatan_qc'],
            ]);

            // Hitung sisa barang yang tidak di-QC untuk dikarantina
            $sisa = $detail->qty_terima - $totalInput;
            if ($sisa > 0) {
                // Cari rak karantina, beri error jika tidak ada
                $quarantineRak = Rak::where('gudang_id', $receiving->gudang_id)
                                    ->where('kode_rak', 'like', '%-KRN-QC')
                                    ->first();
                if (!$quarantineRak) {
                    throw new \Exception('Rak karantina (dengan akhiran kode -KRN-QC) tidak ditemukan di gudang ini. Silakan buat terlebih dahulu.');
                }

                // Update atau buat inventaris di rak karantina
                $inventory = \App\Models\Inventory::firstOrCreate(
                    ['part_id' => $detail->part_id, 'rak_id' => $quarantineRak->id],
                    ['gudang_id' => $receiving->gudang_id, 'quantity' => 0]
                );

                $stokSebelum = $inventory->quantity;
                $inventory->increment('quantity', $sisa);

                // --- PERBAIKAN UTAMA ---
                // Membuat Stock Movement secara manual sesuai skema database Anda
                \App\Models\StockMovement::create([
                    'part_id'       => $detail->part_id,
                    'gudang_id'     => $receiving->gudang_id,
                    'rak_id'        => $quarantineRak->id,
                    'tipe_gerakan'  => 'KARANTINA_QC', // Menggunakan kolom 'tipe_gerakan'
                    'jumlah'        => $sisa,           // Menggunakan kolom 'jumlah'
                    'stok_sebelum'  => $stokSebelum,
                    'stok_sesudah'  => $inventory->quantity,
                    'referensi'     => 'Receiving:' . $receiving->id, // Menggunakan kolom 'referensi' (varchar)
                    'user_id'       => Auth::id(),
                    'keterangan'    => 'Sisa barang dari proses QC otomatis masuk karantina.',
                ]);
            }
        }

        // Update status dokumen penerimaan utama
        $receiving->status = 'PENDING_PUTAWAY';
        $receiving->qc_by = Auth::id();
        $receiving->qc_at = now();
        $receiving->save();

        DB::commit();
        return redirect()->route('admin.qc.index')->with('success', 'Hasil QC berhasil disimpan.');

    } catch (\Exception $e) {
        DB::rollBack();
        // Mengembalikan dengan pesan error yang spesifik agar bisa ditampilkan di view
        return back()->with('error', 'GAGAL DISIMPAN: ' . $e->getMessage())->withInput();
    }
}
}
