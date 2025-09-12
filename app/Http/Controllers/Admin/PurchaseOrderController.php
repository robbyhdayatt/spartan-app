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
        $purchaseOrders = PurchaseOrder::with(['supplier', 'gudang', 'createdBy'])->latest()->get();
        return view('admin.purchase_orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $this->authorize('create-po');
        $user = auth()->user();
        $suppliers = \App\Models\Supplier::where('is_active', true)->orderBy('nama_supplier')->get();

        if ($user->jabatan->nama_jabatan === 'PJ Gudang') {
            $gudangs = \App\Models\Gudang::where('id', $user->gudang_id)->get();
        } else {
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create-po');
        $request->validate([
            'tanggal_po' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'gudang_id' => 'required|exists:gudangs,id',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.qty' => 'required|integer|min:1',
            // Validasi harga tidak lagi diperlukan karena kita akan mengambilnya dari server
            // 'items.*.harga' => 'required|numeric|min:0',
            'use_ppn' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $today = now()->toDateString();
            $itemsToSave = [];

            // --- TAHAP 1: Validasi dan Kalkulasi Ulang Harga di Server ---
            foreach ($request->items as $itemData) {
                // Ambil part dari DB beserta harga efektifnya (harga campaign jika ada)
                $part = Part::where('parts.id', $itemData['part_id'])
                    ->leftJoin('campaigns', function ($join) use ($today) {
                        $join->on('parts.id', '=', 'campaigns.part_id')
                            ->where('campaigns.is_active', true)
                            ->where('campaigns.tipe', 'PEMBELIAN')
                            ->where('campaigns.tanggal_mulai', '<=', $today)
                            ->where('campaigns.tanggal_selesai', '>=', $today);
                    })
                    ->select('parts.*', DB::raw('COALESCE(campaigns.harga_promo, parts.harga_beli_default) as effective_price'))
                    ->firstOrFail();

                $hargaBeliAktual = $part->effective_price;
                $qty = $itemData['qty'];
                $itemSubtotal = $qty * $hargaBeliAktual;

                // Kumpulkan data yang sudah divalidasi
                $itemsToSave[] = [
                    'part_id' => $part->id,
                    'qty_pesan' => $qty,
                    'harga_beli' => $hargaBeliAktual,
                    'subtotal' => $itemSubtotal,
                ];

                // Akumulasi subtotal keseluruhan
                $subtotal += $itemSubtotal;
            }

            // --- TAHAP 2: Hitung Pajak dan Total ---
            $pajak = 0;
            if ($request->has('use_ppn') && $request->use_ppn) {
                $pajak = $subtotal * 0.11;
            }
            $totalAmount = $subtotal + $pajak;

            // --- TAHAP 3: Simpan Purchase Order dan Detailnya ---
            $po = PurchaseOrder::create([
                'nomor_po' => $this->generatePoNumber(),
                'tanggal_po' => $request->tanggal_po,
                'supplier_id' => $request->supplier_id,
                'gudang_id' => $request->gudang_id,
                'catatan' => $request->catatan,
                'status' => 'PENDING_APPROVAL',
                'created_by' => Auth::id(),
                'subtotal' => $subtotal,
                'pajak' => $pajak,
                'total_amount' => $totalAmount,
            ]);

            // Simpan semua item detail yang sudah divalidasi harganya
            $po->details()->createMany($itemsToSave);

            DB::commit();
            return redirect()->route('admin.purchase-orders.index')->with('success', 'Purchase Order berhasil dibuat dengan harga yang tervalidasi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat membuat PO: ' . $e->getMessage())->withInput();
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'gudang', 'details.part']);
        $creatorName = \App\Models\User::find($purchaseOrder->created_by)->name ?? 'User Tidak Dikenal';
        return view('admin.purchase_orders.show', compact('purchaseOrder', 'creatorName'));
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
        // Cukup gunakan satu otorisasi yang sudah jelas
        $this->authorize('approve-po', $purchaseOrder);

        if ($purchaseOrder->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Hanya PO yang berstatus PENDING APPROVAL yang bisa disetujui.');
        }

        $purchaseOrder->status = 'APPROVED';
        $purchaseOrder->approved_by = Auth::id();
        $purchaseOrder->approved_at = now();
        $purchaseOrder->save();

        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)->with('success', 'Purchase Order berhasil disetujui.');
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder) // Tambahkan Request $request
    {
        $this->authorize('approve-po', $purchaseOrder);

        // Validasi bahwa alasan penolakan wajib diisi
        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        if ($purchaseOrder->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Hanya PO yang berstatus PENDING APPROVAL yang bisa ditolak.');
        }

        $purchaseOrder->status = 'REJECTED';
        $purchaseOrder->rejection_reason = $request->rejection_reason; // Simpan alasan penolakan
        $purchaseOrder->approved_by = Auth::id(); // Catat siapa yang menolak
        $purchaseOrder->approved_at = now(); // Catat kapan ditolak
        $purchaseOrder->save();

        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)->with('success', 'Purchase Order berhasil ditolak.');
    }

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
