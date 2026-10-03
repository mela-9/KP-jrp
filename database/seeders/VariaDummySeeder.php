<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VariaRegister; 
use App\Models\SuratBlock;
use Carbon\Carbon;

class VariaDummySeeder extends Seeder
{
    public function run(): void
    {
        $tertanggungList = ['PT Karya Mandiri', 'CV Maju Jaya Abadi', 'PT Sinar Mas Utama'];
        $cobList = ['Fidelity Guarantee', 'Cash in Transit', 'Personal Accident', 'Burglary'];
        $agenList = ['Direct', 'Agency', 'Broker'];
        $approvalList = ['Pending Kepala Staff', 'Diparaf Kepala Staff'];
        $serahTerimaList = ['Belum Diserahkan', 'Siap Dikirim Agen', 'Diterima Tertanggung'];
        $months = [7, 8, 9, 10];

        $globalPolisCounter = 1;
        $blokSurat = SuratBlock::where('jenis_polis', 'VARIA')->where('jalur', '1126')->first();
        $kodePakem = $blokSurat ? $blokSurat->kode_pakem : 'VAR-JRP';
        
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
                
                $noPolis = 'POL/VAR/2026' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . str_pad($globalPolisCounter, 4, '0', STR_PAD_LEFT);
                $kondisi = ($i === 4) ? 'Rusak' : (($i === 10) ? 'Parsial' : 'Normal');
                $keteranganAudit = ($kondisi === 'Rusak') ? 'Kertas Varia sobek saat cetak' : (($kondisi === 'Parsial') ? 'Salah cetak jalur printer' : null);

                VariaRegister::firstOrCreate(
                    ['no_polis' => $noPolis],
                    [
                        'tgl_input' => $date->toDateString(),
                        'no_surat' => $noSuratBase,
                        'cob_toc' => $cobList[array_rand($cobList)],
                        'tertanggung' => $tertanggungList[array_rand($tertanggungList)], 
                        'periode_awal' => $date->toDateString(),
                        'periode_akhir' => (clone $date)->addYear()->toDateString(),
                        'tsi' => rand(15, 80) * 1000000, 
                        'share' => '100%',
                        'premi' => rand(5, 40) * 500000,
                        'agen' => $agenList[array_rand($agenList)], 
                        'nomor_surat_array' => $nomorSuratArray, 
                        'scan_polis' => 'scan_polis/dummy_document.pdf', 
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
            $noPolis = 'POL/VAR/202610/PEND' . str_pad($p, 3, '0', STR_PAD_LEFT);

            VariaRegister::firstOrCreate(
                ['no_polis' => $noPolis],
                [
                    'tgl_input' => $date->toDateString(),
                    'no_surat' => $noSuratBase,
                    'cob_toc' => $cobList[array_rand($cobList)],
                    'tertanggung' => $tertanggungList[array_rand($tertanggungList)], 
                    'periode_awal' => $date->toDateString(),
                    'periode_akhir' => (clone $date)->addYear()->toDateString(),
                    'tsi' => rand(15, 80) * 1000000, 
                    'share' => '100%',
                    'premi' => rand(5, 40) * 500000,
                    'agen' => $agenList[array_rand($agenList)], 
                    'nomor_surat_array' => $nomorSuratArray, 
                    'scan_polis' => 'scan_polis/dummy_document.pdf', 
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