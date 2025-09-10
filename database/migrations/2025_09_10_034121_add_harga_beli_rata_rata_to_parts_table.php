<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHargaBeliRataRataToPartsTable extends Migration
{
    public function up()
    {
        Schema::table('parts', function (Blueprint $table) {
            // Tambahkan kolom baru setelah harga_beli_default
            $table->decimal('harga_beli_rata_rata', 15, 2)->default(0)->after('harga_beli_default');
        });
    }

    public function down()
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('harga_beli_rata_rata');
        });
    }
}
