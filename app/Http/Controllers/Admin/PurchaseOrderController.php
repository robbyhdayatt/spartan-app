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
use App\Services\DiscountService; // <-- IMPORT SERVICE KITA

class PurchaseOrderController extends Controller
{
    protected $discountService;

    // 1. Inject DiscountService melalui Constructor
    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'gudang', 'createdBy'])->latest()->get();
        return view('admin.purchase_orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $this->authorize('create-po');
        $user = auth()->user();
        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();

        if ($user->jabatan->nama_jabatan === 'PJ Gudang') {
            $gudangs = Gudang::where('id', $user->gudang_id)->get();
        } else {
            $gudangs = Gudang::where('is_active', true)->orderBy('nama_gudang')->get();
        }

        // Mengambil part sekarang lebih sederhana
        $parts = Part::where('is_active', true)->orderBy('nama_part')->get();

        return view('admin.purchase_orders.create', compact('suppliers', 'gudangs', 'parts'));
    }

    // 2. API BARU: Untuk mendapatkan harga beli part setelah diskon
    public function getPartPurchaseDetails(Part $part, Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        $supplier = Supplier::find($validated['supplier_id']);

        // Panggil DiscountService untuk kalkulasi harga beli
        $discountResult = $this->discountService->calculatePurchaseDiscount($part, $supplier, $part->harga_beli_default);

        return response()->json([
            'discount_result' => $discountResult,
        ]);
    }


    // 3. MODIFIKASI BESAR: store() sekarang menghitung ulang semua harga di server
    public function store(Request $request)
    {
        $this->authorize('create-po');
        $validated = $request->validate([
            'tanggal_po' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'gudang_id' => 'required|exists:gudangs,id',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.qty' => 'required|integer|min:1',
            'use_ppn' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $supplier = Supplier::find($validated['supplier_id']);
            $totalSubtotal = 0;
            $itemsToSave = [];

            foreach ($validated['items'] as $itemData) {
                $part = Part::find($itemData['part_id']);
                $qty = (int)$itemData['qty'];

                // HITUNG ULANG HARGA DI SERVER MENGGUNAKAN SERVICE
                $discountResult = $this->discountService->calculatePurchaseDiscount($part, $supplier, $part->harga_beli_default);
                $finalPrice = $discountResult['final_price'];
                $itemSubtotal = $qty * $finalPrice;
                $totalSubtotal += $itemSubtotal;

                $itemsToSave[] = [
                    'part_id' => $part->id,
                    'qty_pesan' => $qty,
                    'harga_beli' => $finalPrice,
                    'subtotal' => $itemSubtotal,
                ];
            }

            // Hitung Pajak dan Total
            $pajak = ($request->has('use_ppn') && $request->use_ppn) ? $totalSubtotal * 0.11 : 0;
            $totalAmount = $totalSubtotal + $pajak;

            // Buat Purchase Order
            $po = PurchaseOrder::create([
                'nomor_po' => $this->generatePoNumber(),
                'tanggal_po' => $validated['tanggal_po'],
                'supplier_id' => $validated['supplier_id'],
                'gudang_id' => $validated['gudang_id'],
                'catatan' => $request->catatan,
                'status' => 'PENDING_APPROVAL',
                'created_by' => Auth::id(),
                'subtotal' => $totalSubtotal,
                'pajak' => $pajak,
                'total_amount' => $totalAmount,
            ]);

            $po->details()->createMany($itemsToSave);

            DB::commit();
            return redirect()->route('admin.purchase-orders.index')->with('success', 'Purchase Order berhasil dibuat dengan harga terverifikasi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat membuat PO: ' . $e->getMessage())->withInput();
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        // ... (fungsi show, generatePoNumber, approve, reject, getPoDetailsApi tetap sama) ...
        $purchaseOrder->load(['supplier', 'gudang', 'details.part']);
        $creatorName = \App\Models\User::find($purchaseOrder->created_by)->name ?? 'User Tidak Dikenal';
        return view('admin.purchase_orders.show', compact('purchaseOrder', 'creatorName'));
    }

    public function print(PurchaseOrder $purchaseOrder)
    {
        // Pastikan semua relasi yang dibutuhkan sudah ter-load
        $purchaseOrder->load(['supplier', 'gudang', 'details.part', 'createdBy', 'approvedBy']);
        return view('admin.purchase_orders.print', compact('purchaseOrder'));
    }

    private function generatePoNumber()
    {
        $date = now()->format('Ymd');
        $latestPo = PurchaseOrder::whereDate('created_at', today())->count();
        $sequence = str_pad($latestPo + 1, 4, '0', STR_PAD_LEFT);
        return "PO/{$date}/{$sequence}";
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('approve-po', $purchaseOrder);
        if ($purchaseOrder->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Hanya PO yang berstatus PENDING APPROVAL yang bisa disetujui.');
        }
        $purchaseOrder->update([
            'status' => 'APPROVED',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)->with('success', 'Purchase Order berhasil disetujui.');
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('approve-po', $purchaseOrder);
        $request->validate(['rejection_reason' => 'required|string|min:10']);
        if ($purchaseOrder->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Hanya PO yang berstatus PENDING APPROVAL yang bisa ditolak.');
        }
        $purchaseOrder->update([
            'status' => 'REJECTED',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)->with('success', 'Purchase Order berhasil ditolak.');
    }

    // API lama untuk receiving, tetap dipertahankan
    public function getPoDetailsApi(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('details.part');
        $details = $purchaseOrder->details->map(function ($detail) {
            return [
                'po_detail_id' => $detail->id,
                'part_id' => $detail->part->id,
                'kode_part' => $detail->part->kode_part,
                'nama_part' => $detail->part->nama_part,
                'qty_pesan' => (int) $detail->qty_pesan,
                'qty_sudah_diterima' => (int) $detail->qty_diterima,
                'qty_sisa' => (int) ($detail->qty_pesan - $detail->qty_diterima),
            ];
        });
        return response()->json($details);
    }
}
