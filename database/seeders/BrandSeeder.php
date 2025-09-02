<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run()
    {
        Brand::query()->delete();
        Brand::create(['nama_brand' => 'Yamaha Genuine Part']);
        Brand::create(['nama_brand' => 'Yamalube']);
        Brand::create(['nama_brand' => 'Aspira']);
        Brand::create(['nama_brand' => 'Federal']);
    }
}
