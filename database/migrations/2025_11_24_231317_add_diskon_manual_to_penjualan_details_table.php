<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiskonManualToPenjualanDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('penjualan_details', function (Blueprint $table) {
            // Menambah kolom diskon setelah harga_jual
            $table->decimal('diskon', 15, 2)->default(0)->after('harga_jual');
        });
    }

    public function down()
    {
        Schema::table('penjualan_details', function (Blueprint $table) {
            $table->dropColumn('diskon');
        });
    }
}