<?php

namespace App\Http\Controllers;

use App\Models\Incentive;
use App\Models\Part;
use App\Models\Penjualan;
use App\Models\PurchaseOrder;
use App\Models\Receiving;
use App\Models\SalesTarget;
use App\Models\StockAdjustment;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard based on user role.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $this->authorize('view-dashboard');

        $user = Auth::user();
        $jabatanSingkatan = $user->jabatan->singkatan;

        $viewName = 'dashboards._default';
        $data = [];

        switch ($jabatanSingkatan) {
            case 'SA':
            case 'MA':
                $viewName = 'dashboards._superadmin';
                $data = $this->getSuperAdminData();
                break;

            case 'KG':
                $viewName = 'dashboards._kepala_gudang';
                $data = $this->getKepalaGudangData($user);
                break;

            case 'PJG':
                $viewName = 'dashboards._pj_gudang';
                $data = $this->getPjGudangData($user);
                break;

                case 'SLS':
                $viewName = 'dashboards._sales';
                $data = $this->getSalesData($user);
                break;

            case 'SR':
                $viewName = 'dashboards._staff_receiving';
                $data = $this->getStaffReceivingData($user);
                break;

            case 'QC':
                $viewName = 'dashboards._staff_qc';
                $data = $this->getStaffQcData($user);
                break;

            case 'SP':
                $viewName = 'dashboards._staff_putaway';
                $data = $this->getStaffPutawayData($user);
                break;

            case 'SSC':
                $viewName = 'dashboards._staff_stock_control';
                $data = $this->getStaffStockControlData($user);
                break;
        }

        return view('home', compact('viewName', 'data'));
    }

    /**
     * Mengambil data untuk dashboard Super Admin & Manajer Area.
     */
    private function getSuperAdminData()
    {
        // Data Statistik Utama
        $poToday = PurchaseOrder::whereDate('created_at', today())->count();
        $receivingToday = Receiving::whereDate('created_at', today())->count();
        $salesToday = Penjualan::whereDate('created_at', today())->count();
        $stockValue = DB::table('inventories')
            ->join('parts', 'inventories.part_id', '=', 'parts.id')
            ->sum(DB::raw('inventories.quantity * parts.harga_beli_default'));

        // Data Stok Kritis
        $criticalStockParts = Part::select('parts.nama_part', 'parts.stok_minimum', DB::raw('SUM(inventories.quantity) as total_stock'))
            ->join('inventories', 'parts.id', '=', 'inventories.part_id')
            ->groupBy('parts.id', 'parts.nama_part', 'parts.stok_minimum')
            ->havingRaw('total_stock < parts.stok_minimum AND parts.stok_minimum > 0')
            ->get();

        // Data untuk Grafik Penjualan 30 Hari
        $salesData = Penjualan::select(DB::raw('DATE(tanggal_jual) as date'), DB::raw('SUM(total_harga) as total'))
            ->where('tanggal_jual', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $salesChartLabels = $salesData->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('d M'));
        $salesChartData = $salesData->pluck('total');

        return compact('poToday', 'receivingToday', 'salesToday', 'stockValue', 'criticalStockParts', 'salesChartLabels', 'salesChartData');
    }

    /**
     * Mengambil data untuk dashboard Kepala Gudang.
     */
    private function getKepalaGudangData($user)
    {
        $gudangId = $user->gudang_id;

        // Tugas Approval
        $pendingApprovals = [
            'purchase_orders' => PurchaseOrder::where('status', 'PENDING_APPROVAL')->where('gudang_id', $gudangId)->get(),
            'stock_adjustments' => StockAdjustment::where('status', 'PENDING_APPROVAL')->where('gudang_id', $gudangId)->get(),
            'stock_mutations' => StockMutation::where('status', 'PENDING_APPROVAL')->where('gudang_asal_id', $gudangId)->get(),
        ];

        // Info Stok Gudang
        $stockValue = DB::table('inventories')
            ->join('parts', 'inventories.part_id', '=', 'parts.id')
            ->where('inventories.gudang_id', $gudangId)
            ->sum(DB::raw('inventories.quantity * parts.harga_beli_default'));

        $criticalStockParts = Part::select('parts.nama_part', 'parts.stok_minimum', DB::raw('SUM(inventories.quantity) as total_stock'))
            ->join('inventories', 'parts.id', '=', 'inventories.part_id')
            ->where('inventories.gudang_id', $gudangId)
            ->groupBy('parts.id', 'parts.nama_part', 'parts.stok_minimum')
            ->havingRaw('total_stock < parts.stok_minimum AND parts.stok_minimum > 0')
            ->get();

        return compact('pendingApprovals', 'stockValue', 'criticalStockParts');
    }

    /**
     * Mengambil data untuk dashboard Sales.
     */
    private function getSalesData($user)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // 1. Ambil target sales untuk bulan ini (dari data insentif)
        $salesTarget = \App\Models\SalesTarget::where('user_id', $user->id)
            ->where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->first();

        $targetAmount = $salesTarget ? $salesTarget->target_value : 0;

        // 2. Hitung total penjualan sales di bulan ini SECARA LANGSUNG (REAL-TIME)
        $achievedAmount = Penjualan::where('sales_id', $user->id)
            ->whereMonth('tanggal_jual', $currentMonth)
            ->whereYear('tanggal_jual', $currentYear)
            ->sum('total_harga');

        // 3. Hitung ulang persentase pencapaian berdasarkan data live
        $achievementPercentage = ($targetAmount > 0) ? ($achievedAmount / $targetAmount) * 100 : 0;

        // 4. Ambil data insentif yang sudah dihitung sebelumnya (jika ada)
        $incentive = Incentive::whereHas('salesTarget', function ($query) use ($salesTarget) {
            if ($salesTarget) {
                $query->where('id', $salesTarget->id);
            }
        })->first();
        $incentiveAmount = $incentive->jumlah_insentif ?? 0;


        // 5. Ambil 5 transaksi penjualan terakhir
        $recentSales = Penjualan::where('sales_id', $user->id)
            ->orderBy('tanggal_jual', 'desc')
            ->limit(5)
            ->get();

        return compact('targetAmount', 'achievedAmount', 'achievementPercentage', 'incentiveAmount', 'recentSales');
    }

    /**
     * Mengambil data untuk dashboard Staff Receiving.
     */
    private function getStaffReceivingData($user)
    {
        $gudangId = $user->gudang_id;

        // 1. Ambil tugas penerimaan dari Purchase Order
        $pendingPoReceivings = PurchaseOrder::where('gudang_id', $gudangId)
            ->where('status', 'APPROVED')
            ->with('supplier') // Eager load supplier
            ->orderBy('tanggal_po', 'asc')
            ->get();

        // 2. Ambil tugas penerimaan dari Mutasi
        $pendingMutationReceivings = StockMutation::where('gudang_tujuan_id', $gudangId)
            ->where('status', 'IN_TRANSIT')
            ->with(['gudangAsal', 'part']) // Eager load relasi
            ->orderBy('approved_at', 'asc')
            ->get();

        return compact('pendingPoReceivings', 'pendingMutationReceivings');
    }

    /**
     * Mengambil data untuk dashboard Staff QC.
     */
    private function getStaffQcData($user)
    {
        $gudangId = $user->gudang_id;

        $pendingQc = Receiving::where('gudang_id', $gudangId)
            ->where('status', 'PENDING_QC')
            // Memuat relasi purchaseOrder, DAN di dalam purchaseOrder, muat juga relasi supplier-nya.
            ->with(['purchaseOrder.supplier']) // <-- KODE YANG BENAR
            ->orderBy('tanggal_terima', 'asc')
            ->get();

        return compact('pendingQc');
    }

    /**
     * Mengambil data untuk dashboard Staff Putaway. (FUNGSI BARU)
     */
    private function getStaffPutawayData($user)
    {
        $gudangId = $user->gudang_id;

        $pendingPutaway = Receiving::where('gudang_id', $gudangId)
            ->where('status', 'PENDING_PUTAWAY') // <-- KODE YANG BENAR
            ->with(['purchaseOrder.supplier'])   // <-- TAMBAHKAN INI
            ->orderBy('qc_at', 'asc')
            ->get();

        return compact('pendingPutaway');
    }

    /**
     * Mengambil data untuk dashboard Staff Stock Control. (FUNGSI BARU)
     */
    private function getStaffStockControlData($user)
    {
        // Hitung jumlah pengajuan bulan ini
        $adjustmentCount = StockAdjustment::where('created_by', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $mutationCount = StockMutation::where('created_by', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Ambil 5 aktivitas terakhir
        $adjustments = StockAdjustment::where('created_by', $user->id)->latest()->limit(5)->get()->map(function ($item) {
            $item->type = 'adjustment';
            return $item;
        });

        $mutations = StockMutation::where('created_by', $user->id)->latest()->limit(5)->get()->map(function ($item) {
            $item->type = 'mutation';
            return $item;
        });

        // Gabungkan kedua koleksi, urutkan berdasarkan tanggal terbaru, dan ambil 5 teratas
        $recentActivities = $adjustments->concat($mutations)
            ->sortByDesc('created_at')
            ->take(5);

        return compact('adjustmentCount', 'mutationCount', 'recentActivities');
    }

    /**
     * Mengambil data untuk dashboard PJ Gudang. (FUNGSI BARU)
     */
    private function getPjGudangData($user)
    {
        $gudangId = $user->gudang_id;

        // Hitung jumlah item di setiap tahap operasional gudang
        $pendingReceivingCount = PurchaseOrder::where('gudang_id', $gudangId)
            ->where('status', 'APPROVED')
            ->count();

        $pendingQcCount = Receiving::where('gudang_id', $gudangId)
            ->where('status', 'PENDING_QC')
            ->count();

        $pendingPutawayCount = Receiving::where('gudang_id', $gudangId)
            ->where('status', 'QC_PASSED')
            ->count();

        return compact('pendingReceivingCount', 'pendingQcCount', 'pendingPutawayCount');
    }
}
