<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Rak;
use App\Models\Gudang;

class RakSeeder extends Seeder
{
    public function run()
    {
        Rak::query()->delete();
        $gudangs = Gudang::all();
        foreach ($gudangs as $gudang) {
            Rak::create(['gudang_id' => $gudang->id, 'kode_rak' => $gudang->kode_gudang.'-A-01-01', 'nama_rak' => 'Rak A Baris 1']);
            Rak::create(['gudang_id' => $gudang->id, 'kode_rak' => $gudang->kode_gudang.'-A-01-02', 'nama_rak' => 'Rak A Baris 2']);
        }
    }
}
