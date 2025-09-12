<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRakIdToStockAdjustmentsTable extends Migration
{
    public function up()
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            // Menambahkan kolom rak_id setelah gudang_id
            $table->foreignId('rak_id')->nullable()->after('gudang_id')->constrained('raks');
        });
    }

    public function down()
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropForeign(['rak_id']);
            $table->dropColumn('rak_id');
        });
    }
}
