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

    // API Endpoint
    public function getReturnableItems(Penjualan $penjualan)
    {
        $items = $penjualan->details()
            ->with('part')
            ->where(DB::raw('qty_jual - qty_diretur'), '>', 0)
            ->get();

        return response()->json($items);
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

        DB::beginTransaction();
        try {
            $penjualan = Penjualan::findOrFail($request->penjualan_id);

            $return = SalesReturn::create([
                'nomor_retur_jual' => $this->generateReturnNumber(),
                'penjualan_id' => $penjualan->id,
                'konsumen_id' => $penjualan->konsumen_id,
                'gudang_id' => $penjualan->gudang_id,
                'tanggal_retur' => $request->tanggal_retur,
                'catatan' => $request->catatan,
                'created_by' => Auth::id(),
            ]);

            $totalRetur = 0;

            foreach ($request->items as $detailId => $data) {
                $detail = PenjualanDetail::findOrFail($detailId);
                $qtyToReturn = $data['qty_retur'];
                $availableToReturn = $detail->qty_jual - $detail->qty_diretur;

                if ($qtyToReturn > $availableToReturn) {
                    throw new \Exception("Jumlah retur part {$detail->part->nama_part} melebihi jumlah yang dibeli.");
                }

                $subtotal = $qtyToReturn * $detail->harga_jual;
                $return->details()->create([
                    'part_id' => $detail->part_id,
                    'qty_retur' => $qtyToReturn,
                    'harga_saat_jual' => $detail->harga_jual,
                    'subtotal' => $subtotal,
                ]);

                // Update returned qty on original sales detail
                $detail->qty_diretur += $qtyToReturn;
                $detail->save();

                // Add stock back to the quarantine shelf
                $quarantineRak = Rak::where('gudang_id', $penjualan->gudang_id)
                    ->where('kode_rak', 'like', '%-KRN-RT')
                    ->firstOrFail();

                $inventory = Inventory::firstOrCreate(
                    ['part_id' => $detail->part_id, 'rak_id' => $quarantineRak->id],
                    ['gudang_id' => $penjualan->gudang_id, 'quantity' => 0]
                );

                $stokSebelum = $inventory->quantity;
                $inventory->quantity += $qtyToReturn;
                $inventory->save();

                // Log stock movement
                \App\Models\StockMovement::create([
                    'part_id' => $detail->part_id,
                    'gudang_id' => $penjualan->gudang_id,
                    'tipe_gerakan' => 'RETUR_PENJUALAN',
                    'jumlah' => $qtyToReturn, // Positive
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $inventory->quantity,
                    'referensi' => $return->nomor_retur_jual,
                    'user_id' => Auth::id(),
                ]);

                $totalRetur += $subtotal;
            }

            $return->total_retur = $totalRetur;
            $return->save();

            DB::commit();
            return redirect()->route('admin.sales-returns.index')->with('success', 'Retur penjualan berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(SalesReturn $salesReturn)
    {
        $this->authorize('manage-sales-returns');
        $salesReturn->load(['konsumen', 'penjualan', 'details.part']);
        return view('admin.sales_returns.show', compact('salesReturn'));
    }

    private function generateReturnNumber()
    {
        $date = now()->format('Ymd');
        $latest = SalesReturn::whereDate('created_at', today())->count();
        $sequence = str_pad($latest + 1, 4, '0', STR_PAD_LEFT);
        return "RTS/{$date}/{$sequence}";
    }
}
