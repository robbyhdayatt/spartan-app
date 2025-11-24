<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\Gudang;
use App\Models\Part;
use App\Models\InventoryBatch;
use App\Services\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $user = Auth::user();
        
        // Hapus relasi 'konsumen' dari eager load
        $query = Penjualan::with(['sales']); 

        if (!in_array($user->jabatan->singkatan, ['SA', 'MA'])) {
            $query->where('gudang_id', $user->gudang_id);
        }

        $penjualans = $query->latest()->get();
        return view('admin.penjualans.index', compact('penjualans'));
    }

    public function create()
    {
        $this->authorize('manage-sales');
        $user = Auth::user();
        
        // Tidak perlu ambil data konsumen dari DB lagi
        
        if (in_array($user->jabatan->singkatan, ['SA', 'MA'])) {
            $gudangs = Gudang::where('is_active', true)->get();
        } else {
            $gudangs = Gudang::where('id', $user->gudang_id)->get();
        }

        return view('admin.penjualans.create', compact('gudangs'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage-sales');

        $validated = $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
            'nama_konsumen' => 'required|string|max:150', // Validasi String Manual
            'tanggal_jual' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.batch_id' => 'required|exists:inventory_batches,id',
            'items.*.qty_jual' => 'required|integer|min:1',
            'items.*.diskon' => 'nullable|numeric|min:0',
            'pajak' => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $totalSubtotalServer = 0;
            $totalDiskonServer = 0;

            $penjualan = Penjualan::create([
                'nomor_faktur' => Penjualan::generateNomorFaktur(),
                'tanggal_jual' => $validated['tanggal_jual'],
                'gudang_id' => $validated['gudang_id'],
                'nama_konsumen' => $validated['nama_konsumen'], // Simpan Nama Manual
                'sales_id' => auth()->id(),
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $part = Part::find($item['part_id']);
                $qty = (int)$item['qty_jual'];
                $manualDiscount = isset($item['diskon']) ? (float)$item['diskon'] : 0;
                $batch = InventoryBatch::findOrFail($item['batch_id']);

                if ($batch->quantity < $qty) {
                    throw new \Exception("Stok part '{$part->nama_part}' tidak cukup.");
                }

                // Kirim null sebagai konsumen
                $discountResult = $this->discountService->calculateSalesDiscount(
                    $part, 
                    null, 
                    $part->harga_jual_default, 
                    $manualDiscount
                );

                $finalPrice = $discountResult['final_price'];
                $appliedDiscount = $discountResult['discount_amount'];
                $itemSubtotal = $finalPrice * $qty;

                $totalSubtotalServer += $itemSubtotal;
                $totalDiskonServer += ($appliedDiscount * $qty);

                $stokSebelum = $batch->quantity;
                $batch->decrement('quantity', $qty);

                $penjualan->details()->create([
                    'part_id' => $part->id,
                    'rak_id' => $batch->rak_id,
                    'qty_jual' => $qty,
                    'harga_jual' => $part->harga_jual_default,
                    'diskon' => $appliedDiscount,
                    'subtotal' => $itemSubtotal,
                ]);

                $penjualan->stockMovements()->create([
                    'part_id' => $part->id,
                    'gudang_id' => $penjualan->gudang_id,
                    'rak_id' => $batch->rak_id,
                    'jumlah' => -$qty,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $batch->quantity,
                    'user_id' => auth()->id(),
                    'keterangan' => 'Penjualan #' . $penjualan->nomor_faktur,
                ]);
            }

            InventoryBatch::where('quantity', '<=', 0)->delete();

            $usePpn = $request->has('use_ppn') && $request->use_ppn == '1';
            $pajak = $usePpn ? ($totalSubtotalServer * 0.11) : 0;
            $totalHarga = $totalSubtotalServer + $pajak;

            $penjualan->update([
                'subtotal' => $totalSubtotalServer,
                'total_diskon' => $totalDiskonServer,
                'pajak' => $pajak,
                'total_harga' => $totalHarga,
            ]);

            DB::commit();
            return redirect()->route('admin.penjualans.show', $penjualan)->with('success', 'Penjualan berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Penjualan $penjualan)
    {
        $this->authorize('view-sales');
        $penjualan->load(['gudang', 'sales', 'details.part', 'details.rak']);
        return view('admin.penjualans.show', compact('penjualan'));
    }

    // --- API Methods ---
    public function getPartsByGudang(Gudang $gudang)
    {
        $parts = Part::whereHas('inventoryBatches', function ($query) use ($gudang) {
            $query->where('gudang_id', $gudang->id)->where('quantity', '>', 0);
        })
        ->withSum(['inventoryBatches' => function ($query) use ($gudang) {
            $query->where('gudang_id', $gudang->id);
        }], 'quantity')
        ->orderBy('nama_part')
        ->get()
        ->map(function($part) {
            return [
                'id' => $part->id,
                'kode_part' => $part->kode_part,
                'nama_part' => $part->nama_part,
                'harga_jual' => $part->harga_jual_default,
                'total_stock' => (int) $part->inventory_batches_sum_quantity,
            ];
        });
        return response()->json($parts);
    }

    public function getFifoBatches(Request $request)
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'gudang_id' => 'required|exists:gudangs,id',
        ]);

        $batches = InventoryBatch::where('part_id', $validated['part_id'])
            ->where('gudang_id', $validated['gudang_id'])
            ->where('quantity', '>', 0)
            ->with(['rak', 'receivingDetail.receiving'])
            ->get()
            ->sortBy(function($batch) {
                $date = $batch->receivingDetail && $batch->receivingDetail->receiving 
                        ? $batch->receivingDetail->receiving->tanggal_terima->format('Y-m-d') 
                        : '2000-01-01';
                return $date . '_' . str_pad($batch->id, 8, '0', STR_PAD_LEFT);
            });

        return response()->json($batches->values()->all());
    }

    public function calculateDiscount(Request $request)
    {
        return response()->json(['success' => true]);
    }
}