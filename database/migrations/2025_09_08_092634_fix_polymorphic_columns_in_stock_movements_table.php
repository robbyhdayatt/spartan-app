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
        Schema::table('stock_movements', function (Blueprint $table) {
            // Hapus kolom 'referensi' yang tipenya salah (varchar) jika ada
            if (Schema::hasColumn('stock_movements', 'referensi')) {
                $table->dropColumn('referensi');
            }
            // Hapus juga kolom tipe_gerakan karena akan digantikan oleh referensi_type
            if (Schema::hasColumn('stock_movements', 'tipe_gerakan')) {
                 $table->dropColumn('tipe_gerakan');
            }

            // Tambahkan kolom polimorfik yang benar setelah kolom 'quantity'
            $table->morphs('referensi'); // Ini akan membuat referensi_id dan referensi_type
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Jika ingin bisa di-rollback, definisikan kebalikannya
            $table->dropMorphs('referensi');
            $table->string('referensi')->nullable();
            $table->string('tipe_gerakan');
        });
    }
};
