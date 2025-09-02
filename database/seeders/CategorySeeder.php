<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        Category::query()->delete();
        Category::create(['nama_kategori' => 'Oli Mesin']);
        Category::create(['nama_kategori' => 'Busi']);
        Category::create(['nama_kategori' => 'Shock Absorber']);
        Category::create(['nama_kategori' => 'Aki']);
        Category::create(['nama_kategori' => 'Kampas Rem']);
    }
}
