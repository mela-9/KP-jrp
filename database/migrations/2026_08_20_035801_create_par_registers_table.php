<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('par_registers', function (Blueprint $table) {
            $table->id();
            
            // Data Utama PAR
            $table->date('tgl_input');
            $table->text('tertanggung'); // Nama Tertanggung & Alamat
            $table->string('no_polis')->unique()->nullable(); // No. CI / Polis / Cert No
            $table->date('periode_awal'); // Periode Mulai[cite: 1]
            $table->date('periode_akhir'); // Periode Selesai[cite: 1]
            
            // Keuangan & Rate
            $table->decimal('tsi', 20, 2)->default(0); // Total Sum Insured[cite: 1]
            $table->string('rate')->nullable(); // Rate / Are[cite: 1]
            $table->decimal('premi', 20, 2)->default(0); // Jumlah Premi[cite: 1]
            
            // Keterangan Tambahan
            $table->date('jatuh_tempo')->nullable(); // Tgl Jatuh Tempo Pembayaran[cite: 1]
            $table->string('agen')->nullable(); // Agen / Broker / Direct / Co-Ins[cite: 1]
            $table->string('pic')->nullable(); // PIC Branch Office[cite: 1]
            $table->string('kontak')->nullable(); // Telp / HP / Email[cite: 1]
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('par_registers');
    }
};