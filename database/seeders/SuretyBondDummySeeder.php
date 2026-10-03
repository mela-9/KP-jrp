<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuretyBondRegister; 
use App\Models\SuratBlock;
use Carbon\Carbon;

class SuretyBondDummySeeder extends Seeder
{
    public function run(): void
    {
        $tertanggungList = ['PT Nusantara Logistik', 'PT Anugerah Jaya', 'CV Tri Mandiri', 'PT Pembangunan Semesta'];
        $cobList = ['Jaminan Penawaran (Bid Bond)', 'Jaminan Pelaksanaan (Performance Bond)', 'Jaminan Uang Muka (Advance Payment Bond)', 'Jaminan Pemeliharaan (Maintenance Bond)'];
        $approvalList = ['Pending Kepala Staff', 'Diparaf Kepala Staff'];
        $serahTerimaList = ['Belum Diserahkan', 'Siap Dikirim Agen', 'Diterima Tertanggung'];
        $agenList = ['Direct', 'Agency', 'Broker'];
        $months = [7, 8, 9, 10];

        $globalPolisCounter = 1;
        $blokSurat = SuratBlock::where('jenis_polis', 'SURETY')->where('jalur', '1125')->first();
        $kodePakem = $blokSurat ? $blokSurat->kode_pakem : 'SB-JRP';
        
        // PENGAMANAN GLOBAL: Ambil nilai tertinggi dari SELURUH pengguna Jalur 1125
        $currentNumber = SuratBlock::where('jalur', '1125')->max('last_number') ?? 0;

        foreach ($months as $month) {
            $target = rand(25, 35);
            $maxDays = ($month == 10) ? 10 : Carbon::create(2026, $month, 1)->daysInMonth;

            for ($i = 0; $i < $target; $i++) {
                $date = Carbon::create(2026, $month, rand(1, $maxDays), rand(8, 17), rand(0, 59));
                $currentNumber++;
                $jumlahHalaman = rand(1, 3);
                
                $noSuratBase = "{$kodePakem}/1125/2026/" . str_pad($currentNumber, 4, '0', STR_PAD_LEFT);
                $nomorSuratArray = [];
                for ($h = 1; $h <= $jumlahHalaman; $h++) {
                    $nomorSuratArray[] = "{$noSuratBase}-H{$h}";
                }
                
                $noPolis = 'POL/SB/2026' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . str_pad($globalPolisCounter, 4, '0', STR_PAD_LEFT);
                $kondisi = ($i === 5) ? 'Rusak' : (($i === 11) ? 'Parsial' : 'Normal');
                $keteranganAudit = ($kondisi === 'Rusak') ? 'Kertas jaminan robek saat cetak' : (($kondisi === 'Parsial') ? 'Salah cetak jalur printer' : null);

                SuretyBondRegister::firstOrCreate(
                    ['no_polis' => $noPolis],
                    [
                        'tgl_input' => $date->toDateString(),
                        'no_surat' => $noSuratBase,
                        'cob_toc' => $cobList[array_rand($cobList)],
                        'tertanggung' => $tertanggungList[array_rand($tertanggungList)],
                        'periode_awal' => $date->toDateString(),
                        'periode_akhir' => (clone $date)->addYear()->toDateString(),
                        'tsi' => rand(30, 150) * 1000000,
                        'share' => '100%',
                        'premi' => rand(8, 45) * 500000,
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
            
            $noSuratBase = "{$kodePakem}/1125/2026/" . str_pad($currentNumber, 4, '0', STR_PAD_LEFT);
            $nomorSuratArray = ["{$noSuratBase}-H1"];
            $noPolis = 'POL/SB/202610/PEND' . str_pad($p, 3, '0', STR_PAD_LEFT);

            SuretyBondRegister::firstOrCreate(
                ['no_polis' => $noPolis],
                [
                    'tgl_input' => $date->toDateString(),
                    'no_surat' => $noSuratBase,
                    'cob_toc' => $cobList[array_rand($cobList)],
                    'tertanggung' => $tertanggungList[array_rand($tertanggungList)],
                    'periode_awal' => $date->toDateString(),
                    'periode_akhir' => (clone $date)->addYear()->toDateString(),
                    'tsi' => rand(30, 150) * 1000000,
                    'share' => '100%',
                    'premi' => rand(8, 45) * 500000,
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

        // Sinkronisasi status akhir ke seluruh pengguna Jalur 1125
        SuratBlock::where('jalur', '1125')->update(['last_number' => $currentNumber]);
    }
}