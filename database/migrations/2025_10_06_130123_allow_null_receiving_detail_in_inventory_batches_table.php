<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AllowNullReceivingDetailInInventoryBatchesTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('receiving_detail_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('receiving_detail_id')->nullable(false)->change();
        });
    }
}
