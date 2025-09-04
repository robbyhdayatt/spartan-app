<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncentivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incentives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // The sales user
            $table->foreignId('sales_target_id')->constrained('sales_targets');
            $table->decimal('total_penjualan', 15, 2);
            $table->decimal('persentase_pencapaian', 5, 2);
            $table->decimal('jumlah_insentif', 15, 2);
            $table->date('periode'); // e.g., first day of the month
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
        Schema::dropIfExists('incentives');
    }
}
