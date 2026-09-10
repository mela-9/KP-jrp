<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_liability_registers', function (Blueprint $table) {
            $table->id();
            
            $table->date('tgl_input');
            $table->string('cob_toc'); // Jenis COB / TOC (Contoh: Public Liability, ATJP, Asuransi Pelayanan Umum)
            $table->text('tertanggung'); // Tertanggung & Alamat
            $table->string('no_polis')->unique(); // No. CI / No. Polis / Cert No.[cite: 6]
            $table->date('periode_awal'); // Periode Mulai[cite: 6]
            $table->date('periode_akhir'); // Periode Selesai[cite: 6]
            $table->decimal('tsi', 20, 2)->default(0); // TSI[cite: 6]
            $table->string('share')->default('100%'); // Share (%)[cite: 6]
            $table->decimal('premi', 20, 2)->default(0); // Jumlah Premi[cite: 6]
            $table->date('jatuh_tempo')->nullable(); // Tgl. Jatuh Tempo Pembayaran[cite: 6]
            $table->string('agen')->nullable(); // Agen / Broker / Direct[cite: 6]
            $table->string('pic')->nullable(); // PIC Branch Office[cite: 6]
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_liability_registers');
    }
};