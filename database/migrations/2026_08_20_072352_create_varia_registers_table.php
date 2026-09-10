<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('varia_registers', function (Blueprint $table) {
            $table->id();
            
            $table->date('tgl_input');
            $table->string('cob_toc'); // Jenis COB / TOC (misal: Fidelity Guarantee, Cash in Transit, dll.)
            $table->text('tertanggung'); // Nama Tertanggung & Alamat[cite: 4]
            $table->string('no_polis')->unique(); // No. CI / No. Polis / Cert No.[cite: 4]
            $table->date('periode_awal'); // Periode Awal[cite: 4]
            $table->date('periode_akhir'); // Periode Akhir[cite: 4]
            $table->decimal('tsi', 20, 2)->default(0); // TSI[cite: 4]
            $table->string('share')->default('100%'); // Share (%)[cite: 4]
            $table->decimal('premi', 20, 2)->default(0); // Jumlah Premi[cite: 4]
            $table->date('jatuh_tempo')->nullable(); // Tgl Jatuh Tempo Pembayaran[cite: 4]
            $table->string('agen')->nullable(); // Agen / Broker / Direct[cite: 4]
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('varia_registers');
    }
};