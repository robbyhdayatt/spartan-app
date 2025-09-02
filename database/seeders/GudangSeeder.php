<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gudang;

class GudangSeeder extends Seeder
{
    public function run()
    {
        Gudang::query()->delete();
        Gudang::create(['kode_gudang' => 'BDL', 'nama_gudang' => 'Gudang Bandar Lampung']);
        Gudang::create(['kode_gudang' => 'PWT', 'nama_gudang' => 'Gudang Poncowati']);
        Gudang::create(['kode_gudang' => 'CKR', 'nama_gudang' => 'Gudang Cikarang']);
    }
}
