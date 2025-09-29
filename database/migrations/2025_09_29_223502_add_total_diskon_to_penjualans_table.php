<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalDiskonToPenjualansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            // Tambahkan kolom total_diskon setelah kolom subtotal
            $table->decimal('total_diskon', 15, 2)->default(0.00)->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            // Hapus kolom jika migrasi di-rollback
            $table->dropColumn('total_diskon');
        });
    }
}