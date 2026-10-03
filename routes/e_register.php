<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AkdController;
use App\Http\Controllers\ParController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VariaController;
use App\Http\Controllers\PublicLiabilityController;
use App\Http\Controllers\SuretyBondController;
use App\Http\Controllers\ModuleInfoController;
use App\Http\Controllers\SuratBlockController;
use App\Http\Controllers\KepalaStaffController;
use App\Http\Controllers\ApprovalWorkflowController;

// 1. Landing Page (Welcome)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// 2. Rute Terproteksi (Wajib Login)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard Eksekutif
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 1. AKD (Asuransi Kecelakaan Diri)
    Route::get('/e-register/akd', [AkdController::class, 'index'])->name('akd.index');
    Route::post('/e-register/akd', [AkdController::class, 'store'])->name('akd.store');
    Route::put('/e-register/akd/{id}/ubah-surat', [AkdController::class, 'updateNomorSurat'])->name('akd.update-surat');

    // 2. PAR (Property All Risk)
    Route::get('/e-register/par', [ParController::class, 'index'])->name('par.index');
    Route::post('/e-register/par', [ParController::class, 'store'])->name('par.store');
    Route::put('/e-register/par/{id}/ubah-surat', [ParController::class, 'updateNomorSurat'])->name('par.update-surat');

    // 3. Vehicle (Kendaraan Bermotor)
    Route::get('/e-register/vehicle', [VehicleController::class, 'index'])->name('vehicle.index');
    Route::post('/e-register/vehicle', [VehicleController::class, 'store'])->name('vehicle.store');
    Route::put('/e-register/vehicle/{id}/ubah-surat', [VehicleController::class, 'updateNomorSurat'])->name('vehicle.update-surat');

    // 4. Varia (Aneka)   
    Route::get('/e-register/varia', [VariaController::class, 'index'])->name('varia.index');
    Route::post('/e-register/varia', [VariaController::class, 'store'])->name('varia.store');
    Route::put('/e-register/varia/{id}/ubah-surat', [VariaController::class, 'updateNomorSurat'])->name('varia.update-surat');

    // 5. Public Liability (PL)
    Route::get('/e-register/pl', [PublicLiabilityController::class, 'index'])->name('pl.index');
    Route::post('/e-register/pl', [PublicLiabilityController::class, 'store'])->name('pl.store');
    Route::put('/e-register/pl/{id}/ubah-surat', [PublicLiabilityController::class, 'updateNomorSurat'])->name('pl.update-surat');

    // 6. Surety Bond
    Route::get('/e-register/surety-bond', [SuretyBondController::class, 'index'])->name('surety.index');
    Route::post('/e-register/surety-bond', [SuretyBondController::class, 'store'])->name('surety.store');
    Route::put('/e-register/surety-bond/{id}/ubah-surat', [SuretyBondController::class, 'updateNomorSurat'])->name('surety.update-surat');

    // Manajemen Gudang Surat (Blok 0001 s.d 3000)
    Route::get('/e-register/surat-blocks', [SuratBlockController::class, 'index'])->name('surat-blocks.index');
    Route::post('/e-register/surat-blocks', [SuratBlockController::class, 'store'])->name('surat-blocks.store');
    Route::get('/e-register/surat-blocks/{id}/detail', [SuratBlockController::class, 'detail'])->name('surat-blocks.detail');

    // Rute Khusus Kepala Staff (Approval & History)
    Route::get('/e-register/kepala-staff/approvals', [KepalaStaffController::class, 'approvals'])->name('kepala-staff.approvals');
    Route::get('/e-register/kepala-staff/history', [KepalaStaffController::class, 'history'])->name('kepala-staff.history');
    // routes/web.php
    Route::post('/kepala-staff/approval/{type}/{id}', [KepalaStaffController::class, 'updateApproval'])
    ->name('kepala-staff.update-approval');

    Route::put('/e-register/polis/{type}/{id}/draft', [ApprovalWorkflowController::class, 'updateDraft'])
        ->name('register.draft.update');
    Route::post('/e-register/polis/{type}/{id}/request-approval', [ApprovalWorkflowController::class, 'requestApproval'])
        ->name('register.approval.request');
    
    // Alias untuk kemudahan navigasi
    Route::get('/approvals', [KepalaStaffController::class, 'approvals'])->name('approvals');
    Route::get('/history', [KepalaStaffController::class, 'history'])->name('history');
    });

require __DIR__.'/auth.php';