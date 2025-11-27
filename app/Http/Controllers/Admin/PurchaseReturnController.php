<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\Receiving;
use App\Models\ReceivingDetail;
use App\Models\InventoryBatch; // Pastikan Import Ini Ada
use App\Models\StockMovement; // Pastikan Import Ini Ada
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
        
        // PERBAIKAN 1: Hanya ambil Receiving yang MASIH punya stok fisik di Rak Karantina
        // Kita cek tabel inventory_batches yang terhubung ke receiving_detail_id
        $receivings = Receiving::whereHas('details.inventoryBatches', function ($query) {
            $query->where('quantity', '>', 0)
                  ->whereHas('rak', function($r) {
                      $r->where('tipe_rak', 'KARANTINA'); // Asumsi tipe rak karantina
                  });
        })->with('supplier')->get();

        return view('admin.purchase_returns.create', compact('receivings'));
    }

    // API Endpoint
    public function getFailedItems(Receiving $receiving)
    {
        // PERBAIKAN 2: Hitung Available Qty berdasarkan stok fisik batch
        $items = $receiving->details()
            ->with(['part', 'inventoryBatches' => function($q) {
                $q->where('quantity', '>', 0)
                  ->whereHas('rak', function($r) {
                      $r->where('tipe_rak', 'KARANTINA');
                  });
            }])
            ->get()
            // Filter di level PHP: Hanya ambil yang stok batch-nya > 0
            ->filter(function($detail) {
                return $detail->inventoryBatches->sum('quantity') > 0;
            })
            ->map(function($detail) {
                 // Override qty gagal agar frontend menampilkan sisa stok aktual
                 $stokAktual = $detail->inventoryBatches->sum('quantity');
                 
                 // Kita tambahkan atribut virtual untuk frontend
                 $detail->qty_available_for_return = $stokAktual;
                 
                 // Opsional: Timpa qty_gagal_qc hanya untuk tampilan (hati-hati jika field ini dipakai logic lain)
                 // $detail->qty_gagal_qc = $stokAktual; 
                 
                 return $detail;
            })
            ->values(); // Reset array keys agar JSON rapi

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
                $qtyToReturn = (int) $data['qty_retur'];
                
                // Ambil batch stok yang tersedia untuk detail ini (FIFO)
                $batches = InventoryBatch::where('receiving_detail_id', $detail->id)
                            ->where('quantity', '>', 0)
                            ->whereHas('rak', function($r) {
                                $r->where('tipe_rak', 'KARANTINA');
                            })
                            ->orderBy('created_at', 'asc') // FIFO: Ambil yang paling lama dulu
                            ->get();

                $totalAvailable = $batches->sum('quantity');

                if ($qtyToReturn > $totalAvailable) {
                    throw new \Exception("Jumlah retur untuk part {$detail->part->nama_part} melebihi stok fisik yang tersedia di karantina ($totalAvailable).");
                }

                // PERBAIKAN 3: Potong Stok Fisik (Looping Batch)
                $sisaYangHarusDipotong = $qtyToReturn;
                
                foreach ($batches as $batch) {
                    if ($sisaYangHarusDipotong <= 0) break;

                    $potong = min($batch->quantity, $sisaYangHarusDipotong);
                    
                    // Catat Movement (PENTING)
                    StockMovement::create([
                        'part_id'       => $detail->part_id,
                        'gudang_id'     => $batch->gudang_id,
                        'rak_id'        => $batch->rak_id,
                        'tipe_gerakan'  => 'OUTBOUND', // Atau RETUR_PEMBELIAN
                        'jumlah'        => -$potong, // Negatif karena keluar
                        'stok_sebelum'  => $batch->quantity,
                        'stok_sesudah'  => $batch->quantity - $potong,
                        'referensi'     => $return->nomor_retur,
                        'user_id'       => Auth::id(),
                        'keterangan'    => 'Retur Pembelian ke Supplier',
                    ]);

                    $batch->decrement('quantity', $potong);
                    
                    // Hapus batch jika habis agar bersih
                    if ($batch->quantity <= 0) {
                        $batch->delete();
                    }

                    $sisaYangHarusDipotong -= $potong;
                }

                // Simpan detail retur
                $return->details()->create([
                    'part_id' => $detail->part_id,
                    'qty_retur' => $qtyToReturn,
                    'alasan' => $data['alasan'],
                ]);

                // Update counter di receiving detail (untuk history)
                $detail->qty_diretur += $qtyToReturn;
                $detail->save();
            }

            DB::commit();
            return redirect()->route('admin.purchase-returns.index')->with('success', 'Dokumen retur pembelian berhasil dibuat dan stok karantina telah dipotong.');

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