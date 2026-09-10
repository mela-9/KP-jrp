<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akd_registers', function (Blueprint $table) {
            $table->string('scan_polis')->nullable(); // File scan polis dari staff
            $table->string('status_approval')->default('Pending Kepala Staff'); // Pending Kepala Staff, Diparaf, Disetujui BM
            $table->timestamp('paraf_timestamp')->nullable(); // Waktu digital paraf kepala staff
            $table->string('paraf_oleh')->nullable(); // Nama/ID Kepala Staff yang memaraf
            $table->string('status_serah_terima')->default('Belum Diserahkan'); // Belum Diserahkan, Dalam Pengantaran, Diterima Tertanggung
        });
    }

    public function down(): void
    {
        Schema::table('akd_registers', function (Blueprint $table) {
            $table->dropColumn(['scan_polis', 'status_approval', 'paraf_timestamp', 'paraf_oleh', 'status_serah_terima']);
        });
    }
};