<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Rak;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QuarantineStockController extends Controller
{
public function __construct()
    {
        $this->middleware('can:can-process-quarantine');
    }

    /**
     * Menampilkan daftar stok di rak karantina.
     */
    public function index()
    {
        $quarantineItems = Inventory::whereHas('rak', function ($query) {
                // Logika disederhanakan untuk mencari semua tipe KARANTINA
                $query->where('tipe_rak', 'KARANTINA');
            })
            ->where('quantity', '>', 0)
            ->with(['part', 'gudang', 'rak'])
            ->latest()
            ->get();

        $storageRaks = Rak::where('tipe_rak', 'PENYIMPANAN')->where('is_active', true)->get()->groupBy('gudang_id');

        return view('admin.quarantine_stock.index', compact('quarantineItems', 'storageRaks'));
    }

    /**
     * Memproses stok dari rak karantina.
     */
    public function process(Request $request)
    {
        $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'action' => 'required|in:return_to_stock,write_off',
            'quantity' => 'required|integer|min:1',
            'destination_rak_id' => 'required_if:action,return_to_stock|exists:raks,id',
            'reason' => 'required_if:action,write_off|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $inventory = Inventory::with('part')->findOrFail($request->inventory_id);

            if ($request->quantity > $inventory->quantity) {
                throw new \Exception('Jumlah yang diproses melebihi stok karantina.');
            }

            if ($request->action === 'return_to_stock') {
                $destinationRak = Rak::findOrFail($request->destination_rak_id);

                if ($inventory->gudang_id != $destinationRak->gudang_id) {
                    throw new \Exception('Rak tujuan harus berada di gudang yang sama.');
                }

                // Stok sebelum & sesudah untuk logging
                $stokSebelumKarantina = $inventory->quantity;
                $inventory->decrement('quantity', $request->quantity);

                $destinationInventory = Inventory::firstOrCreate(
                    [
                        'part_id' => $inventory->part_id,
                        'rak_id' => $destinationRak->id,
                    ],
                    [
                        'gudang_id' => $inventory->gudang_id,
                        'quantity' => 0,
                    ]
                );

                $stokSebelumTujuan = $destinationInventory->quantity;
                $destinationInventory->increment('quantity', $request->quantity);

                // Catat pergerakan stok keluar dari karantina
                StockMovement::create([
                    'part_id' => $inventory->part_id,
                    'gudang_id' => $inventory->gudang_id,
                    'tipe_gerakan' => 'ADJUSTMENT',
                    'jumlah' => -$request->quantity,
                    'stok_sebelum' => $stokSebelumKarantina,
                    'stok_sesudah' => $inventory->quantity,
                    'referensi' => 'PROSES KARANTINA',
                    'user_id' => auth()->id(),
                ]);

                // Catat pergerakan stok masuk ke rak penyimpanan
                StockMovement::create([
                    'part_id' => $inventory->part_id,
                    'gudang_id' => $destinationInventory->gudang_id,
                    'tipe_gerakan' => 'ADJUSTMENT',
                    'jumlah' => $request->quantity,
                    'stok_sebelum' => $stokSebelumTujuan,
                    'stok_sesudah' => $destinationInventory->quantity,
                    'referensi' => 'PROSES KARANTINA',
                    'user_id' => auth()->id(),
                ]);

            } elseif ($request->action === 'write_off') {
                $stokSebelum = $inventory->quantity;
                $inventory->decrement('quantity', $request->quantity);

                StockAdjustment::create([
                    'part_id' => $inventory->part_id,
                    'gudang_id' => $inventory->gudang_id,
                    'rak_id' => $inventory->rak_id,
                    'tipe' => 'KURANG',
                    'jumlah' => $request->quantity,
                    'alasan' => $request->reason,
                    'status' => 'APPROVED', // Langsung disetujui
                    'created_by' => auth()->id(),
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                // Catat pergerakan stok untuk write-off
                StockMovement::create([
                    'part_id' => $inventory->part_id,
                    'gudang_id' => $inventory->gudang_id,
                    'tipe_gerakan' => 'ADJUSTMENT',
                    'jumlah' => -$request->quantity,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $inventory->quantity,
                    'referensi' => 'WRITE-OFF KARANTINA',
                    'user_id' => auth()->id(),
                ]);
            }

            DB::commit();
            return redirect()->route('admin.quarantine-stock.index')->with('success', 'Stok karantina berhasil diproses.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function processBulk(Request $request)
    {
        $request->validate([
            'inventory_ids'   => 'required|array|min:1',
            'inventory_ids.*' => 'exists:inventories,id',
            'action'          => 'required|in:return_to_stock,write_off',
            'destination_rak_id' => 'required_if:action,return_to_stock|exists:raks,id',
            'reason'          => 'required_if:action,write_off|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $inventories = Inventory::with(['part', 'rak'])->whereIn('id', $request->inventory_ids)->get();

            if ($request->action === 'return_to_stock') {
                $destinationRak = Rak::findOrFail($request->destination_rak_id);

                $firstGudangId = $inventories->first()->gudang_id;
                if ($destinationRak->gudang_id != $firstGudangId) {
                     throw new \Exception('Rak tujuan harus berada di gudang yang sama dengan item yang dipilih.');
                }
                foreach ($inventories as $inventory) {
                    if ($inventory->gudang_id != $firstGudangId) {
                        throw new \Exception('Semua item yang dipilih harus berasal dari gudang yang sama.');
                    }
                }
            }

            foreach ($inventories as $inventory) {
                $quantityToProcess = $inventory->quantity;
                if ($quantityToProcess <= 0) continue; // Lewati jika kuantitas 0

                if ($request->action === 'return_to_stock') {
                    // --- Logika Return to Stock dengan Logging ---
                    $stokSebelumKarantina = $inventory->quantity;

                    $destinationInventory = Inventory::firstOrCreate(
                        ['part_id' => $inventory->part_id, 'rak_id' => $request->destination_rak_id],
                        ['gudang_id' => $inventory->gudang_id, 'quantity' => 0]
                    );
                    $stokSebelumTujuan = $destinationInventory->quantity;

                    // Update stok
                    $destinationInventory->increment('quantity', $quantityToProcess);
                    $inventory->decrement('quantity', $quantityToProcess);

                    // Catat pergerakan stok KELUAR dari karantina
                    StockMovement::create([
                        'part_id'       => $inventory->part_id,
                        'gudang_id'     => $inventory->gudang_id,
                        'tipe_gerakan'  => 'ADJUSTMENT',
                        'jumlah'        => -$quantityToProcess,
                        'stok_sebelum'  => $stokSebelumKarantina,
                        'stok_sesudah'  => $inventory->quantity,
                        'referensi'     => 'PROSES KARANTINA MASSAL',
                        'user_id'       => auth()->id(),
                    ]);

                    // Catat pergerakan stok MASUK ke rak tujuan
                    StockMovement::create([
                        'part_id'       => $inventory->part_id,
                        'gudang_id'     => $destinationInventory->gudang_id,
                        'tipe_gerakan'  => 'ADJUSTMENT',
                        'jumlah'        => $quantityToProcess,
                        'stok_sebelum'  => $stokSebelumTujuan,
                        'stok_sesudah'  => $destinationInventory->quantity,
                        'referensi'     => 'PROSES KARANTINA MASSAL',
                        'user_id'       => auth()->id(),
                    ]);
                }
                elseif ($request->action === 'write_off') {
                    // --- Logika Write Off dengan Logging ---
                    $stokSebelum = $inventory->quantity;
                    $inventory->decrement('quantity', $quantityToProcess);

                    StockAdjustment::create([
                       'part_id' => $inventory->part_id,
                       'gudang_id' => $inventory->gudang_id,
                       'rak_id' => $inventory->rak_id,
                       'tipe' => 'KURANG',
                       'jumlah' => $quantityToProcess,
                       'alasan' => $request->reason,
                       'status' => 'APPROVED',
                       'created_by' => auth()->id(),
                       'approved_by' => auth()->id(),
                       'approved_at' => now(),
                   ]);

                   // Catat pergerakan stok untuk write-off
                   StockMovement::create([
                        'part_id'       => $inventory->part_id,
                        'gudang_id'     => $inventory->gudang_id,
                        'tipe_gerakan'  => 'ADJUSTMENT',
                        'jumlah'        => -$quantityToProcess,
                        'stok_sebelum'  => $stokSebelum,
                        'stok_sesudah'  => $inventory->quantity,
                        'referensi'     => 'WRITE-OFF KARANTINA MASSAL',
                        'user_id'       => auth()->id(),
                    ]);
                }
            }

            Inventory::whereIn('id', $request->inventory_ids)->where('quantity', '<=', 0)->delete();

            DB::commit();
            return redirect()->route('admin.quarantine-stock.index')->with('success', count($request->inventory_ids) . ' item karantina berhasil diproses dan dicatat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
