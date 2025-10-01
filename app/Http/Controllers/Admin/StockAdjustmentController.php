<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\Inventory;
use App\Models\Part;
use App\Models\Gudang;
use App\Models\Rak; // <-- TAMBAHKAN ATAU PASTIKAN BARIS INI ADA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockAdjustment::with(['part', 'gudang', 'rak', 'createdBy', 'approvedBy'])->latest()->get();
        return view('admin.stock_adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $user = auth()->user();
        $parts = \App\Models\Part::where('is_active', true)->get();

        if ($user->gudang_id) {
            $gudangs = \App\Models\Gudang::where('id', $user->gudang_id)->get();
        } else {
            $gudangs = \App\Models\Gudang::where('is_active', true)->get();
        }

        return view('admin.stock_adjustments.create', compact('gudangs', 'parts'));
    }

    // Fungsi API untuk mengambil data rak
    public function getRaksByGudang(Gudang $gudang)
    {
        $raks = Rak::where('gudang_id', $gudang->id)
                   ->whereIn('tipe_rak', ['PENYIMPANAN', 'KARANTINA'])
                   ->where('is_active', true)
                   ->get();
        return response()->json($raks);
    }

    public function store(Request $request)
    {
        $this->authorize('can-manage-stock');
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'gudang_id' => 'required|exists:gudangs,id',
            'rak_id' => 'required|exists:raks,id',
            'tipe' => 'required|in:TAMBAH,KURANG',
            'jumlah' => 'required|integer|min:1',
            'alasan' => 'required|string',
        ]);

        StockAdjustment::create([
            'part_id' => $validated['part_id'],
            'gudang_id' => $validated['gudang_id'],
            'rak_id' => $validated['rak_id'],
            'tipe' => $validated['tipe'],
            'jumlah' => $validated['jumlah'],
            'alasan' => $validated['alasan'],
            'status' => 'PENDING_APPROVAL',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.stock-adjustments.index')->with('success', 'Permintaan adjusment stok berhasil dibuat dan menunggu persetujuan.');
    }

    public function approve(StockAdjustment $stockAdjustment)
    {
        $this->authorize('approve-adjustment', $stockAdjustment);

        if ($stockAdjustment->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        DB::beginTransaction();
        try {
            $inventory = null;

            if ($stockAdjustment->tipe === 'KURANG') {
                $inventory = Inventory::where('part_id', $stockAdjustment->part_id)
                    ->where('gudang_id', $stockAdjustment->gudang_id)
                    ->where('rak_id', $stockAdjustment->rak_id)
                    ->firstOrFail();
            } else { // TAMBAH
                $inventory = Inventory::firstOrCreate(
                    [
                        'part_id' => $stockAdjustment->part_id,
                        'gudang_id' => $stockAdjustment->gudang_id,
                        'rak_id' => $stockAdjustment->rak_id
                    ],
                    ['quantity' => 0]
                );
            }

            $stokSebelum = $inventory->quantity;
            $jumlah = $stockAdjustment->jumlah;

            if ($stockAdjustment->tipe === 'KURANG') {
                if ($stokSebelum < $jumlah) {
                    throw new \Exception('Stok tidak mencukupi untuk penyesuaian pengurangan.');
                }
                $inventory->decrement('quantity', $jumlah);
            } else { // TAMBAH
                $inventory->increment('quantity', $jumlah);
            }

            // --- PERBAIKAN LOGIKA DI SINI ---
            \App\Models\StockMovement::create([
                'part_id' => $stockAdjustment->part_id,
                'gudang_id' => $stockAdjustment->gudang_id,
                'rak_id' => $stockAdjustment->rak_id,
                'jumlah' => ($stockAdjustment->tipe === 'TAMBAH' ? $jumlah : -$jumlah),
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $inventory->quantity,
                'referensi_type' => get_class($stockAdjustment),
                'referensi_id' => $stockAdjustment->id,
                'keterangan' => $stockAdjustment->alasan,
                'user_id' => $stockAdjustment->created_by, // <-- DIUBAH DARI Auth::id()
            ]);
            // --- END PERBAIKAN ---

            $stockAdjustment->status = 'APPROVED';
            $stockAdjustment->approved_by = Auth::id();
            $stockAdjustment->approved_at = now();
            $stockAdjustment->save();

            DB::commit();
            return redirect()->route('admin.stock-adjustments.index')->with('success', 'Adjusment stok disetujui dan stok telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, StockAdjustment $stockAdjustment)
    {
        $this->authorize('approve-adjustment', $stockAdjustment);
        $request->validate(['rejection_reason' => 'required|string|min:10']);

        if ($stockAdjustment->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $stockAdjustment->status = 'REJECTED';
        $stockAdjustment->rejection_reason = $request->rejection_reason;
        $stockAdjustment->approved_by = Auth::id();
        $stockAdjustment->approved_at = now();
        $stockAdjustment->save();

        return redirect()->route('admin.stock-adjustments.index')->with('success', 'Permintaan adjusment stok telah ditolak.');
    }
}
