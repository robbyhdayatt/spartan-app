<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Part;
use App\Models\StockMovement;
use App\Models\Gudang;
use App\Models\Inventory;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockByWarehouseExport;
use App\Exports\SalesJournalExport;
use App\Models\PenjualanDetail;
use App\Exports\PurchaseJournalExport;
use App\Models\ReceivingDetail;
use App\Exports\InventoryValueExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\StockCardExport;


class ReportController extends Controller
{
    public function stockCard(Request $request)
    {
        // Ambil semua part dan gudang untuk pilihan dropdown
        $user = Auth::user();
        $gudangs = Gudang::where('is_active', true)->orderBy('nama_gudang')->get();
        $movements = collect(); // Buat koleksi kosong secara default

        // Cek jika user adalah Kepala Gudang
        if ($user->jabatan->nama_jabatan === 'Kepala Gudang') {
            // Jika ya, paksa pilihan gudang hanya gudangnya sendiri
            $gudangs = Gudang::where('id', $user->gudang_id)->get();
        } else {
            // Jika bukan, tampilkan semua gudang
            $gudangs = Gudang::where('is_active', true)->orderBy('nama_gudang')->get();
        }

        // Ambil semua part untuk pilihan dropdown
        $parts = Part::where('is_active', true)->orderBy('nama_part')->get();

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        // Jika ada part yang dipilih dari form, cari datanya
        if ($request->filled('part_id')) {
            $query = StockMovement::where('part_id', $request->part_id)
                ->with(['gudang', 'user']) // Eager load untuk efisiensi
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);

            // Tambahkan filter gudang jika dipilih
            if ($request->filled('gudang_id')) {
                $query->where('gudang_id', $request->gudang_id);
            }

            // Jika yang login Kepala Gudang, paksa filter gudang
            if ($user->jabatan->nama_jabatan === 'Kepala Gudang') {
                $query->where('gudang_id', $user->gudang_id);
            }

            $movements = $query->oldest()->get();
        }

