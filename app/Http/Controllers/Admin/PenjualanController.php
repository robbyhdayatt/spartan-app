<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\Konsumen;
use App\Models\Gudang;
use App\Models\User;
use App\Models\Part;
use App\Models\Inventory;
use App\Models\Jabatan;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\DiscountService;

class PenjualanController extends Controller
{
    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    public function index()
    {
        $this->authorize('view-sales');
        $penjualans = Penjualan::with(['konsumen', 'sales'])->latest()->get();
        return view('admin.penjualans.index', compact('penjualans'));
    }

    public function create()
    {
        $this->authorize('manage-sales');
        $user = Auth::user();
        $konsumens = Konsumen::where('is_active', true)->orderBy('nama_konsumen')->get();

        if ($user->jabatan->nama_jabatan === 'Sales') {
            $gudangs = Gudang::where('id', $user->gudang_id)->get();
        } else {
            $gudangs = Gudang::where('is_active', true)->get();
        }

        return view('admin.penjualans.create', compact('konsumens', 'gudangs'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage-sales');

        $validated = $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
            'konsumen_id' => 'required|exists:konsumens,id',
            'tanggal_jual' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.rak_id' => 'required|exists:raks,id',
            'items.*.qty_jual' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $konsumen = Konsumen::find($validated['konsumen_id']);
            $totalSubtotalServer = 0;
            $totalDiskonServer = 0;

            // --- PERBAIKAN DI SINI ---
            $penjualan = Penjualan::create([
                'nomor_faktur' => Penjualan::generateNomorFaktur(), // Diubah dari generateInvoiceNumber()
                'tanggal_jual' => $validated['tanggal_jual'],
                'gudang_id' => $validated['gudang_id'],
                'konsumen_id' => $validated['konsumen_id'],
                'sales_id' => auth()->id(),
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $part = Part::find($item['part_id']);
                $qty = (int)$item['qty_jual'];

                $discountResult = $this->discountService->calculateSalesDiscount($part, $konsumen, $part->harga_jual_default);
                $finalPrice = $discountResult['final_price'];
                $itemSubtotal = $finalPrice * $qty;

                $totalSubtotalServer += $itemSubtotal;
                $totalDiskonServer += ($part->harga_jual_default - $finalPrice) * $qty;

                $inventory = Inventory::where('rak_id', $item['rak_id'])->where('part_id', $part->id)->first();
                if (!$inventory || $inventory->quantity < $qty) {
                    throw new \Exception("Stok tidak mencukupi untuk part '{$part->nama_part}' di rak '{$inventory->rak->kode_rak}'. Sisa stok: {$inventory->quantity}.");
                }
                $stokSebelum = $inventory->quantity;
                $inventory->decrement('quantity', $qty);

                $penjualan->details()->create([
                    'part_id' => $part->id,
                    'rak_id' => $item['rak_id'],
                    'qty_jual' => $qty,
                    'harga_jual' => $finalPrice,
                    'subtotal' => $itemSubtotal,
                ]);

                $penjualan->stockMovements()->create([
                    'part_id' => $part->id,
                    'gudang_id' => $penjualan->gudang_id,
                    'rak_id' => $item['rak_id'],
                    'jumlah' => -$qty,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $inventory->quantity,
                    'user_id' => auth()->id(),
                    'keterangan' => 'Penjualan via Faktur #' . $penjualan->nomor_faktur,
                ]);
            }

            $pajak = 0;
            if ($request->pajak > 0) {
                 $pajak = $totalSubtotalServer * 0.11;
            }
            $totalHarga = $totalSubtotalServer + $pajak;

            $penjualan->update([
                'subtotal' => $totalSubtotalServer,
                'total_diskon' => $totalDiskonServer,
                'pajak' => $pajak,
                'total_harga' => $totalHarga,
            ]);

            DB::commit();
            return redirect()->route('admin.penjualans.show', $penjualan)->with('success', 'Transaksi penjualan berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Penjualan $penjualan)
    {
        $this->authorize('view-sales');
        $penjualan->load(['konsumen', 'gudang', 'sales', 'details.part', 'details.rak']);
        return view('admin.penjualans.show', compact('penjualan'));
    }

    public function print(Penjualan $penjualan)
    {
        $this->authorize('view-sales');
        $penjualan->load(['konsumen', 'gudang', 'sales', 'details.part']);
        return view('admin.penjualans.print', compact('penjualan'));
    }

    // --- API Methods ---

    public function getPartsByGudang(Gudang $gudang)
    {
        $partIdsInStock = Inventory::where('gudang_id', $gudang->id)
            ->where('quantity', '>', 0)
            ->pluck('part_id')
            ->unique();

        $parts = Part::whereIn('id', $partIdsInStock)
            ->select('id', 'nama_part', 'kode_part')
            ->orderBy('nama_part')
            ->get();

        return response()->json($parts);
    }

    public function getPartStockDetails(Request $request, Part $part)
    {
        $validated = $request->validate(['gudang_id' => 'required|exists:gudangs,id']);
        $stockDetails = Inventory::join('raks', 'inventories.rak_id', '=', 'raks.id')
            ->where('inventories.part_id', $part->id)
            ->where('inventories.gudang_id', $validated['gudang_id'])
            ->where('inventories.quantity', '>', 0)
            ->where('raks.tipe_rak', 'PENYIMPANAN')
            ->select('raks.id as rak_id', 'raks.kode_rak', 'inventories.quantity')
            ->get();
        return response()->json($stockDetails);
    }

    public function getDetails($id)
    {
        $penjualan = \App\Models\Penjualan::with('details.part')->find($id);
        if (!$penjualan) { return response()->json(['error' => 'Faktur tidak ditemukan'], 404); }
        return response()->json($penjualan->details);
    }

    public function calculateDiscount(Request $request)
    {
        $request->validate(['part_id' => 'required|exists:parts,id', 'konsumen_id' => 'required|exists:konsumens,id']);
        try {
            $part = Part::findOrFail($request->part_id);
            $konsumen = Konsumen::findOrFail($request->konsumen_id);
            $basePrice = $part->harga_jual_default;
            $discountResult = $this->discountService->calculateSalesDiscount($part, $konsumen, $basePrice);
            return response()->json(['success' => true, 'data' => $discountResult]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghitung diskon: ' . $e->getMessage()], 500);
        }
    }
}
