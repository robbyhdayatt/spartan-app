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
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot()
    {
        $this->registerPolicies();

        // Super Admin (SA) selalu bisa akses semuanya
        Gate::before(function ($user, $ability) {
            if ($user->jabatan->singkatan === 'SA') {
                return true;
            }
        });

        // =================================================================
        // DEFINISI ROLE UTAMA
        // =================================================================
        Gate::define('is-super-admin', fn(User $user) => $user->jabatan->singkatan === 'SA');
        Gate::define('is-manager', fn(User $user) => $user->jabatan->singkatan === 'MA');
        Gate::define('is-kepala-gudang', fn(User $user) => $user->jabatan->singkatan === 'KG');
        
        // Admin Gudang (AG) adalah peran "Super Staff" baru
        Gate::define('is-admin-gudang', fn(User $user) => $user->jabatan->singkatan === 'AG');

        // =================================================================
        // DEFINISI TUGAS (PERMISSIONS) - Disederhanakan ke AG
        // =================================================================

        // 1. Dashboard
        Gate::define('view-dashboard', function (User $user) {
            return in_array($user->jabatan->singkatan, ['SA', 'MA', 'KG', 'AG']);
        });

        // 2. Purchase Order
        Gate::define('view-purchase-orders', function(User $user) {
            return in_array($user->jabatan->singkatan, ['MA', 'KG', 'AG']);
        });
        Gate::define('create-po', function(User $user) {
            return in_array($user->jabatan->singkatan, ['AG']); // Tugas Admin Gudang
        });
        Gate::define('approve-po', function (User $user, PurchaseOrder $purchaseOrder) {
            return $user->jabatan->singkatan === 'KG' && $user->gudang_id === $purchaseOrder->gudang_id;
        });

        // 3. Inbound Process (Receiving, QC, Putaway) - SEMUA KE AG
        Gate::define('can-receive', fn(User $user) => $user->jabatan->singkatan === 'AG');
        Gate::define('can-receive-mutation', fn(User $user) => $user->jabatan->singkatan === 'AG');
        Gate::define('can-qc', fn(User $user) => $user->jabatan->singkatan === 'AG');
        Gate::define('can-putaway', fn(User $user) => $user->jabatan->singkatan === 'AG');

        // 4. Stock Management (Adjustment, Mutasi, Karantina) - SEMUA KE AG
        Gate::define('can-manage-stock', fn(User $user) => in_array($user->jabatan->singkatan, ['AG']));
        Gate::define('can-process-quarantine', fn(User $user) => in_array($user->jabatan->singkatan, ['AG']));
        Gate::define('view-stock-management', fn(User $user) => in_array($user->jabatan->singkatan, ['KG', 'AG']));
        
        Gate::define('approve-adjustment', function (User $user, StockAdjustment $stockAdjustment) {
            return $user->jabatan->singkatan === 'KG' && $user->gudang_id === $stockAdjustment->gudang_id;
        });
        Gate::define('approve-mutation', function (User $user, StockMutation $stockMutation) {
            return $user->jabatan->singkatan === 'KG' && $user->gudang_id === $stockMutation->gudang_asal_id;
        });

        // 5. Returns (Pembelian & Penjualan)
        Gate::define('manage-purchase-returns', fn(User $user) => in_array($user->jabatan->singkatan, ['AG']));
        Gate::define('manage-sales-returns', fn(User $user) => in_array($user->jabatan->singkatan, ['AG'])); // Sales dihapus, dilimpahkan ke AG

        // 6. Sales / Penjualan
        Gate::define('view-sales', fn(User $user) => in_array($user->jabatan->singkatan, ['MA', 'AG']));
        Gate::define('manage-sales', fn(User $user) => in_array($user->jabatan->singkatan, ['AG']));
        Gate::define('view-sales-returns', fn(User $user) => in_array($user->jabatan->singkatan, ['MA', 'AG']));

        // 7. Reports
        Gate::define('view-reports', function(User $user) {
            return in_array($user->jabatan->singkatan, ['MA', 'KG', 'AG']);
        });

        // 8. Menu Grouping Gates
        Gate::define('access-master-data', function(User $user) {
            return in_array($user->jabatan->singkatan, ['SA', 'MA', 'KG', 'AG']);
        });

        Gate::define('access-gudang-transaksi', function(User $user) {
            return in_array($user->jabatan->singkatan, ['KG', 'AG']);
        });

        Gate::define('access-penjualan-pelanggan', function(User $user) {
            return in_array($user->jabatan->singkatan, ['MA', 'AG', 'SA']);
        });

        Gate::define('access-marketing', function(User $user) {
            return in_array($user->jabatan->singkatan, ['MA', 'AG']); // Marketing opsional buat AG
        });

        Gate::define('is-not-kepala-gudang', function ($user) {
            return $user->jabatan->nama_jabatan !== 'Kepala Gudang';
        });

        Gate::define('is-kepala-gudang-only', function ($user) {
            return $user->jabatan->nama_jabatan === 'Kepala Gudang';
        });
    }
}