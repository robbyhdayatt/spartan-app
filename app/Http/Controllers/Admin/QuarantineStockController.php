<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryBatch;
use App\Models\Rak;
use App\Models\Part;      // Tambahkan ini
use App\Models\Gudang;   // Tambahkan ini
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QuarantineStockController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:can-manage-stock');
    }

    public function index()
    {
        $user = Auth::user();
        $gudangFilter = null;

        if (!in_array($user->jabatan->singkatan, ['SA', 'MA'])) {
            $gudangFilter = $user->gudang_id;
        }

        $quarantineQuery = InventoryBatch::whereHas('rak', function ($query) {
            $query->where('tipe_rak', 'KARANTINA');
        })->where('quantity', '>', 0);

        if ($gudangFilter) {
            $quarantineQuery->where('gudang_id', $gudangFilter);
        }

        // --- LOGIKA QUERY YANG DIPERBAIKI ---
        $quarantineItems = $quarantineQuery
            ->select('part_id', 'rak_id', 'gudang_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('part_id', 'rak_id', 'gudang_id')
            ->get();

        // Eager load relasi secara manual setelah query agregasi
        if ($quarantineItems->isNotEmpty()) {
            $partIds = $quarantineItems->pluck('part_id')->unique();
            $rakIds = $quarantineItems->pluck('rak_id')->unique();
            $gudangIds = $quarantineItems->pluck('gudang_id')->unique();

            $parts = Part::whereIn('id', $partIds)->get()->keyBy('id');
            $raks = Rak::whereIn('id', $rakIds)->get()->keyBy('id');
            $gudangs = Gudang::whereIn('id', $gudangIds)->get()->keyBy('id');

            // Lampirkan data relasi ke setiap item
            $quarantineItems->each(function ($item) use ($parts, $raks, $gudangs) {
                $item->part = $parts->get($item->part_id);
                $item->rak = $raks->get($item->rak_id);
                $item->gudang = $gudangs->get($item->gudang_id);
            });
        }
        // --- AKHIR PERBAIKAN ---

        $storageRaks = Rak::where('tipe_rak', 'PENYIMPANAN')
            ->where('is_active', true)
            ->get()
            ->groupBy('gudang_id');

        return view('admin.quarantine_stock.index', compact('quarantineItems', 'storageRaks'));
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'rak_id' => 'required|exists:raks,id',
            'gudang_id' => 'required|exists:gudangs,id',
            'action' => 'required|in:return_to_stock,write_off',
            'quantity' => 'required|integer|min:1',
            'destination_rak_id' => 'nullable|required_if:action,return_to_stock|exists:raks,id',
            'reason' => 'nullable|required_if:action,write_off|string|max:255',
        ]);

        $partId = $validated['part_id'];
        $rakId = $validated['rak_id'];
        $gudangId = $validated['gudang_id'];
        $quantityToProcess = $validated['quantity'];
        $message = '';

        try {
            DB::transaction(function () use ($validated, $partId, $rakId, $gudangId, $quantityToProcess, &$message) {

                $currentStock = InventoryBatch::where('part_id', $partId)->where('rak_id', $rakId)->sum('quantity');
                if ($quantityToProcess > $currentStock) {
                    throw new \Exception('Jumlah melebihi stok karantina. Stok tersedia: ' . $currentStock);
                }

                if ($validated['action'] === 'return_to_stock') {
                    $destinationRak = Rak::findOrFail($validated['destination_rak_id']);
                    if ($gudangId != $destinationRak->gudang_id) {
                        throw new \Exception('Rak tujuan harus berada di gudang yang sama.');
                    }

                    $this->reduceStockFromBatches($partId, $rakId, $quantityToProcess, 'Keluar dari karantina ke rak ' . $destinationRak->kode_rak);

                    InventoryBatch::create([
                        'part_id' => $partId, 'rak_id' => $destinationRak->id, 'gudang_id' => $gudangId,
                        'quantity' => $quantityToProcess, 'receiving_detail_id' => null,
                    ]);

                    $message = 'Barang berhasil dikembalikan ke stok penjualan.';

                } elseif ($validated['action'] === 'write_off') {
                    StockAdjustment::create([
                        'part_id' => $partId,
                        'gudang_id' => $gudangId,
                        'rak_id' => $rakId,
                        'tipe' => 'KURANG',
                        'jumlah' => $quantityToProcess,
                        'alasan' => $validated['reason'],
                        'status' => 'PENDING_APPROVAL',
                        'created_by' => auth()->id(),
                    ]);
                    $message = 'Permintaan write-off berhasil diajukan dan menunggu persetujuan.';
                }
            });

            // Kode BARU
            return redirect()->route('admin.quarantine-stock.index')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    private function reduceStockFromBatches($partId, $rakId, $quantityToReduce, $keterangan)
    {
        $batches = InventoryBatch::where('part_id', $partId)->where('rak_id', $rakId)
            ->where('quantity', '>', 0)->orderBy('created_at', 'asc')->get();

        $remainingQtyToReduce = $quantityToReduce;
        foreach ($batches as $batch) {
            if ($remainingQtyToReduce <= 0) break;

            $stokSebelum = $batch->quantity;
            $qtyToTake = min($batch->quantity, $remainingQtyToReduce);
            $batch->decrement('quantity', $qtyToTake);
            $remainingQtyToReduce -= $qtyToTake;

            // Perbaiki pencatatan Stock Movement
            StockMovement::create([
                'part_id' => $partId, 'gudang_id' => $batch->gudang_id, 'rak_id' => $rakId,
                'jumlah' => -$qtyToTake, 'stok_sebelum' => $stokSebelum, 'stok_sesudah' => $batch->quantity,
                'referensi_type' => 'App\Models\User', 'referensi_id' => auth()->id(), 'user_id' => auth()->id(),
                'keterangan' => $keterangan,
            ]);

            if ($batch->quantity <= 0) {
                $batch->delete();
            }
        }
    }
}