        return view('admin.reports.stock_card', compact('parts', 'gudangs', 'movements', 'startDate', 'endDate'));
    }

    public function stockByWarehouse(Request $request)
    {
        $user = Auth::user();
        $inventoryItems = collect();
        $gudangs = collect();
        $selectedGudang = null;

        if ($user->jabatan->nama_jabatan === 'Kepala Gudang') {
            // Jika Kepala Gudang, paksa filter dan langsung tampilkan data
            $gudangs = Gudang::where('id', $user->gudang_id)->get();
            $selectedGudang = $user->gudang;
            $inventoryItems = Inventory::where('gudang_id', $user->gudang_id)
                ->with(['part', 'rak'])
                ->where('quantity', '>', 0)
                ->get()
                ->sortBy('part.nama_part');
        } else {
            // Jika Super Admin atau Manajer, izinkan memilih dari dropdown
            $gudangs = Gudang::where('is_active', true)->orderBy('nama_gudang')->get();
            if ($request->filled('gudang_id')) {
                $selectedGudang = Gudang::find($request->gudang_id);
                $inventoryItems = Inventory::where('gudang_id', $request->gudang_id)
                    ->with(['part', 'rak'])
                    ->where('quantity', '>', 0)
                    ->get()
                    ->sortBy('part.nama_part');
            }
        }

        return view('admin.reports.stock_by_warehouse', compact('inventoryItems', 'gudangs', 'selectedGudang'));
    }

    public function exportStockByWarehouse(Request $request)
    {
        $request->validate([
            'gudang_id' => 'required|exists:gudangs,id'
        ]);

        $gudang = Gudang::find($request->gudang_id);
        $fileName = 'Laporan Stok - ' . $gudang->kode_gudang . ' - ' . now()->format('d-m-Y') . '.xlsx';

        return Excel::download(new StockByWarehouseExport($request->gudang_id), $fileName);
    }

    public function salesJournal(Request $request)
    {
        // Set default date range ke bulan ini jika tidak ada input
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $salesDetails = PenjualanDetail::with(['penjualan.konsumen', 'penjualan.sales', 'part'])
            ->whereHas('penjualan', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_jual', [$startDate, $endDate]);
            })
            ->latest()
            ->get();

        return view('admin.reports.sales_journal', compact('salesDetails', 'startDate', 'endDate'));
    }

    public function exportSalesJournal(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $fileName = 'Jurnal Penjualan - ' . $startDate . ' sampai ' . $endDate . '.xlsx';

        return Excel::download(new SalesJournalExport($startDate, $endDate), $fileName);
    }

    public function purchaseJournal(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $purchaseDetails = ReceivingDetail::with(['receiving.purchaseOrder.supplier', 'part'])
            ->whereHas('receiving', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_terima', [$startDate, $endDate]);
            })
            ->latest()
            ->get();

        return view('admin.reports.purchase_journal', compact('purchaseDetails', 'startDate', 'endDate'));
    }

    public function exportPurchaseJournal(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $fileName = 'Jurnal Pembelian - ' . $startDate . ' sampai ' . $endDate . '.xlsx';

        return Excel::download(new PurchaseJournalExport($startDate, $endDate), $fileName);
    }

    public function inventoryValue()
    {
        $inventoryDetails = Inventory::with(['part', 'gudang', 'rak'])
            ->where('quantity', '>', 0)
            ->get();

        // Ganti 'harga_beli_default' menjadi 'harga_beli_rata_rata'
        $totalValue = $inventoryDetails->sum(function($item) {
            return $item->quantity * $item->part->harga_beli_rata_rata;
        });

        return view('admin.reports.inventory_value', compact('inventoryDetails', 'totalValue'));
    }

    public function exportInventoryValue()
    {
        $fileName = 'Laporan Nilai Persediaan - ' . now()->format('d-m-Y') . '.xlsx';
        return Excel::download(new InventoryValueExport(), $fileName);
    }

    public function salesPurchaseAnalysis(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // Top 10 Selling Parts (by quantity)
        $topSellingParts = \App\Models\PenjualanDetail::select('part_id', DB::raw('SUM(qty_jual) as total_qty'))
            ->with('part')
            ->whereHas('penjualan', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_jual', [$startDate, $endDate]);
            })
            ->groupBy('part_id')
            ->orderBy('total_qty', 'desc')
            ->limit(10)
            ->get();

        // Top 10 Purchased Parts (by quantity)
        $topPurchasedParts = \App\Models\ReceivingDetail::select('part_id', DB::raw('SUM(qty_terima) as total_qty'))
            ->with('part')
            ->whereHas('receiving', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_terima', [$startDate, $endDate]);
            })
            ->groupBy('part_id')
            ->orderBy('total_qty', 'desc')
            ->limit(10)
            ->get();

        // Sales by Category (for chart)
        $salesByCategory = \App\Models\PenjualanDetail::join('parts', 'penjualan_details.part_id', '=', 'parts.id')
            ->join('categories', 'parts.category_id', '=', 'categories.id')
            ->join('penjualans', 'penjualan_details.penjualan_id', '=', 'penjualans.id')
            ->whereBetween('penjualans.tanggal_jual', [$startDate, $endDate])
            ->groupBy('categories.nama_kategori')
            ->select('categories.nama_kategori', DB::raw('SUM(penjualan_details.subtotal) as total_value'))
            ->pluck('total_value', 'nama_kategori');

        return view('admin.reports.sales_purchase_analysis', compact(
            'topSellingParts',
            'topPurchasedParts',
            'salesByCategory',
            'startDate',
            'endDate'
        ));
    }

    public function stockReport()
    {
        $inventoryDetails = Inventory::with(['part', 'gudang', 'rak'])
            ->where('quantity', '>', 0)
            ->get()
            ->sortBy(['part.nama_part', 'gudang.nama_gudang']); // Urutkan berdasarkan nama part, lalu gudang

        return view('admin.reports.stock_report', compact('inventoryDetails'));
    }

    public function exportStockCard(Request $request)
    {
        $request->validate([
            'part_id' => 'required|exists:parts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'gudang_id' => 'nullable|exists:gudangs,id'
        ]);

        $part = Part::findOrFail($request->part_id);
        $fileName = 'Kartu Stok - ' . $part->kode_part . ' - ' . $request->start_date . ' sampai ' . $request->end_date . '.xlsx';

        return Excel::download(new StockCardExport(
            $request->part_id,
            $request->gudang_id,
            $request->start_date,
            $request->end_date
        ), $fileName);
    }
}
