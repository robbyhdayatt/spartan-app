<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRakIdToStockMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Tambahkan kolom rak_id setelah gudang_id, buat nullable agar data lama tidak error
            $table->foreignId('rak_id')->nullable()->constrained('raks')->after('gudang_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['rak_id']);
            $table->dropColumn('rak_id');
        });
    }
}
