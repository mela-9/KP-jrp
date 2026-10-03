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
            
            // Data Utama PL
            $table->date('tgl_input');
            $table->string('no_surat')->nullable(); // Wajib untuk gudang surat
            $table->string('cob_toc'); // Jenis COB / TOC
            $table->text('tertanggung'); // Tertanggung & Alamat
            $table->string('no_polis')->unique()->nullable(); 
            $table->date('periode_awal'); 
            $table->date('periode_akhir'); 
            
            // Keuangan
            $table->decimal('tsi', 20, 2)->default(0); 
            $table->string('share')->default('100%'); 
            $table->decimal('premi', 20, 2)->default(0); 
            $table->date('jatuh_tempo')->nullable(); 
            $table->string('agen')->nullable(); 
            $table->string('pic')->nullable(); 
            
            // ==========================================
            // KOLOM WORKFLOW & E-REGISTER WAJIB
            // ==========================================
            $table->json('nomor_surat_array')->nullable();
            $table->string('scan_polis')->nullable();
            $table->string('status_approval')->default('Pending Kepala Staff');
            $table->string('status_serah_terima')->default('Belum Diserahkan');
            $table->unsignedBigInteger('created_by')->nullable();
            
            // Log & Tanda Terima Workflow
            $table->timestamp('paraf_timestamp')->nullable();
            $table->string('paraf_oleh')->nullable();
            $table->string('bukti_terima')->nullable();
            $table->timestamp('tanggal_terima')->nullable();

            $table->string('kondisi_surat')->default('Normal'); // Pilihan: Normal, Rusak, Parsial
            $table->text('keterangan_audit')->nullable();       // Catatan khusus jika ada salah cetak/rusak
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_liability_registers');
    }
};