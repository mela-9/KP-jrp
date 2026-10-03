<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AkdRegister;
use App\Models\SuratBlock;
use Carbon\Carbon;

class AkdDummySeeder extends Seeder
{
    public function run(): void
    {
        // PENTING: Jangan gunakan truncate agar data lama yang sudah anteng di staff tidak terhapus!

        $tertanggungList = ['PT Sinar Mas Utama', 'CV Maju Jaya Abadi', 'PT Nusantara Logistik', 'Koperasi Warga Sejahtera'];
        $approvalList = ['Pending Kepala Staff', 'Diparaf Kepala Staff'];
        $serahTerimaList = ['Belum Diserahkan', 'Siap Dikirim Agen', 'Diterima Tertanggung'];
        
        $blokSurat = SuratBlock::where('jenis_polis', 'AKD')->where('jalur', '1125')->first();
        
        // --- 1. DATA HISTORIS LAMA (119 Polis / Juli s.d September 2026) ---
        $totalPolisLama = 119;
        $targetLembarLama = 237; 
        $currentSuratNumber = 0;

        $uniqueNumbersLama = range(1, $totalPolisLama);
        shuffle($uniqueNumbersLama);

        for ($i = 1; $i <= $totalPolisLama; $i++) {
            if ($i <= 40) {
                $month = 7;
            } elseif ($i <= 80) {
                $month = 8;
            } else {
                $month = 9;
            }

            $maxDays = Carbon::create(2026, $month, 1)->daysInMonth;
            $randomDay = rand(1, $maxDays);
            $date = Carbon::create(2026, $month, $randomDay, rand(8, 17), rand(0, 59));
            
            $sisaPolis = $totalPolisLama - $i + 1;
            $sisaLembarTarget = $targetLembarLama - $currentSuratNumber;
            
            if ($sisaPolis === 1) {
                $jumlahHalaman = $sisaLembarTarget;
            } else {
                $minH = max(1, $sisaLembarTarget - ($sisaPolis - 1) * 3);
                $maxH = min(3, $sisaLembarTarget - ($sisaPolis - 1) * 1);
                $jumlahHalaman = rand($minH, $maxH);
            }
            
            $nomorSuratArray = [];
            for ($h = 1; $h <= $jumlahHalaman; $h++) {
                $currentSuratNumber++;
                $nomorSuratArray[] = "{$blokSurat->kode_pakem}/{$blokSurat->jalur}/{$blokSurat->tahun}/" . str_pad($currentSuratNumber, 4, '0', STR_PAD_LEFT);
            }
            
            $noSuratBase = $nomorSuratArray[0];
            $uniqueSuffix = $uniqueNumbersLama[$i - 1];
            $noPolis = '1100010120926' . str_pad($uniqueSuffix, 5, '0', STR_PAD_LEFT);

            $kondisi = 'Normal';
            $keteranganAudit = null;

            if ($i === 5) {
                $kondisi = 'Rusak';
                $keteranganAudit = 'Fisik kertas robek saat dicetak';
            } elseif ($i === 15) {
                $kondisi = 'Parsial';
                $keteranganAudit = 'Salah cetak jalur (lintas ke jalur 1126)';
            }

            // Cek apakah data dengan nomor polis ini sudah ada agar tidak duplikasi saat seed ulang
            AkdRegister::firstOrCreate(
                ['no_polis' => $noPolis],
                [
                    'tgl_input' => $date->toDateString(),
                    'nama_tertanggung' => $tertanggungList[array_rand($tertanggungList)],
                    'no_surat' => $noSuratBase,
                    'periode_awal' => $date->toDateString(),
                    'periode_akhir' => (clone $date)->addYear()->toDateString(),
                    'tsi_ab' => rand(10, 50) * 1000000,
                    'premi' => rand(5, 50) * 500000,
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
        }

        // --- 2. TAMBAHAN 10 POLIS BARU (Pending Approval Kepala Staff) ---
        $totalPolisBaru = 10;
        $uniqueNumbersBaru = range(200, 250);
        shuffle($uniqueNumbersBaru);

        for ($j = 1; $j <= $totalPolisBaru; $j++) {
            $date = Carbon::create(2026, 10, rand(1, 5), rand(8, 17), rand(0, 59));
            
            $currentSuratNumber++;
            $nomorSuratArray = [
                "{$blokSurat->kode_pakem}/{$blokSurat->jalur}/{$blokSurat->tahun}/" . str_pad($currentSuratNumber, 4, '0', STR_PAD_LEFT)
            ];
            
            $noSuratBase = $nomorSuratArray[0];
            $uniqueSuffixBaru = $uniqueNumbersBaru[$j - 1];
            $noPolisBaru = '1100010121026' . str_pad($uniqueSuffixBaru, 5, '0', STR_PAD_LEFT);

            AkdRegister::firstOrCreate(
                ['no_polis' => $noPolisBaru],
                [
                    'tgl_input' => $date->toDateString(),
                    'nama_tertanggung' => $tertanggungList[array_rand($tertanggungList)],
                    'no_surat' => $noSuratBase,
                    'periode_awal' => $date->toDateString(),
                    'periode_akhir' => (clone $date)->addYear()->toDateString(),
                    'tsi_ab' => rand(10, 50) * 1000000,
                    'premi' => rand(5, 50) * 500000,
                    'sumber_bisnis' => 'Direct',
                    'nomor_surat_array' => $nomorSuratArray,
                    'scan_polis' => 'scan_polis/dummy.pdf', 
                    'status_approval' => 'Pending Kepala Staff', // Dikunci murni menunggu approval
                    'status_serah_terima' => 'Belum Diserahkan',
                    'kondisi_surat' => 'Normal',
                    'keterangan_audit' => null,
                    'created_by' => 1, 
                    'created_at' => $date,
                    'updated_at' => $date,
                ]
            );
        }

        // Sinkronisasi total lembar terpakai ke gudang SuratBlock
        if ($blokSurat) {
            $blokSurat->update([
                'last_number' => $currentSuratNumber,
                'terpakai' => $currentSuratNumber
            ]);
        }
    }
}