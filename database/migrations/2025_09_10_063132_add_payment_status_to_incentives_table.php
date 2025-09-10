<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentStatusToIncentivesTable extends Migration
{
    public function up()
    {
        Schema::table('incentives', function (Blueprint $table) {
            $table->string('status')->default('UNPAID')->after('jumlah_insentif');
            $table->timestamp('paid_at')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('incentives', function (Blueprint $table) {
            $table->dropColumn(['status', 'paid_at']);
        });
    }
}

