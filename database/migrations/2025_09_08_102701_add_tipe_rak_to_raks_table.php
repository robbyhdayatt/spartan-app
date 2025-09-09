<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Rak;

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
            // Menambahkan kolom baru 'tipe_rak'
            // Pilihan tipe: PENYIMPANAN, KARANTINA_QC, KARANTINA_RETUR
            $table->string('tipe_rak')->default('PENYIMPANAN')->after('kode_rak');
        });

        // Setelah kolom ditambahkan, kita update rak yang sudah ada berdasarkan kode_rak lama
        // Ini agar data lama Anda tetap konsisten
        DB::table('raks')->where('kode_rak', 'like', '%-KRN-QC')->update(['tipe_rak' => 'KARANTINA_QC']);
        DB::table('raks')->where('kode_rak', 'like', '%-KRN-RT')->update(['tipe_rak' => 'KARANTINA_RETUR']);
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
