<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SuratBlock;
use App\Models\AkdRegister;
use App\Models\ParRegister;
use App\Models\VehicleRegister;
use App\Models\VariaRegister;
use App\Models\PublicLiabilityRegister;
use App\Models\SuretyBondRegister;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ambil nomor urut tertinggi secara global per jalur dari tabel gudang
        $lastJalur1 = SuratBlock::where('jalur', '1125')->max('last_number') ?? 0;
        $lastJalur2 = SuratBlock::where('jalur', '1126')->max('last_number') ?? 0;

        // 2. Hitung jumlah polis per lini produk untuk kebutuhan Chart Donat
        $akd = AkdRegister::count();
        $par = ParRegister::count();
        $vehicle = VehicleRegister::count();
        $varia = VariaRegister::count();
        $pl = PublicLiabilityRegister::count();
        $sb = SuretyBondRegister::count();
        
        // Hitung total keseluruhan
        $totalPolis = $akd + $par + $vehicle + $varia + $pl + $sb;

        return Inertia::render('Dashboard', [
            'stats' => [
                'jalur1' => $lastJalur1,
                'jalur2' => $lastJalur2,
                'total' => $totalPolis,
                'akd' => $akd,
                'par' => $par,
                'vehicle' => $vehicle,
                'varia' => $varia,
                'pl' => $pl,
                'surety_bond' => $sb,
            ],
            'suratBlocks' => SuratBlock::all()
        ]);
    }
}