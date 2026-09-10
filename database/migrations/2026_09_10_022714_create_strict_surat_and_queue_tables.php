<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Blok Nomor Surat (4 Kolom Utama + Pemetaan Jenis Polis + Flag Other Garis Merah)
        Schema::create('surat_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('prefix'); // Kolom 1: Prefix / Kode Depan (misal: JRP)
            $table->string('seri_produk'); // Kolom 2: Seri / Kelompok (misal: 1125 / 1126)
            $table->integer('nomor_awal'); // Kolom 3: Nomor Urut Awal (misal: 1)
            $table->integer('nomor_akhir'); // Kolom 4: Nomor Urut Akhir (misal: 3000)
            $table->json('allowed_polis'); // Array jenis polis yang diizinkan (Kendaraan, PL, Surety, Varia, AKD, PAR, atau Other)
            $table->boolean('is_other_flag')->default(false); // Penanda khusus untuk opsi Other (garis bawah merah)
            $table->timestamps();
        });

        // Tabel Detail Nomor Surat Satuan (Generate otomatis dari blok di atas untuk pelacakan per halaman)
        Schema::create('surat_stocks_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_block_id')->constrained('surat_blocks')->onDelete('cascade');
            $table->string('nomor_surat_lengkap')->unique(); // Contoh: JRP-1125-0001
            $table->enum('status', ['AVAILABLE', 'USED', 'DAMAGED'])->default('AVAILABLE');
            $table->string('keterangan_rusak')->nullable();
            $table->timestamps();
        });

        // 2. Tabel List Antrian Polis (Staging Area / Lembar Kerja sebelum masuk Buku Besar E-Regist)
        Schema::create('polis_queues', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_polis'); // AKD, PAR, Kendaraan, Varia, PL, Surety Bond
            $table->string('nama_tertanggung'); // Nama PT / Klien
            $table->string('no_polis');
            $table->date('tgl_input');
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->decimal('premi', 15, 2)->default(0);
            $table->integer('jumlah_halaman'); // Otomatis menentukan berapa nomor surat yang ditarik
            $table->enum('status_antrian', ['MENUNGGU_NOMOR', 'SIAP_DISETUJUI', 'MASUK_BUKU_BESAR'])->default('MENUNGGU_NOMOR');
            $table->json('allocated_surat_numbers')->nullable(); // Nomor surat yang ditarik sistem
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polis_queues');
        Schema::dropIfExists('surat_stocks_detail');
        Schema::dropIfExists('surat_blocks');
    }
};