<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surety_bond_registers', function (Blueprint $table) {
            $table->id();
            
            $table->date('tgl_input');
            $table->string('cob_toc'); // Jenis Surety Bond (Contoh: Jaminan Penawaran, Jaminan Pelaksanaan, Jaminan Uang Muka)
            $table->text('tertanggung'); // Nama Tertanggung & Alamat[cite: 7]
            $table->string('no_polis')->unique(); // No. CI / No. Polis / Cert No.[cite: 7]
            $table->date('periode_awal'); // Periode Mulai[cite: 7]
            $table->date('periode_akhir'); // Periode Selesai[cite: 7]
            $table->decimal('tsi', 20, 2)->default(0); // TSI[cite: 7]
            $table->string('share')->default('100%'); // Share (%)[cite: 7]
            $table->decimal('premi', 20, 2)->default(0); // Jumlah Premi[cite: 7]
            $table->date('jatuh_tempo')->nullable(); // Tgl. Jatuh Tempo Pembayaran[cite: 7]
            $table->string('agen')->nullable(); // Agen / Broker / Direct[cite: 7]
            $table->string('pic')->nullable(); // PIC Branch Office[cite: 7]
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surety_bond_registers');
    }
};