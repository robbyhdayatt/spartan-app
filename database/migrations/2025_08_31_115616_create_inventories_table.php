<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts');
            $table->foreignId('gudang_id')->constrained('gudangs');
            $table->foreignId('rak_id')->constrained('raks');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            // Each part can only exist once on a specific shelf
            $table->unique(['part_id', 'rak_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventories');
    }
}
