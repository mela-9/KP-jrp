<?php

use App\Http\Controllers\AkdController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| E-Register Routes (Modul Asuransi)
|--------------------------------------------------------------------------
*/

// Semua rute di dalam file ini otomatis terlindungi oleh login (auth)
Route::middleware('auth')->group(function () {
    
    // 1. Asuransi Kecelakaan Diri (AKD)
    Route::get('/e-register/akd', [AkdController::class, 'index'])->name('akd.index');

    // Nanti modul lain (PAR, PSAKBI, Varia, dll) tinggal ditaruh di bawah sini
});