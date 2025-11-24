<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Gudang;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::query()->delete();

        // Ambil ID jabatan berdasarkan singkatan
        // Pastikan JabatanSeeder sudah dijalankan dan memiliki singkatan 'AG' (Admin Gudang)
        $jabatans = Jabatan::pluck('id', 'singkatan');
        $gudangs = Gudang::all();

        // 1. Create Super Admin
        User::create([
            'nik' => 'SA-PST-001', 
            'username' => 'superadmin', 
            'nama' => 'Super Admin',
            'jabatan_id' => $jabatans['SA'], 
            'password' => Hash::make('password'), 
            'is_active' => true,
        ]);

        // 2. Create Manajer Area
        User::create([
            'nik' => 'MA-PST-001', 
            'username' => 'manajer_ma', 
            'nama' => 'Manajer Area',
            'jabatan_id' => $jabatans['MA'], 
            'password' => Hash::make('password'), 
            'is_active' => true,
        ]);

        // 3. Loop setiap gudang untuk membuat Kepala Gudang & Admin Gudang
        foreach ($gudangs as $gudang) {
            $kodeGudang = strtolower($gudang->kode_gudang);

            // Kepala Gudang (KG)
            User::create([
                'nik' => "KG-{$gudang->kode_gudang}-001", 
                'username' => "kg_{$kodeGudang}", 
                'nama' => "Kepala Gudang {$gudang->kode_gudang}",
                'jabatan_id' => $jabatans['KG'], 
                'gudang_id' => $gudang->id, 
                'password' => Hash::make('password'), 
                'is_active' => true,
            ]);

            // Admin Gudang (AG) - Menggantikan semua peran operasional (PJ, Receiving, QC, Putaway, Sales)
            User::create([
                'nik' => "AG-{$gudang->kode_gudang}-001", 
                'username' => "ag_{$kodeGudang}", 
                'nama' => "Admin Gudang {$gudang->kode_gudang}",
                'jabatan_id' => $jabatans['AG'], 
                'gudang_id' => $gudang->id, 
                'password' => Hash::make('password'), 
                'is_active' => true,
            ]);
        }
    }
}