<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;

class JabatanSeeder extends Seeder
{
    public function run()
    {
        // Hati-hati: Ini akan menghapus data jabatan lama.
        // Pastikan tabel users sudah bersih atau disesuaikan foreign key-nya.
        Jabatan::query()->delete();

        $jabatans = [
            ['nama_jabatan' => 'Super Admin', 'singkatan' => 'SA'],
            ['nama_jabatan' => 'Manajer Area', 'singkatan' => 'MA'],
            ['nama_jabatan' => 'Kepala Gudang', 'singkatan' => 'KG'],
            // Jabatan baru hasil peleburan PJ Gudang, Staff Receiving, QC, Putaway, Stock Control, Sales
            ['nama_jabatan' => 'Admin Gudang', 'singkatan' => 'AG'], 
        ];

        foreach ($jabatans as $jabatan) {
            Jabatan::create($jabatan);
        }
    }
}