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
use App\Models\Campaign;

class PenjualanController extends Controller
{
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

        // Aturan baru berdasarkan peran
        // Jika login sebagai Sales, gudang sudah ditentukan
        if ($user->jabatan->nama_jabatan === 'Sales') {
            $gudangs = Gudang::where('id', $user->gudang_id)->get();
        } else {
            // Jika Manajer Area atau Super Admin, bisa pilih semua gudang
            $gudangs = Gudang::where('is_active', true)->get();
        }

        $jabatanSalesId = Jabatan::where('nama_jabatan', 'Sales')->first()->id;
        $salesUsers = User::where('jabatan_id', $jabatanSalesId)->where('is_active', true)->get();

        return view('admin.penjualans.create', compact('konsumens', 'gudangs', 'salesUsers'));
    }

    // API: Get parts that have stock in a specific warehouse
    public function getPartsByGudang(Gudang $gudang)
    {
        $partIdsInStock = Inventory::where('gudang_id', $gudang->id)
            ->where('quantity', '>', 0)
            ->pluck('part_id')
            ->unique();

        if ($partIdsInStock->isEmpty()) {
            return response()->json([]);
        }

        $today = now()->toDateString();

        // Menggunakan satu query efisien dengan LEFT JOIN
        $parts = Part::whereIn('parts.id', $partIdsInStock) // <--- PERBAIKAN FINAL ADA DI SINI
            ->leftJoin('campaigns', function ($join) use ($today) {
                $join->on('parts.id', '=', 'campaigns.part_id')
                    ->where('campaigns.is_active', true)
                    ->where('campaigns.tipe', 'PENJUALAN')
                    ->where('campaigns.tanggal_mulai', '<=', $today)
                    ->where('campaigns.tanggal_selesai', '>=', $today);
            })
            ->select(
                'parts.id',
                'parts.nama_part',
                'parts.kode_part',
                DB::raw('COALESCE(campaigns.harga_promo, parts.harga_jual_default) as effective_price')
            )
            ->orderBy('parts.nama_part')
            ->get();

        return response()->json($parts);
    }

    // API: Get stock details (which shelves and how many) for a part
    public function getPartStockDetails(Part $part)
    {
        $gudangId = request()->query('gudang_id');
        if (!$gudangId) {
            return response()->json(['error' => 'Gudang ID is required.'], 400);
        }

        // Menggunakan join untuk query yang efisien
        $stockDetails = Inventory::join('raks', 'inventories.rak_id', '=', 'raks.id')
            ->where('inventories.part_id', $part->id)
            ->where('inventories.gudang_id', $gudangId)
            ->where('inventories.quantity', '>', 0)
            ->where('raks.tipe_rak', 'PENYIMPANAN')
            ->select('inventories.*', 'raks.nama_rak', 'raks.kode_rak')
            ->get();

        // Transformasi data agar cocok dengan format JavaScript di view
        $stockDetails->transform(function ($item) {
            $item->rak = [
                'id' => $item->rak_id,
                'nama_rak' => $item->nama_rak,
                'kode_rak' => $item->kode_rak,
            ];
            return $item;
        });

        if ($stockDetails->isEmpty()) {
            return response()->json(['error' => 'Stok siap jual tidak ditemukan di gudang yang dipilih.'], 404);
        }

        // Menyiapkan data campaign jika ada
        $activeCampaign = Campaign::where('part_id', $part->id)
            ->where('tipe', 'PENJUALAN')
            ->where('is_active', 1)
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->first();

        $hargaJual = $activeCampaign ? $activeCampaign->harga_promo : $part->harga_jual_default;

        return response()->json([
            'stock_details' => $stockDetails,
            'harga_jual' => $hargaJual,
            'is_promo' => !is_null($activeCampaign)
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage-sales');

        $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
            'konsumen_id' => 'required|exists:konsumens,id',
            'tanggal_jual' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.rak_id' => 'required|exists:raks,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $penjualan = Penjualan::create([
                'nomor_faktur' => Penjualan::generateNomorFaktur(),
                'tanggal_jual' => $request->tanggal_jual,
                'gudang_id' => $request->gudang_id,
                'konsumen_id' => $request->konsumen_id,
                'sales_id' => $request->sales_id ?? auth()->id(),
                'status' => 'COMPLETED',
                'created_by' => auth()->id(),
                'subtotal' => $request->subtotal,
                'kena_ppn' => $request->has('kena_ppn'),
                'pajak' => $request->ppn_jumlah, // PERBAIKAN 1: Menggunakan kolom 'pajak'
                'total_harga' => $request->total_harga,
            ]);

            foreach ($request->items as $item) {
                $rakId = $item['rak_id'];
                $partId = $item['part_id'];
                $qty = (int)$item['qty'];
                $harga = $item['harga'];

                // Kurangi stok dari inventory
                $inventory = Inventory::where('rak_id', $rakId)
                    ->where('part_id', $partId)
                    ->first();

                if (!$inventory || $inventory->quantity < $qty) {
                    $part = Part::find($partId);
                    throw new \Exception("Stok tidak mencukupi untuk part '{$part->nama_part}' di rak yang dipilih.");
                }

                $stokSebelum = $inventory->quantity;
                $inventory->decrement('quantity', $qty);

                // Buat detail penjualan
                $penjualan->details()->create([
                    'part_id' => $partId,
                    'rak_id' => $rakId,
                    'qty_jual' => $qty,         // PERBAIKAN 2: Menggunakan kolom 'qty_jual'
                    'harga_jual' => $harga,   // PERBAIKAN 2: Menggunakan kolom 'harga_jual'
                    'subtotal' => $qty * $harga,
                ]);

                // Catat pergerakan stok
                $penjualan->stockMovements()->create([
                    'part_id' => $partId,
                    'gudang_id' => $penjualan->gudang_id,
                    'rak_id' => $rakId,
                    'jumlah' => -$qty, // PERBAIKAN 3: Menggunakan kolom 'jumlah'
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $inventory->quantity,
                    'user_id' => auth()->id(),
                    'keterangan' => 'Penjualan via Faktur #' . $penjualan->nomor_faktur,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.penjualans.show', $penjualan)->with('success', 'Transaksi penjualan berhasil disimpan.');

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

    private function generateInvoiceNumber()
    {
        $date = now()->format('Ymd');
        $latest = Penjualan::whereDate('created_at', today())->count();
        $sequence = str_pad($latest + 1, 4, '0', STR_PAD_LEFT);
        return "INV/{$date}/{$sequence}";
    }

    /**
     * Mengambil detail item dari sebuah penjualan untuk keperluan AJAX.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetails($id)
    {
        // Cari data penjualan beserta relasi ke detail dan part-nya
        $penjualan = \App\Models\Penjualan::with('details.part')->find($id);

        // Jika tidak ditemukan, kembalikan error 404
        if (!$penjualan) {
            return response()->json(['error' => 'Faktur tidak ditemukan'], 404);
        }

        // Jika berhasil, kembalikan detailnya sebagai JSON
        return response()->json($penjualan->details);
    }
}
