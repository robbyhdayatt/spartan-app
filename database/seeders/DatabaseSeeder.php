<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // Foundational Data
            JabatanSeeder::class,
            GudangSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            RakSeeder::class,
            QuarantineRakSeeder::class,
            QcRakSeeder::class,
            // Dependent Data
            SupplierSeeder::class,
            UserSeeder::class,
        ]);
    }
}
