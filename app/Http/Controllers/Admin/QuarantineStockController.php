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
                $query->whereIn('tipe_rak', ['KARANTINA_RETUR', 'KARANTINA_QC']);
            })
            ->where('quantity', '>', 0)
            ->with(['part', 'gudang', 'rak'])
            ->latest()
            ->get();

        // Ambil daftar rak penyimpanan untuk modal
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
                // Proses kembalikan ke stok jual (Mutasi Internal)
                $destinationRak = Rak::findOrFail($request->destination_rak_id);

                // Pastikan rak tujuan berada di gudang yang sama
                if ($inventory->gudang_id != $destinationRak->gudang_id) {
                    throw new \Exception('Rak tujuan harus berada di gudang yang sama.');
                }

                // Kurangi stok dari rak karantina
                $inventory->decrement('quantity', $request->quantity);

                // Tambah stok ke rak tujuan
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
                $destinationInventory->increment('quantity', $request->quantity);

                // Catat pergerakan stok
                StockMovement::create([
                    'part_id' => $inventory->part_id,
                    'gudang_id' => $inventory->gudang_id,
                    'jumlah' => -$request->quantity,
                    'stok_sebelum' => $inventory->quantity + $request->quantity,
                    'stok_sesudah' => $inventory->quantity,
                    'keterangan' => 'Proses karantina: Pindah dari rak ' . $inventory->rak->kode_rak,
                    'user_id' => auth()->id(),
                    'referensi_type' => Inventory::class,
                    'referensi_id' => $inventory->id
                ]);
                 StockMovement::create([
                    'part_id' => $inventory->part_id,
                    'gudang_id' => $inventory->gudang_id,
                    'jumlah' => $request->quantity,
                    'stok_sebelum' => $destinationInventory->quantity - $request->quantity,
                    'stok_sesudah' => $destinationInventory->quantity,
                    'keterangan' => 'Proses karantina: Pindah ke rak ' . $destinationRak->kode_rak,
                    'user_id' => auth()->id(),
                    'referensi_type' => Inventory::class,
                    'referensi_id' => $destinationInventory->id
                ]);


            } elseif ($request->action === 'write_off') {
                // Proses hapus barang (Adjusment Stok)
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
            }

            DB::commit();
            return redirect()->route('admin.quarantine-stock.index')->with('success', 'Stok karantina berhasil diproses.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
