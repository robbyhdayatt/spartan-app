<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Rak;
use App\Models\Gudang;

class QuarantineRakSeeder extends Seeder
{
    public function run()
    {
        $gudangs = Gudang::all();
        foreach ($gudangs as $gudang) {
            Rak::firstOrCreate(
                ['kode_rak' => $gudang->kode_gudang . '-KRN-RT'],
                [
                    'gudang_id' => $gudang->id,
                    'nama_rak' => 'Rak Karantina Retur',
                    'tipe_rak' => 'KARANTINA', // <-- TAMBAHKAN BARIS INI
                    'is_active' => true
                ]
            );
        }
    }
}