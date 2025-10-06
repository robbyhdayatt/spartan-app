<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeRakAsalIdNullableInStockMutationsTable extends Migration
{
    public function up()
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            // Ubah kolom agar bisa null
            $table->unsignedBigInteger('rak_asal_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            // Kembalikan seperti semula jika di-rollback
            $table->unsignedBigInteger('rak_asal_id')->nullable(false)->change();
        });
    }
}
