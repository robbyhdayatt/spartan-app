<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RestructureCampaignsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Modifikasi tabel 'campaigns' yang sudah ada
        Schema::table('campaigns', function (Blueprint $table) {
            // Hapus kolom yang tidak lagi relevan
            $table->dropForeign(['part_id']);
            $table->dropColumn('part_id');
            $table->dropColumn('harga_promo');

            // Tambah kolom baru untuk diskon persentase
            $table->decimal('discount_percentage', 5, 2)->default(0.00)->after('tipe');
        });

        // 2. Buat tabel baru untuk kategori campaign penjualan
        Schema::create('campaign_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->string('nama_kategori');
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->timestamps();
        });

        // 3. Buat tabel pivot (many-to-many)
        Schema::create('campaign_part', function (Blueprint $table) {
            $table->primary(['campaign_id', 'part_id']); // Composite primary key
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('part_id')->constrained('parts')->onDelete('cascade');
        });

        Schema::create('campaign_supplier', function (Blueprint $table) {
            $table->primary(['campaign_id', 'supplier_id']);
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
        });

        Schema::create('campaign_category_konsumen', function (Blueprint $table) {
            $table->primary(['campaign_category_id', 'konsumen_id'], 'campaign_category_konsumen_primary');
            $table->foreignId('campaign_category_id')->constrained('campaign_categories')->onDelete('cascade');
            $table->foreignId('konsumen_id')->constrained('konsumens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Hapus tabel pivot terlebih dahulu (urutan terbalik dari 'up')
        Schema::dropIfExists('campaign_category_konsumen');
        Schema::dropIfExists('campaign_supplier');
        Schema::dropIfExists('campaign_part');

        // Hapus tabel kategori
        Schema::dropIfExists('campaign_categories');

        // Kembalikan struktur tabel 'campaigns' ke kondisi semula
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('discount_percentage');

            // Tambahkan kembali kolom yang dihapus
            $table->foreignId('part_id')->constrained('parts')->onDelete('cascade');
            $table->decimal('harga_promo', 15, 2);
        });
    }
}
