<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts');
            $table->foreignId('gudang_id')->constrained('gudangs');
            $table->enum('tipe', ['TAMBAH', 'KURANG']);
            $table->unsignedInteger('jumlah');
            $table->text('alasan');
            $table->enum('status', ['PENDING_APPROVAL', 'APPROVED', 'REJECTED'])->default('PENDING_APPROVAL');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
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
        Schema::dropIfExists('stock_adjustments');
    }
}
