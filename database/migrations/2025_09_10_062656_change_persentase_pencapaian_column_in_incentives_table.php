<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePersentasePencapaianColumnInIncentivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incentives', function (Blueprint $table) {
            // Mengubah kolom menjadi decimal dengan total 8 digit, 2 di antaranya di belakang koma
            $table->decimal('persentase_pencapaian', 8, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incentives', function (Blueprint $table) {
            // Mengembalikan ke tipe semula jika diperlukan (gantilah jika tipe awalnya berbeda)
            $table->decimal('persentase_pencapaian', 5, 2)->change();
        });
    }
}
