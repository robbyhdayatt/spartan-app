<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Konsumen;

class KonsumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Konsumen::query()->delete();
        Konsumen::create(['kode_konsumen' => 'CUST001', 'nama_konsumen' => 'Bengkel Lancar Jaya', 'tipe_konsumen' => 'Bengkel']);
        Konsumen::create(['kode_konsumen' => 'CUST002', 'nama_konsumen' => 'Andi (Retail)', 'tipe_konsumen' => 'Retail']);
    }
}
