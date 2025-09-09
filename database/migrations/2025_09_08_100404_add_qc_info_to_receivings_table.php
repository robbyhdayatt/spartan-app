<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receivings', function (Blueprint $table) {
            // PERBAIKAN: Tambahkan semua kolom yang hilang dalam satu migrasi

            // Kolom untuk mencatat siapa yang membuat dokumen
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users');

            // Kolom untuk mencatat siapa yang melakukan QC
            $table->foreignId('qc_by')->nullable()->after('created_by')->constrained('users');

            // Kolom untuk mencatat kapan QC dilakukan
            $table->timestamp('qc_at')->nullable()->after('qc_by');
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
            // Hapus foreign key constraints terlebih dahulu
            $table->dropForeign(['created_by']);
            $table->dropForeign(['qc_by']);

            // Hapus kolom-kolomnya
            $table->dropColumn(['created_by', 'qc_by', 'qc_at']);
        });
    }
};
