<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Gudang;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        // Eager load relationships for efficiency
        $purchaseOrders = PurchaseOrder::with(['supplier', 'gudang', 'createdBy'])
            ->latest()
            ->paginate(15);

        return view('admin.purchase_orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $this->authorize('create-po');
        $user = auth()->user();
        $suppliers = \App\Models\Supplier::where('is_active', true)->orderBy('nama_supplier')->get();

        // Cek peran pengguna
        if ($user->jabatan->nama_jabatan === 'PJ Gudang') {
            // Jika PJ Gudang, hanya ambil gudang tempat dia ditugaskan
            $gudangs = \App\Models\Gudang::where('id', $user->gudang_id)->get();
        } else {
            // Jika bukan, ambil semua gudang (untuk Super Admin, dll.)
            $gudangs = \App\Models\Gudang::where('is_active', true)->orderBy('nama_gudang')->get();
        }

        $today = now()->toDateString();
        $parts = \App\Models\Part::where('parts.is_active', true)
            ->leftJoin('campaigns', function ($join) use ($today) {
                $join->on('parts.id', '=', 'campaigns.part_id')
                    ->where('campaigns.is_active', true)
                    ->where('campaigns.tipe', 'PEMBELIAN')
                    ->where('campaigns.tanggal_mulai', '<=', $today)
                    ->where('campaigns.tanggal_selesai', '>=', $today);
            })
            ->select('parts.*', DB::raw('COALESCE(campaigns.harga_promo, parts.harga_beli_default) as effective_price'))
            ->orderBy('nama_part')->get();

        return view('admin.purchase_orders.create', compact('suppliers', 'gudangs', 'parts'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-po');
        // Basic validation for header and at least one item
        $request->validate([
            'tanggal_po' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'gudang_id' => 'required|exists:gudangs,id',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0',
        ]);

        // Use a database transaction to ensure data integrity
        DB::beginTransaction();
        try {
            // Create the PO Header
            $po = PurchaseOrder::create([
                'nomor_po' => $this->generatePoNumber(),
                'tanggal_po' => $request->tanggal_po,
                'supplier_id' => $request->supplier_id,
                'gudang_id' => $request->gudang_id,
                'catatan' => $request->catatan,
                'status' => 'PENDING_APPROVAL', // Status awal
                'created_by' => Auth::id(),
            ]);

            $totalAmount = 0;

            // Create PO Details
            foreach ($request->items as $item) {
                $subtotal = $item['qty'] * $item['harga'];
                $po->details()->create([
                    'part_id' => $item['part_id'],
                    'qty_pesan' => $item['qty'],
                    'harga_beli' => $item['harga'],
                    'subtotal' => $subtotal,
                ]);
                $totalAmount += $subtotal;
            }

            // Update total amount in the PO header
            $po->total_amount = $totalAmount;
            $po->save();

            DB::commit(); // If all good, save the records
            return redirect()->route('admin.purchase-orders.index')->with('success', 'Purchase Order berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack(); // If any error, rollback
            return back()->with('error', 'Terjadi kesalahan saat membuat PO: ' . $e->getMessage())->withInput();
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        // Eager load all relationships for the detail view
        $purchaseOrder->load(['supplier', 'gudang', 'createdBy', 'details.part']);
        return view('admin.purchase_orders.show', compact('purchaseOrder'));
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        // Logic to prevent deletion of approved POs
        if ($purchaseOrder->status != 'DRAFT' && $purchaseOrder->status != 'PENDING_APPROVAL') {
            return back()->with('error', 'Hanya PO dengan status Draft atau Pending Approval yang bisa dihapus.');
        }

        $purchaseOrder->delete();
        return redirect()->route('admin.purchase-orders.index')->with('success', 'Purchase Order berhasil dihapus.');
    }

    // Helper function to generate a unique PO number
    private function generatePoNumber()
    {
        $date = now()->format('Ymd');
        $latestPo = PurchaseOrder::whereDate('created_at', today())->count();
        $sequence = str_pad($latestPo + 1, 4, '0', STR_PAD_LEFT);
        return "PO/{$date}/{$sequence}";
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('perform-approval', $purchaseOrder);
        // Otorisasi menggunakan Gate
        $this->authorize('approve-po', $purchaseOrder);

        // Validasi status
        if ($purchaseOrder->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Hanya PO yang berstatus PENDING APPROVAL yang bisa disetujui.');
        }

        $purchaseOrder->status = 'APPROVED';
        $purchaseOrder->approved_by = Auth::id();
        $purchaseOrder->approved_at = now();
        $purchaseOrder->save();

        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)->with('success', 'Purchase Order berhasil disetujui.');
    }

    public function reject(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('perform-approval', $purchaseOrder);
        // Otorisasi menggunakan Gate
        $this->authorize('approve-po', $purchaseOrder);

        // Validasi status
        if ($purchaseOrder->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Hanya PO yang berstatus PENDING APPROVAL yang bisa ditolak.');
        }

        $purchaseOrder->status = 'REJECTED';
        $purchaseOrder->save();

        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)->with('success', 'Purchase Order berhasil ditolak.');
    }
}
