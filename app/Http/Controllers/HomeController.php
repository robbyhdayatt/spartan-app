<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Penjualan;
use App\Models\PurchaseOrder;
use App\Models\Part;
use App\Models\StockAdjustment;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $role = $user->jabatan->singkatan;

        // --- SUPER ADMIN & MANAJER AREA (Analisis Lengkap) ---
        if (in_array($role, ['SA', 'MA'])) {
            // Data Grafik Penjualan & Pembelian 6 Bulan Terakhir
            $months = collect([]);
            $salesData = collect([]);
            $purchaseData = collect([]);

            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthName = $date->format('M Y');
                $months->push($monthName);

                $sales = Penjualan::whereMonth('tanggal_jual', $date->month)
                                  ->whereYear('tanggal_jual', $date->year)
                                  ->sum('total_harga');
                
                $purchases = PurchaseOrder::whereMonth('tanggal_po', $date->month)
                                          ->whereYear('tanggal_po', $date->year)
                                          ->where('status', '!=', 'CANCELLED')
                                          ->sum('total_amount');

                $salesData->push($sales);
                $purchaseData->push($purchases);
            }

            // Top 5 Produk Terlaris
            $topProducts = DB::table('penjualan_details')
                ->join('parts', 'penjualan_details.part_id', '=', 'parts.id')
                ->select('parts.nama_part', DB::raw('SUM(penjualan_details.qty_jual) as total_qty'))
                ->groupBy('parts.id', 'parts.nama_part')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get();

            $view = ($role === 'SA') ? 'dashboards._superadmin' : 'dashboards._default';
            
            return view($view, compact('months', 'salesData', 'purchaseData', 'topProducts'));
        } 
        
        // --- KEPALA GUDANG (Approval & Monitoring) ---
        elseif ($role === 'KG') {
            // Menghitung stok gudang ini
            $gudangId = $user->gudang_id;
            
            // Notifikasi Approval
            $needApprovalPO = PurchaseOrder::where('gudang_id', $gudangId)
                                           ->where('status', 'PENDING_APPROVAL')
                                           ->count();
                                           
            $needApprovalAdj = StockAdjustment::where('gudang_id', $gudangId)
                                              ->where('status', 'PENDING')
                                              ->count();

            // 5 Transaksi Terakhir
            $recentMovements = \App\Models\StockMovement::with(['part', 'user'])
                ->where('gudang_id', $gudangId)
                ->latest()
                ->limit(5)
                ->get();

            return view('dashboards._kepala_gudang', compact('needApprovalPO', 'needApprovalAdj', 'recentMovements'));
        } 
        
        // --- ADMIN GUDANG (Operasional & Stok Alert) ---
        elseif ($role === 'AG') {
            $gudangId = $user->gudang_id;

            // Cari barang yang stoknya di bawah minimum (Khusus gudang ini)
            // Menggunakan subquery untuk menghitung total stok batch per part di gudang ini
            $lowStockParts = Part::select('parts.*')
                ->selectSub(function ($query) use ($gudangId) {
                    $query->from('inventory_batches')
                          ->selectRaw('COALESCE(SUM(quantity), 0)')
                          ->whereColumn('part_id', 'parts.id')
                          ->where('gudang_id', $gudangId);
                }, 'current_stock')
                ->havingRaw('current_stock <= stok_minimum')
                ->get();

            return view('dashboards._admin_gudang', compact('lowStockParts'));
        }

        return view('home');
    }
}