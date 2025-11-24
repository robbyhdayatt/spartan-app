<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMarketingAndIncentiveTables extends Migration
{
    public function up()
    {
        // Nonaktifkan foreign key check sementara agar urutan drop tidak error
        Schema::disableForeignKeyConstraints();

        // 1. Hapus Tabel Insentif & Target
        Schema::dropIfExists('incentives');
        Schema::dropIfExists('sales_targets');

        // 2. Hapus Tabel Campaign & Relasinya
        Schema::dropIfExists('campaign_konsumen');
        Schema::dropIfExists('customer_discount_category_konsumen');
        Schema::dropIfExists('campaign_part');
        Schema::dropIfExists('campaign_categories');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('campaign_category_konsumen');
        Schema::dropIfExists('campaign_supplier');
        Schema::dropIfExists('inventories');

        // 3. Hapus Kategori Diskon Konsumen
        Schema::dropIfExists('customer_discount_categories');

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        // Kosongkan saja, karena kita tidak berniat mengembalikan fitur ini
    }
}