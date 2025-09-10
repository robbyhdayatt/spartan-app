<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder;
use App\Models\Receiving;
use App\Models\Penjualan;
use App\Models\Part;
use App\Models\StockAdjustment;
use App\Models\StockMutation;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('view-dashboard');

        $user = Auth::user();
        $gudangId = ($user->jabatan->nama_jabatan === 'Kepala Gudang') ? $user->gudang_id : null;

        // 1. Jumlah Transaksi Hari Ini (disaring per gudang jika perlu)
        $poToday = PurchaseOrder::whereDate('created_at', today())
            ->when($gudangId, fn($query) => $query->where('gudang_id', $gudangId))
            ->count();
        $receivingToday = Receiving::whereDate('created_at', today())
            ->when($gudangId, fn($query) => $query->where('gudang_id', $gudangId))
            ->count();
        $salesToday = Penjualan::whereDate('created_at', today())
            ->when($gudangId, fn($query) => $query->where('gudang_id', $gudangId))
            ->count();

        // 2. Nilai Stok (disaring per gudang jika perlu)
        $stockValue = DB::table('inventories')
            ->join('parts', 'inventories.part_id', '=', 'parts.id')
            ->when($gudangId, fn($query) => $query->where('inventories.gudang_id', $gudangId))
            ->sum(DB::raw('inventories.quantity * parts.harga_beli_default'));

        // 3. Stok Kritis (disaring per gudang jika perlu)
        $criticalStockParts = Part::select('parts.nama_part', 'parts.stok_minimum', DB::raw('SUM(inventories.quantity) as total_stock'))
            ->join('inventories', 'parts.id', '=', 'inventories.part_id')
            ->when($gudangId, fn($query) => $query->where('inventories.gudang_id', $gudangId))
            ->groupBy('parts.id', 'parts.nama_part', 'parts.stok_minimum')
            ->havingRaw('total_stock < parts.stok_minimum AND parts.stok_minimum > 0')
            ->get();

        // 4. Tugas Approval (sudah otomatis per gudang untuk Kepala Gudang)
        $pendingApprovals = [];
        if ($user->jabatan->nama_jabatan === 'Kepala Gudang') {
            $pendingApprovals['purchase_orders'] = PurchaseOrder::where('status', 'PENDING_APPROVAL')->where('gudang_id', $user->gudang_id)->get();
            $pendingApprovals['stock_adjustments'] = StockAdjustment::where('status', 'PENDING_APPROVAL')->where('gudang_id', $user->gudang_id)->get();
            $pendingApprovals['stock_mutations'] = StockMutation::where('status', 'PENDING_APPROVAL')->where('gudang_asal_id', $user->gudang_id)->get();
        }

        // 5. Data Grafik Penjualan (disaring per gudang jika perlu)
        $salesData = Penjualan::select(DB::raw('DATE(tanggal_jual) as date'), DB::raw('SUM(total_harga) as total'))
            ->where('tanggal_jual', '>=', now()->subDays(30))
            ->when($gudangId, fn($query) => $query->where('gudang_id', $gudangId))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $salesChartLabels = $salesData->pluck('date')->map(fn ($date) => \Carbon\Carbon::parse($date)->format('d M'));
        $salesChartData = $salesData->pluck('total');

        return view('home', compact(
            'poToday', 'receivingToday', 'salesToday', 'stockValue', 'criticalStockParts',
            'pendingApprovals', 'salesChartLabels', 'salesChartData'
        ));
    }
}
