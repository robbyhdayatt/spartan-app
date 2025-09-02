<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;

class JabatanSeeder extends Seeder
{
    public function run()
    {
        Jabatan::query()->delete();
        $jabatans = [
            ['nama_jabatan' => 'Super Admin', 'singkatan' => 'SA'],
            ['nama_jabatan' => 'Manajer Area', 'singkatan' => 'MA'],
            ['nama_jabatan' => 'Kepala Gudang', 'singkatan' => 'KG'],
            ['nama_jabatan' => 'PJ Gudang', 'singkatan' => 'PJG'],
            ['nama_jabatan' => 'Staff Receiving', 'singkatan' => 'SR'],
            ['nama_jabatan' => 'Staff QC', 'singkatan' => 'QC'],
            ['nama_jabatan' => 'Staff Putaway', 'singkatan' => 'SP'],
            ['nama_jabatan' => 'Staff Stock Control', 'singkatan' => 'SSC'],
            ['nama_jabatan' => 'Sales', 'singkatan' => 'SLS'],
        ];
        foreach ($jabatans as $jabatan) {
            Jabatan::create($jabatan);
        }
    }
}
