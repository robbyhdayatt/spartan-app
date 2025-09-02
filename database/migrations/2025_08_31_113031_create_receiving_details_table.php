<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceivingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receiving_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiving_id')->constrained('receivings')->onDelete('cascade');
            $table->foreignId('part_id')->constrained('parts');
            $table->integer('qty_terima');
            $table->integer('qty_lolos_qc')->default(0);
            $table->integer('qty_gagal_qc')->default(0);
            $table->text('catatan_qc')->nullable();
            $table->integer('qty_disimpan')->default(0);
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
        Schema::dropIfExists('receiving_details');
    }
}
