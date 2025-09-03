<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\Receiving;
use App\Models\ReceivingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $this->authorize('manage-purchase-returns');
        $returns = PurchaseReturn::with(['supplier', 'receiving'])->latest()->get();
        return view('admin.purchase_returns.index', compact('returns'));
    }

    public function create()
    {
        $this->authorize('manage-purchase-returns');
        // Get receiving documents that have failed items which have not been fully returned yet
        $receivings = Receiving::whereHas('details', function ($query) {
            $query->where('qty_gagal_qc', '>', DB::raw('qty_diretur'));
        })->get();

        return view('admin.purchase_returns.create', compact('receivings'));
    }

    // API Endpoint
    public function getFailedItems(Receiving $receiving)
    {
        $items = $receiving->details()
            ->with('part')
            ->where('qty_gagal_qc', '>', DB::raw('qty_diretur'))
            ->get();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $this->authorize('manage-purchase-returns');
        $request->validate([
            'receiving_id' => 'required|exists:receivings,id',
            'tanggal_retur' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.qty_retur' => 'required|integer|min:1',
            'items.*.alasan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $receiving = Receiving::findOrFail($request->receiving_id);

            $return = PurchaseReturn::create([
                'nomor_retur' => $this->generateReturnNumber(),
                'receiving_id' => $receiving->id,
                'supplier_id' => $receiving->purchaseOrder->supplier_id,
                'tanggal_retur' => $request->tanggal_retur,
                'catatan' => $request->catatan,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $detailId => $data) {
                $detail = ReceivingDetail::findOrFail($detailId);
                $qtyToReturn = $data['qty_retur'];
                $availableToReturn = $detail->qty_gagal_qc - $detail->qty_diretur;

                if ($qtyToReturn > $availableToReturn) {
                    throw new \Exception("Jumlah retur untuk part {$detail->part->nama_part} melebihi jumlah yang tersedia untuk diretur.");
                }

                $return->details()->create([
                    'part_id' => $detail->part_id,
                    'qty_retur' => $qtyToReturn,
                    'alasan' => $data['alasan'],
                ]);

                // Update the returned quantity on the receiving detail
                $detail->qty_diretur += $qtyToReturn;
                $detail->save();
            }

            DB::commit();
            return redirect()->route('admin.purchase-returns.index')->with('success', 'Dokumen retur pembelian berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $this->authorize('manage-purchase-returns');
        $purchaseReturn->load(['supplier', 'receiving.purchaseOrder', 'details.part']);
        return view('admin.purchase_returns.show', compact('purchaseReturn'));
    }

    private function generateReturnNumber()
    {
        $date = now()->format('Ymd');
        $latest = PurchaseReturn::whereDate('created_at', today())->count();
        $sequence = str_pad($latest + 1, 4, '0', STR_PAD_LEFT);
        return "RTN/{$date}/{$sequence}";
    }
}
