<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesReturn;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\InventoryBatch; // DIUBAH
use App\Models\Rak;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function index()
    {
        $this->authorize('view-sales-returns');
        $returns = SalesReturn::with(['konsumen', 'penjualan'])->latest()->get();
        return view('admin.sales_returns.index', compact('returns'));
    }

    public function create()
    {
        $this->authorize('manage-sales-returns');
        $penjualans = Penjualan::whereHas('details', function ($query) {
            $query->where(DB::raw('qty_jual - qty_diretur'), '>', 0);
        })->latest()->get();

        return view('admin.sales_returns.create', compact('penjualans'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage-sales-returns');
        $request->validate([
            'penjualan_id' => 'required|exists:penjualans,id',
            'tanggal_retur' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.qty_retur' => 'required|integer|min:1',
        ]);

        $penjualan = Penjualan::findOrFail($request->penjualan_id);

        try {
            DB::transaction(function () use ($request, $penjualan) {
                // Cari atau buat rak karantina di gudang yang sama
                $rakKarantina = Rak::firstOrCreate(
                    ['gudang_id' => $penjualan->gudang_id, 'tipe_rak' => 'KARANTINA'],
                    ['nama_rak' => 'RAK KARANTINA RETUR', 'kode_rak' => $penjualan->gudang->kode_gudang . '-KRN-RT']
                );

                $salesReturn = SalesReturn::create([
                    'nomor_retur_jual' => SalesReturn::generateReturnNumber(),
                    'penjualan_id' => $penjualan->id,
                    'konsumen_id' => $penjualan->konsumen_id,
                    'gudang_id' => $penjualan->gudang_id,
                    'tanggal_retur' => $request->tanggal_retur,
                    'catatan' => $request->catatan,
                    'created_by' => auth()->id(),
                    'total_retur' => 0, // Placeholder
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

                    $penjualanDetail->increment('qty_diretur', $qtyRetur);

                    // LOGIKA BARU: Buat InventoryBatch baru di rak karantina
                    $newBatch = InventoryBatch::create([
                        'part_id' => $penjualanDetail->part_id,
                        'rak_id' => $rakKarantina->id,
                        'gudang_id' => $penjualan->gudang_id,
                        'quantity' => $qtyRetur,
                        'receiving_detail_id' => null, // Tidak berasal dari PO
                    ]);

                    // LOGIKA BARU: Catat pergerakan stok dengan format yang benar
                    StockMovement::create([
                        'part_id' => $penjualanDetail->part_id,
                        'gudang_id' => $penjualan->gudang_id,
                        'rak_id' => $rakKarantina->id,
                        'jumlah' => $qtyRetur,
                        'stok_sebelum' => 0, // Stok di batch baru ini selalu mulai dari 0
                        'stok_sesudah' => $qtyRetur,
                        'referensi_type' => get_class($salesReturn),
                        'referensi_id' => $salesReturn->id,
                        'keterangan' => 'Retur Penjualan: ' . $salesReturn->nomor_retur_jual,
                        'user_id' => auth()->id(),
                    ]);
                }

                // Hitung ulang total
                $taxRate = ($penjualan->subtotal > 0 && $penjualan->pajak > 0) ? ($penjualan->pajak / $penjualan->subtotal) : 0;
                $pajakRetur = $subtotalRetur * $taxRate;

                $salesReturn->update([
                    'subtotal' => $subtotalRetur,
                    'pajak' => $pajakRetur,
                    'total_retur' => $subtotalRetur + $pajakRetur,
                ]);
            });

            return redirect()->route('admin.sales-returns.index')->with('success', 'Retur penjualan berhasil dibuat dan barang telah masuk ke karantina.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(SalesReturn $salesReturn)
    {
        $this->authorize('view-sales-returns');
        $salesReturn->load(['konsumen', 'penjualan', 'details.part']);
        return view('admin.sales_returns.show', compact('salesReturn'));
    }

    public function getReturnableItems(Penjualan $penjualan)
    {
        $penjualan->load('details.part');
        $returnableItems = $penjualan->details->filter(function ($detail) {
            return $detail->qty_jual > $detail->qty_diretur;
        })->map(function ($detail) {
            // Tambahkan max_returnable agar mudah divalidasi di frontend
            $detail->max_returnable = $detail->qty_jual - $detail->qty_diretur;
            return $detail;
        })->values();

        return response()->json($returnableItems);
    }
}
