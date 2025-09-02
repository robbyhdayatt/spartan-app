<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('kode_part', 50)->unique();
            $table->string('nama_part');
            $table->string('satuan', 20)->comment('Contoh: Pcs, Set, Liter');
            $table->unsignedInteger('stok_minimum')->default(0);
            $table->decimal('harga_beli_default', 15, 2)->default(0);
            $table->decimal('harga_jual_default', 15, 2)->default(0);
            $table->string('foto_part')->nullable();

            $table->foreignId('brand_id')->constrained('brands');
            $table->foreignId('category_id')->constrained('categories');

            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('parts');
    }
}
