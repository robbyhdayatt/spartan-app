<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesReturn;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Inventory;
use App\Models\Rak;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function index()
    {
        $this->authorize('view-sales-returns'); // <-- KODE YANG BENAR
        $returns = SalesReturn::with(['konsumen', 'penjualan'])->latest()->get();
        return view('admin.sales_returns.index', compact('returns'));
    }

    public function create()
    {
        $this->authorize('manage-sales-returns');
        // Get sales invoices that have items which can still be returned
        $penjualans = Penjualan::whereHas('details', function ($query) {
            $query->where(DB::raw('qty_jual - qty_diretur'), '>', 0);
        })->get();

        return view('admin.sales_returns.create', compact('penjualans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'penjualan_id' => 'required|exists:penjualans,id',
            'tanggal_retur' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.qty_retur' => 'required|integer|min:1',
        ]);

        $penjualan = Penjualan::findOrFail($request->penjualan_id);

        if (empty($request->items)) {
            return redirect()->back()->with('error', 'Tidak ada item yang dipilih untuk diretur.');
        }

        DB::beginTransaction();
        try {
            // --- LOGIKA BARU YANG LEBIH TANGGUH ---
            // Cari atau buat rak karantina di gudang yang sama
            $rakKarantina = Rak::firstOrCreate(
                ['gudang_id' => $penjualan->gudang_id, 'tipe_rak' => 'KARANTINA'],
                ['nama_rak' => 'RAK KARANTINA', 'kode_rak' => 'KARANTINA-'.$penjualan->gudang->kode_gudang]
            );
            // --- AKHIR LOGIKA BARU ---

            $salesReturn = SalesReturn::create([
                'nomor_retur_jual' => SalesReturn::generateReturnNumber(),
                'penjualan_id' => $penjualan->id,
                'konsumen_id' => $penjualan->konsumen_id,
                'gudang_id' => $penjualan->gudang_id,
                'tanggal_retur' => $request->tanggal_retur,
                'catatan' => $request->catatan,
                'created_by' => auth()->id(),
                'total_retur' => 0, // Akan di-update nanti
            ]);

            $subtotalRetur = 0;

            foreach ($request->items as $penjualanDetailId => $itemData) {
                $penjualanDetail = PenjualanDetail::findOrFail($penjualanDetailId);
                $qtyRetur = (int)$itemData['qty_retur'];
                $maxQty = $penjualanDetail->qty_jual - $penjualanDetail->qty_diretur;

                if ($qtyRetur <= 0 || $qtyRetur > $maxQty) {
                    throw new \Exception("Jumlah retur untuk part {$penjualanDetail->part->nama_part} tidak valid.");
                }

                $itemSubtotal = $penjualanDetail->harga_jual * $qtyRetur;
                $subtotalRetur += $itemSubtotal;

                $salesReturn->details()->create([
                    'part_id' => $penjualanDetail->part_id,
                    'qty_retur' => $qtyRetur,
                    'harga_saat_jual' => $penjualanDetail->harga_jual,
                    'subtotal' => $itemSubtotal,
                ]);

                // Update jumlah yang sudah diretur pada detail penjualan asli
                $penjualanDetail->increment('qty_diretur', $qtyRetur);

                // Tambahkan stok ke rak karantina
                $inventory = Inventory::firstOrCreate(
                    ['part_id' => $penjualanDetail->part_id, 'rak_id' => $rakKarantina->id],
                    ['gudang_id' => $penjualan->gudang_id, 'quantity' => 0]
                );

                $stokSebelum = $inventory->quantity;
                $inventory->increment('quantity', $qtyRetur);

                // --- TAMBAHKAN PENCATATAN STOCK MOVEMENT ---
                StockMovement::create([
                    'part_id'       => $penjualanDetail->part_id,
                    'gudang_id'     => $penjualan->gudang_id,
                    'tipe_gerakan'  => 'RETUR_JUAL',
                    'jumlah'        => $qtyRetur, // Positif karena stok masuk
                    'stok_sebelum'  => $stokSebelum,
                    'stok_sesudah'  => $inventory->quantity,
                    'referensi'     => $salesReturn->nomor_retur_jual,
                    'user_id'       => auth()->id(),
                ]);
            }

            // Hitung ulang total retur
            $taxRate = ($penjualan->subtotal > 0 && $penjualan->pajak > 0) ? ($penjualan->pajak / $penjualan->subtotal) : 0;
            $pajakRetur = $subtotalRetur * $taxRate;
            $totalRetur = $subtotalRetur + $pajakRetur;

            $salesReturn->update([
                'subtotal' => $subtotalRetur,
                'pajak' => $pajakRetur,
                'total_retur' => $totalRetur,
            ]);

            DB::commit();

            return redirect()->route('admin.sales-returns.show', $salesReturn)->with('success', 'Retur penjualan berhasil dibuat dan barang telah masuk ke karantina.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(SalesReturn $salesReturn)
    {
        $this->authorize('manage-sales-returns');
        $salesReturn->load(['konsumen', 'penjualan', 'details.part']);
        return view('admin.sales_returns.show', compact('salesReturn'));
    }

    // Fungsi ini sudah tidak digunakan karena generate nomor ada di Model, tapi kita biarkan saja.
    private function generateReturnNumber()
    {
        $date = now()->format('Ymd');
        $latest = SalesReturn::whereDate('created_at', today())->count();
        $sequence = str_pad($latest + 1, 4, '0', STR_PAD_LEFT);
        return "RTS/{$date}/{$sequence}";
    }

    /**
     * Mengambil item dari faktur penjualan yang bisa diretur via API.
     *
     * @param  \App\Models\Penjualan  $penjualan
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReturnableItems(Penjualan $penjualan)
    {
        $penjualan->load('details.part');

        $returnableItems = $penjualan->details->filter(function ($detail) {
            return $detail->qty_jual > $detail->qty_diretur;
        })->values();

        return response()->json($returnableItems);
    }
}
