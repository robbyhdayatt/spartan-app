<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\PurchaseOrder;
use App\Models\StockAdjustment;
use App\Models\StockMutation;

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

        // Gate untuk Super Admin - bisa melakukan apa saja
        Gate::before(function ($user, $ability) {
            if ($user->jabatan->nama_jabatan === 'Super Admin') {
                return true;
            }
        });

        // --- GATES UNTUK PERAN SPESIFIK ---

        // Manajer Area: hanya bisa melihat
        Gate::define('is-manager', function (User $user) {
            return $user->jabatan->nama_jabatan === 'Manajer Area';
        });

        // Kepala Gudang
        Gate::define('is-kepala-gudang', function (User $user) {
            return $user->jabatan->nama_jabatan === 'Kepala Gudang';
        });

        // PJ Gudang
        Gate::define('is-pj-gudang', function (User $user) {
            return $user->jabatan->nama_jabatan === 'PJ Gudang';
        });

        Gate::define('view-purchase-orders', function (User $user) {
            return in_array($user->jabatan->nama_jabatan, ['Kepala Gudang', 'PJ Gudang']);
        });

        Gate::define('can-receive', function(User $user) {
            return in_array($user->jabatan->nama_jabatan, ['Staff Receiving', 'PJ Gudang']);
        });
        Gate::define('can-qc', function(User $user) {
            return in_array($user->jabatan->nama_jabatan, ['Staff QC', 'PJ Gudang']);
        });
        Gate::define('can-putaway', function(User $user) {
            return in_array($user->jabatan->nama_jabatan, ['Staff Putaway', 'PJ Gudang']);
        });
        Gate::define('can-manage-stock', function(User $user) {
            return in_array($user->jabatan->nama_jabatan, ['Staff Stock Control', 'PJ Gudang', 'Kepala Gudang']);
        });

        // Gate untuk Retur Pembelian (TAMBAHKAN INI)
        Gate::define('manage-purchase-returns', function (User $user) {
            return in_array($user->jabatan->nama_jabatan, ['Staff Stock Control', 'PJ Gudang']);
        });

        // Gate untuk Retur Penjualan (TAMBAHKAN INI)
        Gate::define('manage-sales-returns', function (User $user) {
            return in_array($user->jabatan->nama_jabatan, ['Sales', 'Manajer Area']);
        });

        // Sales
        Gate::define('is-sales', function (User $user) {
            return $user->jabatan->nama_jabatan === 'Sales';
        });

        // Gate untuk membuat Penjualan (Sales atau Manajer Area)
        Gate::define('create-penjualan', function (User $user) {
            return in_array($user->jabatan->nama_jabatan, ['Sales', 'Manajer Area']);
        });

        // --- GATES UNTUK AKSI SPESIFIK ---

        // Hak akses untuk membuat PO (hanya PJ Gudang)
        Gate::define('create-po', function (User $user) {
            return $user->jabatan->nama_jabatan === 'PJ Gudang';
        });

        // Hak akses untuk MELAKUKAN APPROVAL (hanya Kepala Gudang dari gudang yg bersangkutan)
        Gate::define('perform-approval', function (User $user, $model) {
            // $model bisa berupa PO, Adjusment, atau Mutasi
            $gudangId = $model->gudang_id ?? $model->gudang_asal_id;
            return $user->jabatan->nama_jabatan === 'Kepala Gudang' && $user->gudang_id === $gudangId;
        });

        // Hak akses untuk approval (hanya Kepala Gudang dari gudang yg bersangkutan)
        Gate::define('approve-po', function (User $user, PurchaseOrder $po) {
            return $user->jabatan->nama_jabatan === 'Kepala Gudang' && $user->gudang_id === $po->gudang_id;
        });
        Gate::define('approve-adjustment', function (User $user, StockAdjustment $adj) {
            return $user->jabatan->nama_jabatan === 'Kepala Gudang' && $user->gudang_id === $adj->gudang_id;
        });
        Gate::define('approve-mutation', function (User $user, StockMutation $mut) {
            return $user->jabatan->nama_jabatan === 'Kepala Gudang' && $user->gudang_id === $mut->gudang_asal_id;
        });

        Gate::define('view-dashboard', function(User $user) {
            return in_array($user->jabatan->nama_jabatan, ['Super Admin', 'Manajer Area', 'Kepala Gudang']);
        });
        Gate::define('manage-sales-targets', function (User $user) {
            return in_array($user->jabatan->nama_jabatan, ['Manajer Area']);
        });

        Gate::define('can-process-quarantine', function ($user) {
            $allowedRoles = ['PJ Gudang', 'Staff Stock Control'];
            return in_array($user->jabatan->nama_jabatan, $allowedRoles);
        });

    }

}
