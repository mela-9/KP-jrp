<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akd_registers', function (Blueprint $table) {
            $table->id();
            
            // Data Utama
            $table->date('tgl_input');
            $table->string('nama_tertanggung');
            $table->string('no_surat')->nullable();
            $table->string('no_polis')->unique()->nullable();
            $table->date('periode_awal');
            $table->date('periode_akhir');
            
            // Nilai Pertanggungan (TSI) 
            $table->decimal('tsi_ab', 20, 2)->default(0);
            $table->decimal('tsi_c', 20, 2)->default(0);
            $table->decimal('tsi_d', 20, 2)->default(0);
            $table->decimal('tsi_e', 20, 2)->default(0);
            
            // Premi & Informasi Tambahan
            $table->decimal('premi', 20, 2)->default(0);
            $table->string('sumber_bisnis')->nullable();
            $table->date('batas_pembayaran')->nullable();
            $table->string('kontak')->nullable();
            
            // Status Validasi Digital
            $table->string('status_penerbitan')->default('Pending');
            $table->string('status_pengiriman')->default('Pending');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akd_registers');
    }
};

