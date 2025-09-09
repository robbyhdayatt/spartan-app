<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('raks', function (Blueprint $table) {
            // PERUBAHAN: Menggunakan ENUM untuk tipe_rak yang lebih ketat
            $table->enum('tipe_rak', ['PENYIMPANAN', 'KARANTINA'])
                  ->default('PENYIMPANAN')
                  ->after('kode_rak');
        });

        // PERUBAHAN: Update semua rak yang ada dengan kata 'KRN' menjadi tipe 'KARANTINA'
        DB::table('raks')->where('kode_rak', 'like', '%-KRN-%')->update(['tipe_rak' => 'KARANTINA']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('raks', function (Blueprint $table) {
            $table->dropColumn('tipe_rak');
        });
    }
};