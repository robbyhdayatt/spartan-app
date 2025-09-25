<?php

namespace App\Providers;

use App\Models\User;
use App\Models\StockAdjustment;
use App\Models\StockMutation;
use App\Models\PurchaseOrder;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /**
         * Gate `before` ini memberikan hak akses super admin.
         * Pengguna dengan singkatan 'SA' akan selalu bisa mengakses semuanya.
         */
        Gate::before(function ($user, $ability) {
            if ($user->jabatan->singkatan === 'SA') {
                return true;
            }
        });

        // =================================================================
        // DEFINISI GATE BERDASARKAN PERAN (ROLE-BASED)
        // =================================================================

        Gate::define('is-super-admin', fn(User $user) => $user->jabatan->singkatan === 'SA');
        Gate::define('is-manager', fn(User $user) => $user->jabatan->singkatan === 'MA');
        Gate::define('is-kepala-gudang', fn(User $user) => $user->jabatan->singkatan === 'KG');
        Gate::define('is-pj-gudang', fn(User $user) => $user->jabatan->singkatan === 'PJG');
        Gate::define('is-sales', fn(User $user) => $user->jabatan->singkatan === 'SLS');

        Gate::define('is-staff-gudang', function (User $user) {
            return in_array($user->jabatan->singkatan, ['SR', 'QC', 'SP', 'SSC']);
        });

        // =================================================================
        // DEFINISI GATE BERDASARKAN TUGAS (PERMISSION-BASED)
        // =================================================================

        // Izin untuk melihat dashboard
        Gate::define('view-dashboard', function (User $user) {
            return in_array($user->jabatan->singkatan, [
                'SA',
                'MA',
                'KG',
                'SLS',
                'SR',
                'QC',
                'SP',
                'SSC',
                'PJG'
            ]);
        });

        // Izin terkait Purchase Order
        Gate::define('view-purchase-orders', function(User $user) {
            return in_array($user->jabatan->singkatan, ['MA', 'KG', 'PJG']);
        });

        Gate::define('create-po', function(User $user) {
            return in_array($user->jabatan->singkatan, ['PJG']);
        });

        Gate::define('approve-po', function (User $user, PurchaseOrder $purchaseOrder) {
            return $user->jabatan->singkatan === 'KG' && $user->gudang_id === $purchaseOrder->gudang_id;
        });

        Gate::define('approve-adjustment', function (User $user, StockAdjustment $stockAdjustment) {
            return $user->jabatan->singkatan === 'KG' && $user->gudang_id === $stockAdjustment->gudang_id;
        });

        // Izin untuk proses inbound (penerimaan barang)
        Gate::define('can-receive', fn(User $user) => in_array($user->jabatan->singkatan, ['PJG', 'SR']));
        Gate::define('can-receive-mutation', fn(User $user) => in_array($user->jabatan->singkatan, ['PJG', 'SR']));
        Gate::define('can-qc', fn(User $user) => $user->jabatan->singkatan === 'QC');
        Gate::define('can-putaway', fn(User $user) => $user->jabatan->singkatan ==='SP');

        // Izin untuk mengelola stok internal
        Gate::define('can-manage-stock', function(User $user) {
            return in_array($user->jabatan->singkatan, ['PJG', 'SSC']);
        });

        Gate::define('can-process-quarantine', function(User $user) {
            return in_array($user->jabatan->singkatan, ['PJG', 'SSC']);
        });

        Gate::define('view-stock-management', function(User $user) {
            return in_array($user->jabatan->singkatan, ['KG', 'PJG', 'SSC']);
        });

        // Izin untuk retur
        Gate::define('manage-purchase-returns', fn(User $user) => in_array($user->jabatan->singkatan, ['PJG', 'SSC']));
        Gate::define('manage-sales-returns', fn(User $user) => in_array($user->jabatan->singkatan, ['SLS']));

        Gate::define('approve-mutation', function (User $user, StockMutation $stockMutation) {
            return $user->jabatan->singkatan === 'KG' && $user->gudang_id === $stockMutation->gudang_asal_id;
        });

        Gate::define('view-reports', function(User $user) {
            return in_array($user->jabatan->singkatan, ['MA', 'KG', 'PJG']);
        });

        Gate::define('view-sales', function(User $user) {
            return in_array($user->jabatan->singkatan, ['MA', 'SLS']);
        });

        Gate::define('manage-sales', function(User $user) {
            return in_array($user->jabatan->singkatan, ['SLS']);
        });

        Gate::define('view-sales-returns', function(User $user) {
            return in_array($user->jabatan->singkatan, ['MA', 'SLS']);
        });

        // GATE BARU UNTUK GRUP MENU
        Gate::define('access-master-data', function(User $user) {
            // Hanya Super Admin dan Manajer Area yang bisa melihat master data
            return in_array($user->jabatan->singkatan, ['SA', 'MA', 'KG', 'PJG', 'SLS']);
        });

        Gate::define('access-gudang-transaksi', function(User $user) {
            // Semua peran gudang bisa mengakses menu ini
            return in_array($user->jabatan->singkatan, ['KG', 'PJG', 'SR', 'QC', 'SP', 'SSC']);
        });

        Gate::define('access-penjualan-pelanggan', function(User $user) {
            return in_array($user->jabatan->singkatan, ['MA', 'SLS', 'SA']);
        });

        Gate::define('access-marketing', function(User $user) {
            return in_array($user->jabatan->singkatan, ['MA']);
        });

        Gate::define('is-not-kepala-gudang', function ($user) {
            return $user->jabatan->nama_jabatan !== 'Kepala Gudang';
        });

        Gate::define('is-kepala-gudang-only', function ($user) {
            return $user->jabatan->nama_jabatan === 'Kepala Gudang';
        });

    }
}
