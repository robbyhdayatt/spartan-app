<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receiving;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceivingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = \App\Models\Receiving::with(['purchaseOrder', 'gudang', 'receivedBy']);
        
        // Jika user BUKAN SA atau MA, maka filter berdasarkan gudang user tersebut.
        // AG (Admin Gudang) akan masuk ke kondisi ini, sehingga hanya melihat data gudangnya sendiri.
        if (!in_array($user->jabatan->singkatan, ['SA', 'MA'])) {
            $query->where('gudang_id', $user->gudang_id);
        }
        
        $receivings = $query->latest()->paginate(15);
        return view('admin.receivings.index', compact('receivings'));
    }

    public function create()
    {
        // Gate 'can-receive' sudah diupdate di AuthServiceProvider untuk mengizinkan 'AG'
        $this->authorize('can-receive');
        
        $user = Auth::user();
        $query = PurchaseOrder::whereIn('status', ['APPROVED', 'PARTIALLY_RECEIVED']);
        
        // Filter PO: Hanya SA yang bisa melihat PO lintas gudang. 
        // MA, KG, dan AG hanya melihat gudang mereka.
        if (!in_array($user->jabatan->singkatan, ['SA'])) {
            $query->where('gudang_id', $user->gudang_id);
        }
        
        $purchaseOrders = $query->orderBy('tanggal_po', 'desc')->get();
        return view('admin.receivings.create', compact('purchaseOrders'));
    }

    public function getPoDetails(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('details.part');
        return response()->json($purchaseOrder);
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'tanggal_terima' => 'required|date',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.qty_terima' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 1. Cek apakah ada minimal 1 barang yang qty_terima > 0
            $hasValidItems = collect($request->items)->contains(function ($item) {
                return $item['qty_terima'] > 0;
            });

            if (!$hasValidItems) {
                throw new \Exception("Minimal harus ada 1 barang yang diterima (Qty > 0).");
            }

            $po = PurchaseOrder::with('details')->findOrFail($request->purchase_order_id);

            $receiving = Receiving::create([
                'purchase_order_id' => $po->id,
                'gudang_id' => $po->gudang_id,
                'nomor_penerimaan' => Receiving::generateReceivingNumber(),
                'tanggal_terima' => $request->tanggal_terima,
                'status' => 'PENDING_QC',
                'catatan' => $request->catatan,
                'created_by' => Auth::id(),
                'received_by' => Auth::id(),
            ]);

            foreach ($request->items as $itemData) {
                $partId = $itemData['part_id']; 
                $qtyTerima = $itemData['qty_terima'];

                if ($qtyTerima <= 0) {
                    continue; 
                }

                $poDetail = $po->details->firstWhere('part_id', $partId);
                if ($poDetail) {
                    $poDetail->qty_diterima += $qtyTerima;
                    $poDetail->save();
                }
                
                $receiving->details()->create([
                    'part_id' => $partId,
                    'qty_terima' => $qtyTerima,
                ]);
            }

            $po->refresh();
            $fullyReceived = $po->details->every(fn($detail) => $detail->qty_diterima >= $detail->qty_pesan);

            $po->status = $fullyReceived ? 'FULLY_RECEIVED' : 'PARTIALLY_RECEIVED';
            $po->save();

            DB::commit();
            return redirect()->route('admin.receivings.index')->with('success', 'Data penerimaan barang berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Receiving $receiving)
    {
        $receiving->load('purchaseOrder.supplier', 'details.part', 'createdBy', 'receivedBy', 'qcBy', 'putawayBy');
        $stockMovements = $receiving->stockMovements()->with(['rak', 'user'])->get();

        return view('admin.receivings.show', compact('receiving', 'stockMovements'));
    }
}