<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\Inventory;
use App\Models\Part;
use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockAdjustment::with(['part', 'gudang', 'createdBy', 'approvedBy'])->latest()->paginate(15);
        return view('admin.stock_adjustments.index', compact('adjustments'));
    }

    public function create()
    {

        $user = auth()->user();
        $parts = \App\Models\Part::where('is_active', true)->get();

        // Cek peran pengguna untuk menentukan pilihan gudang
        if ($user->gudang_id) {
            // Jika user terikat pada gudang, hanya tampilkan gudangnya
            $gudangs = \App\Models\Gudang::where('id', $user->gudang_id)->get();
        } else {
            // Jika Super Admin, tampilkan semua gudang
            $gudangs = \App\Models\Gudang::where('is_active', true)->get();
        }

        return view('admin.stock_adjustments.create', compact('gudangs', 'parts'));
    }

    public function store(Request $request)
    {
        $this->authorize('can-manage-stock');
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'gudang_id' => 'required|exists:gudangs,id',
            'tipe' => 'required|in:TAMBAH,KURANG',
            'jumlah' => 'required|integer|min:1',
            'alasan' => 'required|string',
        ]);

        StockAdjustment::create([
            'part_id' => $validated['part_id'],
            'gudang_id' => $validated['gudang_id'],
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
        $this->authorize('perform-approval', $stockAdjustment);
        $user = Auth::user();
        if ($user->jabatan->nama_jabatan !== 'Kepala Gudang' || $user->gudang_id !== $stockAdjustment->gudang_id) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk aksi ini.');
        }
        if ($stockAdjustment->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        DB::beginTransaction();
        try {
            $inventoryItems = Inventory::where('part_id', $stockAdjustment->part_id)
                ->where('gudang_id', $stockAdjustment->gudang_id)->get();
            if ($inventoryItems->isEmpty()) {
                throw new \Exception('Stok untuk part ini tidak ditemukan di gudang. Lakukan Putaway terlebih dahulu.');
            }
            $inventory = $inventoryItems->first();

            // --- MULAI LOGGING ---
            $stokSebelum = $inventory->quantity;
            $jumlah = $stockAdjustment->jumlah;

            if ($stockAdjustment->tipe === 'KURANG') {
                if ($stokSebelum < $jumlah) {
                    throw new \Exception('Stok tidak mencukupi untuk penyesuaian pengurangan.');
                }
                $stokSesudah = $stokSebelum - $jumlah;
                $inventory->quantity = $stokSesudah;
            } else { // TAMBAH
                $stokSesudah = $stokSebelum + $jumlah;
                $inventory->quantity = $stokSesudah;
            }
            $inventory->save();

            \App\Models\StockMovement::create([
                'part_id' => $stockAdjustment->part_id,
                'gudang_id' => $stockAdjustment->gudang_id,
                'tipe_gerakan' => 'ADJUSMENT_' . $stockAdjustment->tipe,
                'jumlah' => ($stockAdjustment->tipe === 'TAMBAH' ? $jumlah : -$jumlah), // Positif atau negatif
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'referensi' => 'ADJ-' . $stockAdjustment->id,
                'keterangan' => $stockAdjustment->alasan,
                'user_id' => $user->id,
            ]);
            // --- AKHIR LOGGING ---

            $stockAdjustment->status = 'APPROVED';
            $stockAdjustment->approved_by = $user->id;
            $stockAdjustment->approved_at = now();
            $stockAdjustment->save();

            DB::commit();
            return redirect()->route('admin.stock-adjustments.index')->with('success', 'Adjusment stok disetujui dan stok telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject(StockAdjustment $stockAdjustment)
    {
        $this->authorize('perform-approval', $stockAdjustment);
        $user = Auth::user();
        if ($user->jabatan->nama_jabatan !== 'Kepala Gudang' || $user->gudang_id !== $stockAdjustment->gudang_id) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk aksi ini.');
        }

        if ($stockAdjustment->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $stockAdjustment->status = 'REJECTED';
        $stockAdjustment->approved_by = $user->id;
        $stockAdjustment->approved_at = now();
        $stockAdjustment->save();

        return redirect()->route('admin.stock-adjustments.index')->with('success', 'Permintaan adjusment stok telah ditolak.');
    }
}
