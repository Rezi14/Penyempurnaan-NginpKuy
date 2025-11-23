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
        // 1. Tabel Master Fasilitas
        Schema::create('fasilitas', function (Blueprint $table) {
            $table->id('id_fasilitas'); // Primary Key
            $table->string('nama_fasilitas');
            $table->text('deskripsi')->nullable();
            $table->string('icon')->nullable(); // Opsional: untuk class icon fontawesome/path gambar
            $table->decimal('biaya_tambahan', 12, 2)->default(0); // Harga jika dipilih sebagai tambahan
            $table->timestamps();
        });

        // 2. Pivot: Fasilitas Dasar (Tipe Kamar <-> Fasilitas)
        // Fasilitas ini melekat pada tipe kamar (gratis/include harga kamar)
        Schema::create('tipe_kamar_fasilitas', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke tipe_kamars
            $table->unsignedBigInteger('id_tipe_kamar');
            $table->foreign('id_tipe_kamar')->references('id_tipe_kamar')->on('tipe_kamars')->onDelete('cascade');

            // Foreign Key ke fasilitas
            $table->unsignedBigInteger('id_fasilitas');
            $table->foreign('id_fasilitas')->references('id_fasilitas')->on('fasilitas')->onDelete('cascade');

            $table->timestamps();
        });

        // 3. Pivot: Fasilitas Tambahan (Pemesanan <-> Fasilitas)
        // Fasilitas ini dipilih user saat booking (berbayar)
        Schema::create('pemesanan_fasilitas', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke pemesanans
            $table->unsignedBigInteger('id_pemesanan'); // Sesuaikan dengan kolom id di tabel pemesanans
            $table->foreign('id_pemesanan')->references('id_pemesanan')->on('pemesanans')->onDelete('cascade');

            // Foreign Key ke fasilitas
            $table->unsignedBigInteger('id_fasilitas');
            $table->foreign('id_fasilitas')->references('id_fasilitas')->on('fasilitas')->onDelete('cascade');

            $table->integer('jumlah')->default(1); // Opsional: jika ingin pesan 2 Extra Bed
            $table->decimal('total_harga_fasilitas', 12, 2)->default(0); // Menyimpan harga saat transaksi terjadi (untuk history)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_fasilitas');
        Schema::dropIfExists('tipe_kamar_fasilitas');
        Schema::dropIfExists('fasilitas');
    }
};
