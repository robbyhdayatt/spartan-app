<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // The sales user
            $table->year('tahun');
            $table->tinyInteger('bulan'); // 1-12
            $table->decimal('target_amount', 15, 2);
            $table->foreignId('created_by')->constrained('users'); // Who set the target
            $table->timestamps();
            $table->unique(['user_id', 'tahun', 'bulan']); // One target per sales per month
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_targets');
    }
}
