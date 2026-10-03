<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleRegister;
use App\Models\SuratBlock;
use Carbon\Carbon;

class VehicleDummySeeder extends Seeder
{
    public function run(): void
    {
        $tertanggungList = ['PT Auto Rental', 'CV Transportasi Jaya', 'Bapak Budi Santoso', 'Ibu Siti Aminah'];
        $approvalList = ['Pending Kepala Staff', 'Diparaf Kepala Staff'];
        $serahTerimaList = ['Belum Diserahkan', 'Siap Dikirim Agen', 'Diterima Tertanggung'];
        $months = [7, 8, 9, 10]; 

        $globalPolisCounter = 1;
        $blokSurat = SuratBlock::where('jenis_polis', 'VEHICLE')->where('jalur', '1126')->first();
        
        if (!$blokSurat) {
            $blokSurat = SuratBlock::create([
                'jenis_polis' => 'VEHICLE', 'kode_pakem' => 'VEH-JRP', 'jalur' => '1126',
                'tahun' => 2026, 'range_start' => 1, 'range_end' => 3000, 'terpakai' => 0, 'last_number' => 0,
            ]);
        }
        
        $kodePakem = $blokSurat->kode_pakem;
        
        // PENGAMANAN GLOBAL: Ambil nilai tertinggi dari SELURUH pengguna Jalur 1126
        $currentNumber = SuratBlock::where('jalur', '1126')->max('last_number') ?? 0;

        foreach ($months as $month) {
            $target = rand(25, 35);
            $maxDays = ($month == 10) ? 10 : Carbon::create(2026, $month, 1)->daysInMonth;

            for ($i = 0; $i < $target; $i++) {
                $date = Carbon::create(2026, $month, rand(1, $maxDays), rand(8, 17), rand(0, 59));
                $currentNumber++;
                $jumlahHalaman = rand(1, 3); 
                
                $noSuratBase = "{$kodePakem}/1126/2026/" . str_pad($currentNumber, 4, '0', STR_PAD_LEFT);
                $nomorSuratArray = [];
                for ($h = 1; $h <= $jumlahHalaman; $h++) {
                    $nomorSuratArray[] = "{$noSuratBase}-H{$h}";
                }
                
                $noPolis = 'POL/VEH/2026' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . str_pad($globalPolisCounter, 4, '0', STR_PAD_LEFT);
                $kondisi = ($i === 4) ? 'Rusak' : (($i === 9) ? 'Parsial' : 'Normal');
                $keteranganAudit = ($kondisi === 'Rusak') ? 'Kertas kendaraan sobek saat cetak' : (($kondisi === 'Parsial') ? 'Salah cetak jalur printer' : null);

                VehicleRegister::firstOrCreate(
                    ['no_polis' => $noPolis],
                    [
                        'tgl_input' => $date->toDateString(),
                        'nama_tertanggung' => $tertanggungList[array_rand($tertanggungList)],
                        'alamat_tertanggung' => 'Jl. Jenderal Sudirman No. ' . rand(1, 100),
                        'no_surat' => $noSuratBase,
                        'periode_awal' => $date->toDateString(),
                        'periode_akhir' => (clone $date)->addYear()->toDateString(),
                        'no_polisi' => 'BG ' . rand(1000, 9999) . ' XX',
                        'merk_tipe' => 'Toyota Avanza ' . rand(2015, 2025),
                        'no_rangka_mesin' => 'MHK' . rand(100000, 999999),
                        'tsi_casco' => rand(150, 400) * 1000000,
                        'total_premi' => rand(3, 12) * 1000000,
                        'sumber_bisnis' => 'Direct',
                        'nomor_surat_array' => $nomorSuratArray,
                        'scan_polis' => 'scan_polis/dummy.pdf', 
                        'status_approval' => $approvalList[array_rand($approvalList)],
                        'status_serah_terima' => $serahTerimaList[array_rand($serahTerimaList)],
                        'kondisi_surat' => $kondisi,
                        'keterangan_audit' => $keteranganAudit,
                        'created_by' => 1, 
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]
                );
                $globalPolisCounter++;
            }
        }

        for ($p = 1; $p <= 10; $p++) {
            $date = Carbon::create(2026, 10, rand(1, 10), rand(8, 17), rand(0, 59));
            $currentNumber++;
            
            $noSuratBase = "{$kodePakem}/1126/2026/" . str_pad($currentNumber, 4, '0', STR_PAD_LEFT);
            $nomorSuratArray = ["{$noSuratBase}-H1"];
            $noPolis = 'POL/VEH/202610/PEND' . str_pad($p, 3, '0', STR_PAD_LEFT);

            VehicleRegister::firstOrCreate(
                ['no_polis' => $noPolis],
                [
                    'tgl_input' => $date->toDateString(),
                    'nama_tertanggung' => $tertanggungList[array_rand($tertanggungList)],
                    'alamat_tertanggung' => 'Jl. Jenderal Sudirman No. ' . rand(1, 100),
                    'no_surat' => $noSuratBase,
                    'periode_awal' => $date->toDateString(),
                    'periode_akhir' => (clone $date)->addYear()->toDateString(),
                    'no_polisi' => 'BG ' . rand(1000, 9999) . ' XX',
                    'merk_tipe' => 'Toyota Avanza ' . rand(2015, 2025),
                    'no_rangka_mesin' => 'MHK' . rand(100000, 999999),
                    'tsi_casco' => rand(150, 400) * 1000000,
                    'total_premi' => rand(3, 12) * 1000000,
                    'sumber_bisnis' => 'Direct',
                    'nomor_surat_array' => $nomorSuratArray,
                    'scan_polis' => 'scan_polis/dummy.pdf', 
                    'status_approval' => 'Pending Kepala Staff',
                    'status_serah_terima' => 'Belum Diserahkan',
                    'kondisi_surat' => 'Normal',
                    'keterangan_audit' => null,
                    'created_by' => 1, 
                    'created_at' => $date,
                    'updated_at' => $date,
                ]
            );
        }

        // Sinkronisasi status akhir ke seluruh pengguna Jalur 1126
        SuratBlock::where('jalur', '1126')->update(['last_number' => $currentNumber]);
    }
}