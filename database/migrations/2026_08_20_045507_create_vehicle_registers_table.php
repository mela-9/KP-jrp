<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_registers', function (Blueprint $table) {
            $table->id();
            
            // Data Utama Polis
            $table->date('tgl_input');
            $table->string('no_polis')->unique();
            $table->string('nama_tertanggung');
            $table->text('alamat_tertanggung');
            
            // Periode
            $table->date('periode_awal');
            $table->date('periode_akhir');
            
            // Detail Objek Kendaraan
            $table->string('no_polisi'); // No. Polisi (plat nomor)
            $table->string('merk_tipe'); // Merek / Tipe / Tahun
            $table->string('no_rangka_mesin'); // No Mesin / No Rangka
            $table->string('penggunaan')->default('PRIBADI');
            
            // Keuangan & Pertanggungan
            $table->decimal('tsi_casco', 20, 2)->default(0); // Harga Pertanggungan / Casco
            $table->decimal('tsi_tjh', 20, 2)->default(0); // Third Party Liability (jika ada)
            $table->decimal('total_premi', 20, 2)->default(0);
            $table->decimal('biaya_admin', 20, 2)->default(0); // Biaya Polis & Materai
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_registers');
    }
};