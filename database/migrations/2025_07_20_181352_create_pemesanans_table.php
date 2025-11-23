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
        Schema::create('pemesanans', function (Blueprint $table) {
            $table->id('id_pemesanan'); // Primary key untuk tabel pemesanan

            // Foreign key ke tabel users (siapa yang memesan)
            // Ini akan mereferensikan 'id' di tabel 'users' (sesuai default Laravel)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Foreign key ke tabel kamars (kamar mana yang dipesan)
            // KOREKSI: Mereferensikan 'id_kamar' di tabel 'kamars'
            $table->foreignId('kamar_id')->constrained('kamars', 'id_kamar')->onDelete('cascade');
            //                                                      ^^^^^^^^^ PENTING: KOLOM INI

            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('jumlah_tamu');
            $table->decimal('total_harga', 12, 2);
            $table->string('status_pemesanan', 50)->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanans');
    }
};