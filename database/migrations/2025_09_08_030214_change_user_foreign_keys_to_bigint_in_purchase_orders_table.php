<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUserForeignKeysToBigintInPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Mengubah tipe data kolom agar cocok dengan primary key di tabel users
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->unsignedBigInteger('approved_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Kembalikan ke tipe data sebelumnya jika diperlukan (opsional)
            $table->integer('created_by')->nullable()->change();
            $table->integer('approved_by')->nullable()->change();
        });
    }
}
