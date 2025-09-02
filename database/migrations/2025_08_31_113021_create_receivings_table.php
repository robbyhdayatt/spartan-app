<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceivingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receivings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders');
            $table->foreignId('gudang_id')->constrained('gudangs');
            $table->string('nomor_penerimaan', 50)->unique();
            $table->date('tanggal_terima');
            $table->enum('status', ['PENDING_QC', 'PENDING_PUTAWAY', 'COMPLETED'])->default('PENDING_QC');
            $table->text('catatan')->nullable();
            $table->foreignId('received_by')->constrained('users');
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
        Schema::dropIfExists('receivings');
    }
}
