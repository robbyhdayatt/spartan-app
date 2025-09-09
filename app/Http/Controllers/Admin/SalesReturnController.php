<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesReturn;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Inventory;
use App\Models\Rak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function index()
    {
        $this->authorize('manage-sales-returns');
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

        // Cek apakah ada item yang valid untuk diretur
        if (empty($request->items)) {
            return redirect()->back()->with('error', 'Tidak ada item yang dipilih untuk diretur.');
        }

        $subtotalRetur = 0;

        // Gunakan DB Transaction untuk memastikan integritas data
        DB::beginTransaction();
        try {
            // Buat Dokumen Induk Retur
            $salesReturn = SalesReturn::create([
                'nomor_retur_jual' => SalesReturn::generateReturnNumber(),
                'penjualan_id' => $penjualan->id,
                'konsumen_id' => $penjualan->konsumen_id,
                'gudang_id' => $penjualan->gudang_id,
                'tanggal_retur' => $request->tanggal_retur,
                'catatan' => $request->catatan,
                'created_by' => auth()->id(),
                'total_retur' => 0, // Akan kita update nanti
            ]);

            foreach ($request->items as $penjualanDetailId => $itemData) {
                $penjualanDetail = PenjualanDetail::findOrFail($penjualanDetailId);
                $qtyRetur = (int)$itemData['qty_retur'];
                $maxQty = $penjualanDetail->qty_jual - $penjualanDetail->qty_diretur;

                if ($qtyRetur <= 0 || $qtyRetur > $maxQty) {
                    // Jika user mencoba meretur lebih dari yang seharusnya, batalkan proses
                    throw new \Exception("Jumlah retur untuk part {$penjualanDetail->part->nama_part} tidak valid.");
                }

                // Hitung subtotal untuk item ini
                $itemSubtotal = $penjualanDetail->harga_jual * $qtyRetur;
                $subtotalRetur += $itemSubtotal;

                // Buat Detail Retur
                $salesReturn->details()->create([
                    'part_id' => $penjualanDetail->part_id,
                    'qty_retur' => $qtyRetur,
                    'harga_saat_jual' => $penjualanDetail->harga_jual,
                    'subtotal' => $itemSubtotal,
                ]);

                // Update kuantitas yang sudah diretur di detail penjualan
                $penjualanDetail->increment('qty_diretur', $qtyRetur);

                // **PERBAIKAN: Gunakan firstOrCreate untuk membuat rak karantina jika belum ada**
                $rakKarantina = Rak::firstOrCreate(
                    [
                        'gudang_id' => $penjualan->gudang_id,
                        'kode_rak'  => 'KARANTINA-RETUR-JUAL' // Kunci unik untuk pencarian
                    ],
                    [
                        'nama_rak'  => 'Karantina Retur Penjualan', // Data ini hanya dipakai jika membuat baru
                        'tipe_rak'  => 'KARANTINA_RETUR',
                        'is_active' => 1
                    ]
                );

                $inventory = Inventory::firstOrCreate(
                    ['part_id' => $penjualanDetail->part_id, 'rak_id' => $rakKarantina->id],
                    ['gudang_id' => $penjualan->gudang_id, 'quantity' => 0]
                );
                $inventory->increment('quantity', $qtyRetur);
            }

            // === KALKULASI PAJAK DAN TOTAL AKHIR ===
            $taxRate = 0;
            // Cek apakah faktur penjualan asli punya subtotal dan pajak
            if ($penjualan->subtotal > 0 && $penjualan->pajak > 0) {
                 // Gunakan kolom 'pajak' yang benar dari tabel penjualan, bukan 'ppn_jumlah'
                $taxRate = $penjualan->pajak / $penjualan->subtotal;
            }

            $pajakRetur = $subtotalRetur * $taxRate;
            $totalRetur = $subtotalRetur + $pajakRetur;

            // Update total dan subtotal di dokumen retur
            $salesReturn->subtotal = $subtotalRetur;
            $salesReturn->pajak = $pajakRetur;
            $salesReturn->total_retur = $totalRetur;
            $salesReturn->save();

            DB::commit();

            return redirect()->route('admin.sales-returns.show', $salesReturn)->with('success', 'Retur penjualan berhasil dibuat.');

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