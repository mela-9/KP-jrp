<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuratBlock;
use App\Models\AkdRegister;
use App\Models\ParRegister;
use App\Models\VehicleRegister;
use App\Models\VariaRegister;
use App\Models\PublicLiabilityRegister;
use App\Models\SuretyBondRegister;

class SuratBlockSeeder extends Seeder
{
    // Ganti fungsi ini saja di dalam SuratBlockSeeder.php
    private function getMaxSuratNumber($modelClass, $jalur)
    {
        $records = $modelClass::where('no_surat', 'LIKE', "%/{$jalur}/%")->pluck('nomor_surat_array');
        $maxSeq = 0;
        
        foreach ($records as $arraySurat) {
            // PENGAMANAN: Jika data dari database terbaca sebagai String JSON, ubah ke Array
            if (is_string($arraySurat)) {
                $arraySurat = json_decode($arraySurat, true);
            }
            
            if (is_array($arraySurat)) {
                foreach ($arraySurat as $surat) {
                    $parts = explode('/', $surat);
                    $lastPart = end($parts);
                    // Ambil angka depan sebelum -H (misal: 0145-H1 menjadi 145)
                    $seq = (int) explode('-', $lastPart)[0]; 
                    if ($seq > $maxSeq) {
                        $maxSeq = $seq;
                    }
                }
            }
        }
        return $maxSeq;
    }
    public function run(): void
    {
        SuratBlock::truncate();

        // Cari nomor tertinggi masing-masing produk
        $maxAkd = $this->getMaxSuratNumber(AkdRegister::class, '1125');
        $maxPar = $this->getMaxSuratNumber(ParRegister::class, '1125');
        $maxSb  = $this->getMaxSuratNumber(SuretyBondRegister::class, '1125');

        $maxVehicle = $this->getMaxSuratNumber(VehicleRegister::class, '1126');
        $maxVaria   = $this->getMaxSuratNumber(VariaRegister::class, '1126');
        $maxPl      = $this->getMaxSuratNumber(PublicLiabilityRegister::class, '1126');

        // Tentukan Global Max per Jalur
        $globalMax1125 = max($maxAkd, $maxPar, $maxSb);
        $globalMax1126 = max($maxVehicle, $maxVaria, $maxPl);

        // 1. Blok khusus Jalur 1125 (AKD, PAR, Surety Bond)
        $produkJalur1 = [
            ['jenis' => 'AKD', 'pakem' => 'AKD-JRP', 'terpakai' => $maxAkd],
            ['jenis' => 'PAR', 'pakem' => 'PAR-JRP', 'terpakai' => $maxPar],
            ['jenis' => 'SURETY', 'pakem' => 'SB-JRP', 'terpakai' => $maxSb],
        ];

        foreach ($produkJalur1 as $p) {
            SuratBlock::create([
                'jenis_polis' => $p['jenis'],
                'kode_pakem'  => $p['pakem'],
                'jalur'       => '1125',
                'tahun'       => date('Y'),
                'range_start' => 1,
                'range_end'   => 3000,
                'terpakai'    => $p['terpakai'],
                'last_number' => $globalMax1125, // Menggunakan antrean global
            ]);
        }

        // 2. Blok khusus Jalur 1126 (Vehicle, Varia, PL)
        $produkJalur2 = [
            ['jenis' => 'VEHICLE', 'pakem' => 'VEH-JRP', 'terpakai' => $maxVehicle],
            ['jenis' => 'VARIA',   'pakem' => 'VAR-JRP', 'terpakai' => $maxVaria],
            ['jenis' => 'PL',      'pakem' => 'PL-JRP',  'terpakai' => $maxPl],
        ];

        foreach ($produkJalur2 as $p) {
            SuratBlock::create([
                'jenis_polis' => $p['jenis'],
                'kode_pakem'  => $p['pakem'],
                'jalur'       => '1126',
                'tahun'       => date('Y'),
                'range_start' => 1,
                'range_end'   => 3000,
                'terpakai'    => $p['terpakai'],
                'last_number' => $globalMax1126, // Menggunakan antrean global
            ]);
        }
    }
}