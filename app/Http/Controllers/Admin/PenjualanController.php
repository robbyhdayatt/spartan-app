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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\DiscountService; // <-- IMPORT SERVICE KITA

class PenjualanController extends Controller
{
    protected $discountService;

    // 1. Inject DiscountService melalui Constructor
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

        $jabatanSalesId = Jabatan::where('nama_jabatan', 'Sales')->first()->id;
        $salesUsers = User::where('jabatan_id', $jabatanSalesId)->where('is_active', true)->get();

        return view('admin.penjualans.create', compact('konsumens', 'gudangs', 'salesUsers'));
    }

    // API: (TIDAK BERUBAH) - Hanya mengambil daftar part yang ada stok
    public function getPartsByGudang(Gudang $gudang)
    {
        $partIdsInStock = Inventory::where('gudang_id', $gudang->id)
            ->where('quantity', '>', 0)
            ->pluck('part_id')
            ->unique();

        if ($partIdsInStock->isEmpty()) {
            return response()->json([]);
        }

        $parts = Part::whereIn('id', $partIdsInStock)
            ->select('id', 'nama_part', 'kode_part', 'harga_jual_default') // Ambil harga default
            ->orderBy('nama_part')
            ->get();

        return response()->json($parts);
    }

    // 2. MODIFIKASI API: getPartStockDetails sekarang memanggil DiscountService
    public function getPartStockDetails(Part $part, Request $request)
    {
        $validated = $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
            'konsumen_id' => 'required|exists:konsumens,id', // Konsumen sekarang wajib ada
        ]);

        $konsumen = Konsumen::find($validated['konsumen_id']);

        $stockDetails = Inventory::join('raks', 'inventories.rak_id', '=', 'raks.id')
            ->where('inventories.part_id', $part->id)
            ->where('inventories.gudang_id', $validated['gudang_id'])
            ->where('inventories.quantity', '>', 0)
            ->where('raks.tipe_rak', 'PENYIMPANAN')
            ->select('inventories.id as inventory_id', 'inventories.quantity', 'raks.id as rak_id', 'raks.nama_rak')
            ->get();

        if ($stockDetails->isEmpty()) {
            return response()->json(['error' => 'Stok siap jual tidak ditemukan di gudang yang dipilih.'], 404);
        }

        // Panggil DiscountService untuk kalkulasi harga
        $discountResult = $this->discountService->calculateSalesDiscount($part, $konsumen, $part->harga_jual_default);

        return response()->json([
            'stock_details' => $stockDetails,
            'discount_result' => $discountResult, // Kirim semua hasil kalkulasi ke frontend
        ]);
    }

    // 3. MODIFIKASI BESAR: store() sekarang menghitung ulang semua harga di server
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
            'items.*.qty' => 'required|integer|min:1',
            // Harga tidak lagi divalidasi, karena akan dihitung ulang
        ]);

        DB::beginTransaction();
        try {
            $konsumen = Konsumen::find($validated['konsumen_id']);
            $totalSubtotal = 0;

            // Buat header penjualan terlebih dahulu, total akan diupdate nanti
            $penjualan = Penjualan::create([
                'nomor_faktur' => Penjualan::generateNomorFaktur(),
                'tanggal_jual' => $validated['tanggal_jual'],
                'gudang_id' => $validated['gudang_id'],
                'konsumen_id' => $validated['konsumen_id'],
                'sales_id' => auth()->id(), // Asumsi sales adalah user yang login
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $part = Part::find($item['part_id']);
                $qty = (int)$item['qty'];

                // HITUNG ULANG HARGA DI SERVER MENGGUNAKAN SERVICE
                $discountResult = $this->discountService->calculateSalesDiscount($part, $konsumen, $part->harga_jual_default);
                $finalPrice = $discountResult['final_price'];
                $itemSubtotal = $finalPrice * $qty;
                $totalSubtotal += $itemSubtotal;

                // Cek dan kurangi stok dari inventory
                $inventory = Inventory::where('rak_id', $item['rak_id'])->where('part_id', $part->id)->first();
                if (!$inventory || $inventory->quantity < $qty) {
                    throw new \Exception("Stok tidak mencukupi untuk part '{$part->nama_part}' di rak yang dipilih.");
                }
                $stokSebelum = $inventory->quantity;
                $inventory->decrement('quantity', $qty);

                // Buat detail penjualan dengan harga yang sudah divalidasi server
                $penjualan->details()->create([
                    'part_id' => $part->id,
                    'rak_id' => $item['rak_id'],
                    'qty_jual' => $qty,
                    'harga_jual' => $finalPrice, // Gunakan harga final dari service
                    'harga_awal' => $part->harga_jual_default, // Simpan harga asli untuk referensi
                    'subtotal' => $itemSubtotal,
                ]);

                // Catat pergerakan stok
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

            // Hitung pajak dan total akhir berdasarkan subtotal yang sudah dihitung ulang
            $pajak = 0; // Logika PPN bisa ditambahkan di sini jika perlu
            $totalHarga = $totalSubtotal + $pajak;

            // Update header penjualan dengan total yang benar
            $penjualan->update([
                'subtotal' => $totalSubtotal,
                'pajak' => $pajak,
                'total_harga' => $totalHarga,
            ]);

            DB::commit();
            return redirect()->route('admin.penjualans.show', $penjualan)->with('success', 'Transaksi penjualan berhasil disimpan dengan harga terverifikasi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage())->withInput();
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

    public function getDetails($id)
    {
        $penjualan = \App\Models\Penjualan::with('details.part')->find($id);
        if (!$penjualan) {
            return response()->json(['error' => 'Faktur tidak ditemukan'], 404);
        }
        return response()->json($penjualan->details);
    }
}
