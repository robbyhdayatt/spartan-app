<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateWorkflowForStockMutationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tambahkan kolom-kolom baru terlebih dahulu
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->foreignId('rak_tujuan_id')->nullable()->constrained('raks')->after('rak_asal_id');
            $table->foreignId('received_by')->nullable()->constrained('users')->after('approved_by');
            $table->timestamp('received_at')->nullable()->after('received_by');
        });

        // 2. Setelah kolom baru ada, baru ubah tipe data kolom 'status'
        DB::statement("ALTER TABLE stock_mutations MODIFY COLUMN status ENUM('PENDING_APPROVAL', 'APPROVED', 'REJECTED', 'IN_TRANSIT', 'COMPLETED') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 1. Kembalikan dulu tipe data kolom 'status'
        DB::statement("ALTER TABLE stock_mutations MODIFY COLUMN status ENUM('PENDING_APPROVAL', 'APPROVED', 'REJECTED') NOT NULL");

        // 2. Baru hapus kolom-kolom yang tadi ditambahkan
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->dropForeign(['rak_tujuan_id']);
            $table->dropForeign(['received_by']);
            $table->dropColumn(['rak_tujuan_id', 'received_by', 'received_at']);
        });
    }
}
