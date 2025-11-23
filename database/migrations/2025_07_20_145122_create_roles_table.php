<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_role'); // id_role serial, secara default Laravel menggunakan 'id' sebagai primary key dan auto-increment.
                                  // Kita ganti menjadi id_role sesuai gambar.
            $table->string('nama_role', 255)->unique(); // nama_role character varying(255), diasumsikan unique
            $table->text('deskripsi')->nullable(); // deskripsi text, diasumsikan bisa null

            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
