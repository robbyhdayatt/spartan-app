<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseCampaignFieldsToCampaignsTable extends Migration
{

    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('tipe')->default('PENJUALAN')->after('nama_campaign'); // PENJUALAN or PEMBELIAN
            $table->renameColumn('harga_jual_promo', 'harga_promo');
        });
    }

    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('tipe');
            $table->renameColumn('harga_promo', 'harga_jual_promo');
        });
    }
}
