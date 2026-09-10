<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('akd_registers', function (Blueprint $table) {
        $table->string('status_approval')->default('Pending Review Staff')->change();
        $table->string('status_serah_terima')->default('Belum Diserahkan')->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akd_registers', function (Blueprint $table) {
            //
        });
    }
};
