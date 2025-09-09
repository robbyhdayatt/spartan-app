<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Rak;
use App\Models\Gudang;

class QcRakSeeder extends Seeder
{
    public function run()
    {
        $gudangs = Gudang::all();
        foreach ($gudangs as $gudang) {
            Rak::firstOrCreate(
                ['kode_rak' => $gudang->kode_gudang . '-KRN-QC'],
                [
                    'gudang_id' => $gudang->id,
                    'nama_rak' => 'Rak Karantina QC',
                    'tipe_rak' => 'KARANTINA', // <-- TAMBAHKAN BARIS INI
                    'is_active' => true
                ]
            );
        }
    }
}