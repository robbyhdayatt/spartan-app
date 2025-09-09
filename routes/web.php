<?php

// Make sure all these 'use' statements are at the top of the file
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\GudangController;
use App\Http\Controllers\Admin\RakController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\KonsumenController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PartController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\ReceivingController;
use App\Http\Controllers\Admin\QcController;
use App\Http\Controllers\Admin\PutawayController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\StockMutationController;
use App\Http\Controllers\Admin\PenjualanController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\PurchaseReturnController;
use App\Http\Controllers\Admin\SalesReturnController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\IncentiveController;
use App\Http\Controllers\HomeController; // Import HomeController

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'show'])->name('profile.show');

    // === MASTER DATA & PRODUK ===
    Route::resource('brands', BrandController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('gudangs', GudangController::class);
    Route::resource('raks', RakController::class);
    Route::resource('suppliers', SupplierController::class);

    // Penambahan route untuk search part
    Route::get('parts/search', [PartController::class, 'search'])->name('parts.search');
    Route::resource('parts', PartController::class);
    Route::post('parts/import', [PartController::class, 'import'])->name('parts.import');

    // === TRANSAKSI GUDANG ===
    Route::resource('purchase-orders', PurchaseOrderController::class)->except(['edit', 'update', 'destroy']);
    Route::post('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchase_order}/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
    Route::get('purchase_orders/{purchaseOrder}/details', [PurchaseOrderController::class, 'getPoDetailsApi'])->name('purchase_orders.details_api');

    Route::get('receivings', [ReceivingController::class, 'index'])->name('receivings.index');
    Route::get('receivings/create', [ReceivingController::class, 'create'])->name('receivings.create');
    Route::post('receivings', [ReceivingController::class, 'store'])->name('receivings.store');
    Route::get('receivings/{receiving}', [ReceivingController::class, 'show'])->name('receivings.show');

    Route::get('api/purchase-orders/{purchaseOrder}', [ReceivingController::class, 'getPoDetails'])->name('api.po.details');

    Route::get('quality-control', [QcController::class, 'index'])->name('qc.index');
    Route::get('quality-control/{receiving}/form', [QcController::class, 'showQcForm'])->name('qc.form');
    Route::post('quality-control/{receiving}', [QcController::class, 'storeQcResult'])->name('qc.store');

    Route::get('putaway', [PutawayController::class, 'index'])->name('putaway.index');
    Route::get('putaway/{receiving}/form', [PutawayController::class, 'showPutawayForm'])->name('putaway.form');
    Route::post('putaway/{receiving}', [PutawayController::class, 'storePutaway'])->name('putaway.store');

    Route::get('stock-adjustments', [StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
    Route::get('stock-adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
    Route::post('stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
    Route::post('stock-adjustments/{stockAdjustment}/approve', [StockAdjustmentController::class, 'approve'])->name('stock-adjustments.approve');
    Route::post('stock-adjustments/{stockAdjustment}/reject', [StockAdjustmentController::class, 'reject'])->name('stock-adjustments.reject');

    Route::get('stock-mutations', [StockMutationController::class, 'index'])->name('stock-mutations.index');
    Route::get('stock-mutations/create', [StockMutationController::class, 'create'])->name('stock-mutations.create');
    Route::post('stock-mutations', [StockMutationController::class, 'store'])->name('stock-mutations.store');
    Route::post('stock-mutations/{stockMutation}/approve', [StockMutationController::class, 'approve'])->name('stock-mutations.approve');
    Route::post('stock-mutations/{stockMutation}/reject', [StockMutationController::class, 'reject'])->name('stock-mutations.reject');

    Route::get('purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
    Route::get('purchase-returns/create', [PurchaseReturnController::class, 'create'])->name('purchase-returns.create');
    Route::post('purchase-returns', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
    Route::get('purchase-returns/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchase-returns.show');

    // === PENGGUNA, PENJUALAN, & MARKETING ===
    Route::resource('konsumens', KonsumenController::class);
    Route::resource('users', UserController::class);
    Route::resource('campaigns', CampaignController::class);

    Route::get('penjualans', [PenjualanController::class, 'index'])->name('penjualans.index');
    Route::get('penjualans/create', [PenjualanController::class, 'create'])->name('penjualans.create');
    Route::post('penjualans', [PenjualanController::class, 'store'])->name('penjualans.store');
    Route::get('penjualans/{penjualan}', [PenjualanController::class, 'show'])->name('penjualans.show');

    Route::get('sales-returns', [SalesReturnController::class, 'index'])->name('sales-returns.index');
    Route::get('sales-returns/create', [SalesReturnController::class, 'create'])->name('sales-returns.create');
    Route::post('sales-returns', [SalesReturnController::class, 'store'])->name('sales-returns.store');
    Route::get('sales-returns/{salesReturn}', [SalesReturnController::class, 'show'])->name('sales-returns.show');

    Route::get('incentives/targets', [IncentiveController::class, 'targets'])->name('incentives.targets');
    Route::post('incentives/targets', [IncentiveController::class, 'storeTarget'])->name('incentives.targets.store');
    Route::get('incentives/report', [IncentiveController::class, 'report'])->name('incentives.report');

    // === LAPORAN ===
    Route::get('reports/stock-card', [ReportController::class, 'stockCard'])->name('reports.stock-card');
    Route::get('reports/stock-by-warehouse', [ReportController::class, 'stockByWarehouse'])->name('reports.stock-by-warehouse');
    Route::get('reports/stock-by-warehouse/export', [ReportController::class, 'exportStockByWarehouse'])->name('reports.stock-by-warehouse.export');
    Route::get('reports/stock-report', [ReportController::class, 'stockReport'])->name('reports.stock-report');
    Route::get('reports/sales-journal', [ReportController::class, 'salesJournal'])->name('reports.sales-journal');
    Route::get('reports/sales-journal/export', [ReportController::class, 'exportSalesJournal'])->name('reports.sales-journal.export');
    Route::get('reports/purchase-journal', [ReportController::class, 'purchaseJournal'])->name('reports.purchase-journal');
    Route::get('reports/purchase-journal/export', [ReportController::class, 'exportPurchaseJournal'])->name('reports.purchase-journal.export');
    Route::get('reports/inventory-value', [ReportController::class, 'inventoryValue'])->name('reports.inventory-value');
    Route::get('reports/inventory-value/export', [ReportController::class, 'exportInventoryValue'])->name('reports.inventory-value.export');
    Route::get('reports/sales-purchase-analysis', [ReportController::class, 'salesPurchaseAnalysis'])->name('reports.sales-purchase-analysis');

    // === API (untuk AJAX) ===
    Route::get('api/gudangs/{gudang}/parts', [PenjualanController::class, 'getPartsByGudang'])->name('api.gudang.parts');
    Route::get('api/parts/{part}/stock', [PenjualanController::class, 'getPartStockDetails'])->name('api.part.stock');
    Route::get('api/gudangs/{gudang}/raks', [StockMutationController::class, 'getRaksByGudang'])->name('api.gudang.raks');
    Route::get('api/gudangs/{gudang}/parts', [PenjualanController::class, 'getPartsByGudang'])->name('api.gudang.parts');
    Route::get('api/receivings/{receiving}/failed-items', [PurchaseReturnController::class, 'getFailedItems'])->name('api.receivings.failed-items');
    Route::get('api/penjualans/{penjualan}/returnable-items', [SalesReturnController::class, 'getReturnableItems'])->name('api.penjualans.returnable-items');
});
