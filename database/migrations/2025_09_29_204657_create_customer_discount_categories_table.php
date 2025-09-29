<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerDiscountCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_discount_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori');
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('customer_discount_category_konsumen', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_discount_category_id');
            $table->unsignedBigInteger('konsumen_id');

            // Menetapkan foreign keys dengan nama pendek
            $table->foreign('customer_discount_category_id', 'fk_cdc_category_id')
                  ->references('id')->on('customer_discount_categories')
                  ->onDelete('cascade');

            $table->foreign('konsumen_id', 'fk_cdc_konsumen_id')
                  ->references('id')->on('konsumens')
                  ->onDelete('cascade');

            // Menetapkan primary key komposit dengan nama pendek
            $table->primary(['customer_discount_category_id', 'konsumen_id'], 'pk_cdc_konsumen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_discount_category_konsumen');
        Schema::dropIfExists('customer_discount_categories');
    }
}