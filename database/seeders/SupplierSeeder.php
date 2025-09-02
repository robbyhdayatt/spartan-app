<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        Supplier::query()->delete();
        Supplier::create(['kode_supplier' => 'SUP001', 'nama_supplier' => 'PT. Suku Cadang Sejahtera']);
        Supplier::create(['kode_supplier' => 'SUP002', 'nama_supplier' => 'CV. Mitra Otomotif']);
    }
}
