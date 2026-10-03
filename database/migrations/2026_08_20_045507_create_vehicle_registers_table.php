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
            $table->string('no_surat')->nullable(); // Tambahan untuk Gudang Surat
            $table->string('no_polis')->unique()->nullable();
            $table->string('nama_tertanggung');
            $table->text('alamat_tertanggung')->nullable();
            
            // Periode
            $table->date('periode_awal');
            $table->date('periode_akhir');
            
            // Detail Objek Kendaraan
            $table->string('no_polisi')->nullable();
            $table->string('merk_tipe')->nullable();
            $table->string('no_rangka_mesin')->nullable();
            $table->string('penggunaan')->default('PRIBADI');
            
            // Keuangan & Pertanggungan
            $table->decimal('tsi_casco', 20, 2)->default(0); 
            $table->decimal('tsi_tjh', 20, 2)->default(0); 
            $table->decimal('total_premi', 20, 2)->default(0); // Bedanya dengan AKD, ini pakai total_premi
            $table->decimal('biaya_admin', 20, 2)->default(0); 
            $table->string('sumber_bisnis')->nullable();
            
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
        Schema::dropIfExists('vehicle_registers');
    }
};