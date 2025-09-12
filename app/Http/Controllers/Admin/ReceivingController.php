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

        // Memulai query dasar
        $query = \App\Models\Receiving::with(['purchaseOrder', 'gudang', 'receivedBy']);

        // Terapkan filter gudang berdasarkan peran
        if (!in_array($user->jabatan->singkatan, ['SA', 'MA'])) {
            $query->where('gudang_id', $user->gudang_id);
        }

        // Ambil data setelah difilter dan diurutkan
        $receivings = $query->latest()->paginate(15); // Menggunakan paginate untuk halaman yang lebih rapi

        return view('admin.receivings.index', compact('receivings'));
    }

    public function create()
    {
        $this->authorize('can-receive');

        $user = Auth::user();

        // Memulai query untuk mengambil PO yang statusnya APPROVED
        $query = PurchaseOrder::where('status', 'APPROVED');

        // Terapkan filter gudang HANYA jika user BUKAN Super Admin atau Manajer Area
        if (!in_array($user->jabatan->singkatan, ['SA'])) {
            $query->where('gudang_id', $user->gudang_id);
        }

        $purchaseOrders = $query->orderBy('tanggal_po', 'desc')->get();

        return view('admin.receivings.create', compact('purchaseOrders'));
    }

    // API endpoint to fetch PO details for the form
    public function getPoDetails(PurchaseOrder $purchaseOrder)
    {
        // Eager load relasi yang dibutuhkan (details dan part di dalamnya)
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
            $po = PurchaseOrder::with('details')->findOrFail($request->purchase_order_id);

            // Create the Receiving Header (with the call to the private method)
            $receiving = Receiving::create([
                'purchase_order_id' => $po->id,
                'gudang_id' => $po->gudang_id,
                'nomor_penerimaan' => $this->generateReceivingNumber(),
                'tanggal_terima' => $request->tanggal_terima,
                'status' => 'PENDING_QC',
                'catatan' => $request->catatan,
                'received_by' => Auth::id(),
            ]);

            // Loop through items to create receiving details and update PO details
            foreach ($request->items as $partId => $itemData) {
                // Find the corresponding detail line in the Purchase Order
                $poDetail = $po->details->firstWhere('part_id', $partId);

                if ($poDetail) {
                    // Add the received quantity to the PO detail
                    $poDetail->qty_diterima += $itemData['qty_terima'];
                    $poDetail->save();
                }

                // Create the receiving detail line
                $receiving->details()->create([
                    'part_id' => $partId,
                    'qty_terima' => $itemData['qty_terima'],
                ]);
            }

            // Check if the PO is now fully or partially received
            $fullyReceived = true;
            // We need to refresh the PO details from the database to get the latest counts
            $po->refresh();
            foreach ($po->details as $detail) {
                if ($detail->qty_diterima < $detail->qty_pesan) {
                    $fullyReceived = false;
                    break;
                }
            }

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
        // Tambahkan 'createdBy' ke dalam list load
        $receiving->load('purchaseOrder.supplier', 'details.part', 'createdBy');

        $stockMovements = $receiving->stockMovements()->with('rak')->get();

        return view('admin.receivings.show', compact('receiving', 'stockMovements'));
    }

    private function generateReceivingNumber()
    {
        $date = now()->format('Ymd');
        $latest = Receiving::whereDate('created_at', today())->count();
        $sequence = str_pad($latest + 1, 4, '0', STR_PAD_LEFT);
        return "RCV/{$date}/{$sequence}";
    }
}
