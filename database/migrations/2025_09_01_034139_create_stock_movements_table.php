<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts');
            $table->foreignId('gudang_id')->constrained('gudangs');
            $table->string('tipe_gerakan'); // e.g., PENJUALAN, PEMBELIAN, MUTASI_MASUK, etc.
            $table->integer('jumlah'); // Can be positive (in) or negative (out)
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->string('referensi')->nullable(); // e.g., Invoice Number, PO Number
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
}
