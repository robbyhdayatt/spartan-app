<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPutawayInfoToReceivingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receivings', function (Blueprint $table) {
            $table->foreignId('putaway_by')->nullable()->constrained('users')->after('qc_at');
            $table->timestamp('putaway_at')->nullable()->after('putaway_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receivings', function (Blueprint $table) {
            $table->dropForeign(['putaway_by']);
            $table->dropColumn(['putaway_by', 'putaway_at']);
        });
    }
}
