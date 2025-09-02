<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Kita hapus kolom 'name' dan 'email' bawaan jika tidak diperlukan
            // Atau kita bisa biarkan saja. Di sini kita modifikasi.
            $table->renameColumn('name', 'nama');
            $table->string('email')->nullable()->change(); // Buat email jadi opsional

            // Tambahkan kolom baru setelah kolom 'id'
            $table->string('nik', 50)->unique()->after('id');
            $table->string('username', 100)->unique()->after('nik');

            // Tambahkan foreign key
            $table->foreignId('gudang_id')->nullable()->after('password')->constrained('gudangs');
            $table->foreignId('jabatan_id')->after('gudang_id')->constrained('jabatans');

            // Tambahkan kolom status
            $table->boolean('is_active')->default(true)->after('jabatan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
