<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuratBlocksTable extends Migration
{
    public function up(): void
    {
        Schema::create('surat_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_polis');
            $table->string('kode_pakem');
            $table->string('jalur');
            $table->year('tahun');
            $table->integer('range_start');
            $table->integer('range_end');
            $table->integer('terpakai')->default(0);
            $table->integer('last_number')->default(0); // <-- TAMBAHKAN BARIS INI (Melacak nomor terakhir yang dipakai)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_blocks');
    }
}