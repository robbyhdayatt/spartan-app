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

        $jabatans = Jabatan::pluck('id', 'singkatan');
        $gudangs = Gudang::all();

        // Create Super Admin (already has is_active implicitly)
        User::create([
            'nik' => 'SA-PST-001', 'username' => 'superadmin', 'nama' => 'Super Admin',
            'jabatan_id' => $jabatans['SA'], 'password' => Hash::make('password'), 'is_active' => true,
        ]);

        // Create Manajer Area
        User::create([
            'nik' => 'MA-PST-001', 'username' => 'manajer_ma', 'nama' => 'Manajer Area',
            'jabatan_id' => $jabatans['MA'], 'password' => Hash::make('password'), 'is_active' => true,
        ]);

        // Create users for each warehouse
        foreach ($gudangs as $gudang) {
            $kodeGudang = strtolower($gudang->kode_gudang);

            User::create([
                'nik' => "KG-{$gudang->kode_gudang}-001", 'username' => "kg_{$kodeGudang}", 'nama' => "Kepala Gudang {$gudang->kode_gudang}",
                'jabatan_id' => $jabatans['KG'], 'gudang_id' => $gudang->id, 'password' => Hash::make('password'), 'is_active' => true,
            ]);
            User::create([
                'nik' => "PJG-{$gudang->kode_gudang}-001", 'username' => "pjg_{$kodeGudang}", 'nama' => "PJ Gudang {$gudang->kode_gudang}",
                'jabatan_id' => $jabatans['PJG'], 'gudang_id' => $gudang->id, 'password' => Hash::make('password'), 'is_active' => true,
            ]);
            User::create([
                'nik' => "SR-{$gudang->kode_gudang}-001", 'username' => "sr_{$kodeGudang}", 'nama' => "Staff Receiving {$gudang->kode_gudang}",
                'jabatan_id' => $jabatans['SR'], 'gudang_id' => $gudang->id, 'password' => Hash::make('password'), 'is_active' => true,
            ]);
            User::create([
                'nik' => "QC-{$gudang->kode_gudang}-001", 'username' => "qc_{$kodeGudang}", 'nama' => "Staff QC {$gudang->kode_gudang}",
                'jabatan_id' => $jabatans['QC'], 'gudang_id' => $gudang->id, 'password' => Hash::make('password'), 'is_active' => true,
            ]);
            User::create([
                'nik' => "SP-{$gudang->kode_gudang}-001", 'username' => "sp_{$kodeGudang}", 'nama' => "Staff Putaway {$gudang->kode_gudang}",
                'jabatan_id' => $jabatans['SP'], 'gudang_id' => $gudang->id, 'password' => Hash::make('password'), 'is_active' => true,
            ]);
            User::create([
                'nik' => "SSC-{$gudang->kode_gudang}-001", 'username' => "ssc_{$kodeGudang}", 'nama' => "Stock Control {$gudang->kode_gudang}",
                'jabatan_id' => $jabatans['SSC'], 'gudang_id' => $gudang->id, 'password' => Hash::make('password'), 'is_active' => true,
            ]);
             User::create([
                'nik' => "SLS-{$gudang->kode_gudang}-001", 'username' => "sales_{$kodeGudang}", 'nama' => "Sales {$gudang->kode_gudang}",
                'jabatan_id' => $jabatans['SLS'], 'gudang_id' => $gudang->id, 'password' => Hash::make('password'), 'is_active' => true,
            ]);
        }
    }
}
