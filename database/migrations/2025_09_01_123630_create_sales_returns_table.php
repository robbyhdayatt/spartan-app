<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_retur_jual', 50)->unique();
            $table->foreignId('penjualan_id')->constrained('penjualans');
            $table->foreignId('konsumen_id')->constrained('konsumens');
            $table->foreignId('gudang_id')->constrained('gudangs');
            $table->date('tanggal_retur');
            $table->decimal('total_retur', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users');
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
        Schema::dropIfExists('sales_returns');
    }
}
