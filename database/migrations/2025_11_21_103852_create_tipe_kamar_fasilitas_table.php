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
        // Tambahkan tabel pivot untuk fasilitas default per tipe kamar
        Schema::create('tipe_kamar_fasilitas', function (Blueprint $table) {
            // Gunakan nama kolom sesuai dengan foreign key di model TipeKamar dan Fasilitas
            $table->foreignId('tipe_kamar_id')->constrained('tipe_kamars', 'id_tipe_kamar')->onDelete('cascade');
            $table->foreignId('fasilitas_id')->constrained('fasilitas', 'id_fasilitas')->onDelete('cascade');

            $table->primary(['tipe_kamar_id', 'fasilitas_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipe_kamar_fasilitas');
    }
};
