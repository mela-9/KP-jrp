<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParRegister; 
use App\Models\SuratBlock;
use Carbon\Carbon;

class ParDummySeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan data lama PAR
        ParRegister::truncate();

        $tertanggungList = ['PT Graha Sarana', 'CV Citra Kirana', 'PT Delta Makmur', 'PT Anugerah Jaya'];
        $approvalList = ['Pending Kepala Staff', 'Diparaf Kepala Staff'];
        $serahTerimaList = ['Belum Diserahkan', 'Siap Dikirim Agen', 'Diterima Tertanggung'];
        $agenList = ['Direct', 'Agency', 'Broker'];
        $months = [7, 8, 9, 10]; 

        $globalPolisCounter = 1;

        // Ambil stok blok surat PAR jalur 1125 dari database
        $blokSurat = SuratBlock::where('kode_pakem', 'PAR-JRP')->where('jalur', '1125')->first();
        
        // Ambil posisi awal last_number supaya penomoran berurutan rapi dan tidak acak
        $currentNumber = $blokSurat ? $blokSurat->last_number : 0;

        foreach ($months as $month) {
            $target = rand(25, 35); // Target volume polis per bulan
            $maxDays = ($month == 10) ? 10 : Carbon::create(2026, $month, 1)->daysInMonth;

            for ($i = 0; $i < $target; $i++) {
                $date = Carbon::create(2026, $month, rand(1, $maxDays), rand(8, 17), rand(0, 59));
                
                // Naikkan nomor secara urut murni dari gudang (Anti-Redudan)
                $currentNumber++;
                
                $jumlahHalaman = rand(1, 3); 
                
                // Format: PAR-JRP/1125/2026/0001
                $noSuratBase = "{$blokSurat->kode_pakem}/{$blokSurat->jalur}/{$blokSurat->tahun}/" . str_pad($currentNumber, 4, '0', STR_PAD_LEFT);
                
                $nomorSuratArray = [];
                for ($h = 1; $h <= $jumlahHalaman; $h++) {
                    $nomorSuratArray[] = "{$noSuratBase}-H{$h}";
                }
                
                $noPolis = 'POL/PAR/2026' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . str_pad($globalPolisCounter, 4, '0', STR_PAD_LEFT);

                // Simulasi kondisi surat untuk demo manual (Normal, Rusak, Parsial)
                $kondisi = ($i === 3) ? 'Rusak' : (($i === 9) ? 'Parsial' : 'Normal');
                $keteranganAudit = ($kondisi === 'Rusak') ? 'Kertas macet di printer (Void)' : (($kondisi === 'Parsial') ? 'Salah cetak jalur (tercetak 1126)' : null);

                ParRegister::create([
                    'tgl_input' => $date->toDateString(),
                    'tertanggung' => $tertanggungList[array_rand($tertanggungList)], 
                    'no_surat' => $noSuratBase,
                    'no_polis' => $noPolis,
                    'periode_awal' => $date->toDateString(),
                    'periode_akhir' => (clone $date)->addYear()->toDateString(),
                    
                    'tsi' => rand(50, 200) * 1000000, 
                    'rate' => '0.' . rand(10, 25) . '%',
                    'premi' => rand(10, 80) * 500000,
                    'agen' => $agenList[array_rand($agenList)], 
                    
                    'nomor_surat_array' => $nomorSuratArray,
                    'scan_polis' => 'scan_polis/dummy_document.pdf', 
                    'status_approval' => $approvalList[array_rand($approvalList)],
                    'status_serah_terima' => $serahTerimaList[array_rand($serahTerimaList)],
                    
                    // Kolom audit kondisi surat
                    'kondisi_surat' => $kondisi,
                    'keterangan_audit' => $keteranganAudit,
                    
                    'created_by' => 1, 
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $globalPolisCounter++;
            }
        }

        // --- TAMBAHAN TEPAT 10 POLIS BARU UNTUK ANTREAN PENDING KEPALA STAFF ---
        for ($p = 1; $p <= 10; $p++) {
            $date = Carbon::create(2026, 10, rand(1, 10), rand(8, 17), rand(0, 59));
            $currentNumber++;
            
            $noSuratBase = "{$blokSurat->kode_pakem}/{$blokSurat->jalur}/{$blokSurat->tahun}/" . str_pad($currentNumber, 4, '0', STR_PAD_LEFT);
            $nomorSuratArray = ["{$noSuratBase}-H1"];
            $noPolis = 'POL/PAR/202610/PEND' . str_pad($p, 3, '0', STR_PAD_LEFT);

            ParRegister::create([
                'tgl_input' => $date->toDateString(),
                'tertanggung' => $tertanggungList[array_rand($tertanggungList)], 
                'no_surat' => $noSuratBase,
                'no_polis' => $noPolis,
                'periode_awal' => $date->toDateString(),
                'periode_akhir' => (clone $date)->addYear()->toDateString(),
                
                'tsi' => rand(50, 200) * 1000000, 
                'rate' => '0.15%',
                'premi' => rand(10, 80) * 500000,
                'agen' => $agenList[array_rand($agenList)], 
                
                'nomor_surat_array' => $nomorSuratArray,
                'scan_polis' => 'scan_polis/dummy_document.pdf', 
                
                // Dikunci murni masuk antrean approval Kepala Staff
                'status_approval' => 'Pending Kepala Staff',
                'status_serah_terima' => 'Belum Diserahkan',
                
                'kondisi_surat' => 'Normal',
                'keterangan_audit' => null,
                
                'created_by' => 1, 
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        // Sinkronisasi status akhir ke tabel SuratBlock gudang
        if ($blokSurat) {
            $blokSurat->update([
                'last_number' => $currentNumber,
                'terpakai' => $currentNumber
            ]);
        }
    }
}