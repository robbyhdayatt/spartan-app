<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveKonsumenRelationAndAddManualName extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        // 1. Ubah tabel penjualans
        Schema::table('penjualans', function (Blueprint $table) {
            // Hapus foreign key lama (pastikan nama foreign key benar, biasanya penjualans_konsumen_id_foreign)
            $table->dropForeign(['konsumen_id']); 
            
            // Hapus kolom ID
            $table->dropColumn('konsumen_id');

            // Tambah kolom nama manual
            $table->string('nama_konsumen')->after('gudang_id');
        });

        // 2. Hapus tabel konsumens dan kategori diskon (jika belum terhapus)
        Schema::dropIfExists('konsumens');
        Schema::dropIfExists('customer_discount_categories');

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        // Tidak perlu rollback karena ini penghapusan fitur permanen
    }
}