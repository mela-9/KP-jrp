<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Master Stok Nomor Surat
        Schema::create('surat_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_produk', 10); // Contoh: 1101 (AKD), 1202 (PAR), dll
            $table->string('nomor_surat')->unique(); // Contoh: JRP-1101/2026/0019 atau nomor manual
            $table->enum('status', ['AVAILABLE', 'USED', 'DAMAGED'])->default('AVAILABLE');
            $table->string('keterangan')->nullable(); // Keterangan jika rusak/cacat cetak
            $table->timestamps();
        });

        // Tabel Relasi Polis ke Nomor Surat (Mendukung 1 Polis punya banyak No Surat / Halaman)
        Schema::create('polis_surat_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('modul_tipe'); // akd, par, vehicle, varia, pl, surety
            $table->unsignedBigInteger('polis_id'); // ID data polis terkait
            $table->unsignedBigInteger('surat_stock_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polis_surat_mappings');
        Schema::dropIfExists('surat_stocks');
    }
};