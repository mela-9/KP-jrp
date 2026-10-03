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
            $table->string('no_surat')->nullable(); // Wajib ada untuk Gudang Surat
            $table->text('tertanggung'); // Nama Tertanggung & Alamat
            $table->string('no_polis')->unique()->nullable(); 
            $table->date('periode_awal'); 
            $table->date('periode_akhir'); 
            
            // Keuangan & Rate
            $table->decimal('tsi', 20, 2)->default(0); 
            $table->string('rate')->nullable(); 
            $table->decimal('premi', 20, 2)->default(0); 
            
            // Keterangan Tambahan
            $table->date('jatuh_tempo')->nullable(); 
            $table->string('agen')->nullable(); 
            $table->string('pic')->nullable(); 
            $table->string('kontak')->nullable(); 
            
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
        Schema::dropIfExists('par_registers');
    }
};