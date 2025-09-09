<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubtotalAndPajakToSalesReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            // Menambahkan kolom subtotal dan pajak setelah kolom total_retur
            $table->decimal('subtotal', 15, 2)->default(0)->after('total_retur');
            $table->decimal('pajak', 15, 2)->default(0)->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'pajak']);
        });
    }
}