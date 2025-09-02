<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMutationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_mutasi', 50)->unique();
            $table->foreignId('part_id')->constrained('parts');
            $table->unsignedBigInteger('gudang_asal_id');
            $table->unsignedBigInteger('gudang_tujuan_id');
            $table->foreignId('rak_asal_id')->constrained('raks');
            $table->unsignedInteger('jumlah');
            $table->enum('status', ['PENDING_APPROVAL', 'APPROVED', 'REJECTED'])->default('PENDING_APPROVAL');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('gudang_asal_id')->references('id')->on('gudangs');
            $table->foreign('gudang_tujuan_id')->references('id')->on('gudangs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_mutations');
    }
}
