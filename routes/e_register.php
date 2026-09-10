<?php

use App\Http\Controllers\AkdController;
use App\Http\Controllers\ParController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VariaController;
use App\Http\Controllers\PublicLiabilityController;
use App\Http\Controllers\SuretyBondController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleInfoController;
use App\Http\Controllers\SuratManagementController;

// Semua rute di dalam file ini otomatis terlindungi oleh login (auth)
Route::middleware('auth')->group(function () {
    
    // 1. Asuransi Kecelakaan Diri (AKD)
    Route::get('/e-register/akd', [AkdController::class, 'index'])->name('akd.index');
    Route::post('/e-register/akd', [AkdController::class, 'store'])->name('akd.store');
    // Rute Workflow Baru
    Route::post('/e-register/akd/{id}/paraf', [AkdController::class, 'paraf'])->name('akd.paraf');
    Route::post('/e-register/akd/{id}/terima', [AkdController::class, 'konfirmasiTerima'])->name('akd.terima'); 
    Route::post('/e-register/akd/{id}/minta-approval', [AkdController::class, 'mintaApproval'])->name('akd.mintaApproval');
    Route::post('/e-register/akd/{id}/kirim-ke-agen', [AkdController::class, 'kirimKeAgen'])->name('akd.kirimKeAgen');

    // Rute PAR
    Route::get('/e-register/par', [ParController::class, 'index'])->name('par.index');
    Route::post('/e-register/par', [ParController::class, 'store'])->name('par.store');
    // Rute Workflow PAR
    Route::post('/e-register/par/{id}/paraf', [ParController::class, 'paraf'])->name('par.paraf');
    Route::post('/e-register/par/{id}/terima', [ParController::class, 'konfirmasiTerima'])->name('par.terima'); 
    Route::post('/e-register/par/{id}/minta-approval', [ParController::class, 'mintaApproval'])->name('par.mintaApproval');
    Route::post('/e-register/par/{id}/kirim-ke-agen', [ParController::class, 'kirimKeAgen'])->name('par.kirimKeAgen');

    // Rute Kendaraan Bermotor
    Route::get('/e-register/vehicle', [VehicleController::class, 'index'])->name('vehicle.index');
    Route::post('/e-register/vehicle', [VehicleController::class, 'store'])->name('vehicle.store');
    // Rute Workflow Kendaraan
    Route::post('/e-register/vehicle/{id}/paraf', [VehicleController::class, 'paraf'])->name('vehicle.paraf');
    Route::post('/e-register/vehicle/{id}/terima', [VehicleController::class, 'konfirmasiTerima'])->name('vehicle.terima'); 
    Route::post('/e-register/vehicle/{id}/minta-approval', [VehicleController::class, 'mintaApproval'])->name('vehicle.mintaApproval');
    Route::post('/e-register/vehicle/{id}/kirim-ke-agen', [VehicleController::class, 'kirimKeAgen'])->name('vehicle.kirimKeAgen');

    // Rute Varia   
    Route::get('/e-register/varia', [VariaController::class, 'index'])->name('varia.index');
    Route::post('/e-register/varia', [VariaController::class, 'store'])->name('varia.store');
    // Rute Workflow Varia
    Route::post('/e-register/varia/{id}/paraf', [VariaController::class, 'paraf'])->name('varia.paraf');
    Route::post('/e-register/varia/{id}/terima', [VariaController::class, 'konfirmasiTerima'])->name('varia.terima'); 
    Route::post('/e-register/varia/{id}/minta-approval', [VariaController::class, 'mintaApproval'])->name('varia.mintaApproval');
    Route::post('/e-register/varia/{id}/kirim-ke-agen', [VariaController::class, 'kirimKeAgen'])->name('varia.kirimKeAgen');

    // Rute Public Liability (PL)
    Route::get('/e-register/pl', [PublicLiabilityController::class, 'index'])->name('pl.index');
    Route::post('/e-register/pl', [PublicLiabilityController::class, 'store'])->name('pl.store');
    // Rute Workflow Public Liability
    Route::post('/e-register/pl/{id}/paraf', [PublicLiabilityController::class, 'paraf'])->name('pl.paraf');
    Route::post('/e-register/pl/{id}/terima', [PublicLiabilityController::class, 'konfirmasiTerima'])->name('pl.terima'); 
    Route::post('/e-register/pl/{id}/minta-approval', [PublicLiabilityController::class, 'mintaApproval'])->name('pl.mintaApproval');
    Route::post('/e-register/pl/{id}/kirim-ke-agen', [PublicLiabilityController::class, 'kirimKeAgen'])->name('pl.kirimKeAgen');

    // Rute Surety Bond
    Route::get('/e-register/surety-bond', [SuretyBondController::class, 'index'])->name('surety.index');
    Route::post('/e-register/surety-bond', [SuretyBondController::class, 'store'])->name('surety.store');
    // Rute Workflow Surety Bond
    Route::post('/e-register/surety-bond/{id}/paraf', [SuretyBondController::class, 'paraf'])->name('surety.paraf');
    Route::post('/e-register/surety-bond/{id}/terima', [SuretyBondController::class, 'konfirmasiTerima'])->name('surety.terima'); 
    Route::post('/e-register/surety-bond/{id}/minta-approval', [SuretyBondController::class, 'mintaApproval'])->name('surety.mintaApproval');
    Route::post('/e-register/surety-bond/{id}/kirim-ke-agen', [SuretyBondController::class, 'kirimKeAgen'])->name('surety.kirimKeAgen');

    //Modul
    Route::get('/e-register/akd/info', [ModuleInfoController::class, 'akd'])->name('akd.info');
    Route::get('/e-register/par/info', [ModuleInfoController::class, 'par'])->name('par.info');
    Route::get('/e-register/vehicle/info', [ModuleInfoController::class, 'vehicle'])->name('vehicle.info');
    Route::get('/e-register/varia/info', [ModuleInfoController::class, 'varia'])->name('varia.info');
    Route::get('/e-register/pl/info', [ModuleInfoController::class, 'publicLiability'])->name('pl.info');
    Route::get('/e-register/surety-bond/info', [ModuleInfoController::class, 'suretyBond'])->name('surety.info');

    // Rute Modul Sentral Manajemen Nomor Surat
    Route::get('/e-register/manajemen-surat', [SuratManagementController::class, 'index'])->name('surat.index');
    Route::post('/e-register/manajemen-surat', [SuratManagementController::class, 'store'])->name('surat.store');
    Route::patch('/e-register/manajemen-surat/{id}/status', [SuratManagementController::class, 'updateStatus'])->name('surat.updateStatus');
});