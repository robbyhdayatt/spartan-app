<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gudang_id')->constrained('gudangs');
            $table->string('kode_rak', 20);
            $table->string('nama_rak', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['gudang_id', 'kode_rak']); // Kode rak harus unik per gudang
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('raks');
    }
}
