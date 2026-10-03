<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AkdRegister;
use App\Models\ParRegister;        
use App\Models\SuretyBondRegister;
use App\Models\SuratBlock;
use Inertia\Inertia;

class AkdController extends Controller
{
   public function index(Request $request)
    {
        $query = AkdRegister::query();

        if ($request->filled('bulan')) {
            $query->whereMonth('tgl_input', $request->bulan);
        }

        if ($request->filled('jalur')) {
            $query->where('no_surat', 'LIKE', "%/{$request->jalur}/%");
        }

        if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('no_surat', 'LIKE', "%{$search}%")
              ->orWhere('no_polis', 'LIKE', "%{$search}%")
              ->orWhere('nama_tertanggung', 'LIKE', "%{$search}%");
        });
    }

        $akdData = $query->orderBy('tgl_input', 'asc')->get();
        
        $jalurFisik = '1125';

        // CARA AMBIL NOMOR URUT BERIKUTNYA YANG BENAR (GLOBAL LINTAS PRODUK JALUR 1125)
        // Ambil nomor surat terakhir dari tabel AKD, PAR, dan Surety Bond yang menggunakan jalur 1125
        $lastAkd = AkdRegister::where('no_surat', 'LIKE', "%/{$jalurFisik}/%")->latest('id')->value('no_surat');
        $lastPar = ParRegister::where('no_surat', 'LIKE', "%/{$jalurFisik}/%")->latest('id')->value('no_surat');
        $lastSb  = SuretyBondRegister::where('no_surat', 'LIKE', "%/{$jalurFisik}/%")->latest('id')->value('no_surat');

        $maxSeq = 0;
        foreach ([$lastAkd, $lastPar, $lastSb] as $surat) {
            if ($surat) {
                // Ekstrak 4 digit terakhir dari format "AKD-JRP/1125/2026/0015"
                $parts = explode('/', $surat);
                $seq = (int) end($parts);
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }
        
        $nextNumber = $maxSeq + 1;

        $blokAkd = SuratBlock::where('jenis_polis', 'AKD')->where('jalur', $jalurFisik)->first();
        $kodePakem = $blokAkd ? $blokAkd->kode_pakem : 'AKD-JRP';

        $tahun = date('Y');
        $bulanTahun = '0926';

        $autoNumbers = [
            'no_polis' => '110001012' . $bulanTahun . str_pad(rand(1, 999), 5, '0', STR_PAD_LEFT),
            'no_surat' => $kodePakem . '/' . $jalurFisik . '/' . $tahun . '/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT),
        ];
        
        $stats = [
            'total' => AkdRegister::count(),
            'pending_paraf' => AkdRegister::where('status_approval', 'LIKE', '%Pending%')->count(),
            'siap_serah' => AkdRegister::where('status_approval', 'Diparaf Kepala Staff')
                                    ->where('status_serah_terima', '!=', 'Diterima Tertanggung')->count(),
        ];

        return Inertia::render('ERegist/AKD/Index', [
            'akdData' => $akdData,
            'stats' => $stats,
            'autoNumbers' => $autoNumbers,
            'filters' => $request->only(['bulan', 'jalur', 'search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tgl_input' => 'required|date',
            'nama_tertanggung' => 'required|string|max:255',
            'no_surat' => 'required|string',
            'no_polis' => 'required|string',
            'jumlah_halaman' => 'required|integer|min:1|max:5',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'tsi_ab' => 'nullable|numeric',
            'premi' => 'nullable|numeric',
            'scan_polis' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $jalurFisik = '1125';

        // 1. Tarik nomor urut berdasarkan antrean global tertinggi di Jalur 1125
        $maxLastNumber = SuratBlock::where('jalur', $jalurFisik)->max('last_number') ?? 0;
        $startNum = $maxLastNumber + 1;
        
        $nomorSuratArray = [];
        $blokAkd = SuratBlock::where('jenis_polis', 'AKD')->where('jalur', $jalurFisik)->first();
        $kodePakem = $blokAkd ? $blokAkd->kode_pakem : 'AKD-JRP';

        for ($i = 0; $i < $validated['jumlah_halaman']; $i++) {
            $currentNo = $startNum + $i;
            $nomorSuratArray[] = $kodePakem . '/' . $jalurFisik . '/' . date('Y') . '/' . str_pad($currentNo, 4, '0', STR_PAD_LEFT);
        }
        
        $validated['nomor_surat_array'] = $nomorSuratArray;
        $newLastNumber = $startNum + $validated['jumlah_halaman'] - 1;
        
        unset($validated['jumlah_halaman']);

        $kolomAngka = ['tsi_ab', 'premi'];
        foreach ($kolomAngka as $kolom) {
            $validated[$kolom] = $validated[$kolom] ?? 0;
        }

        if ($request->hasFile('scan_polis') && $request->file('scan_polis')->isValid()) {
            $file = $request->file('scan_polis');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/public/scan_polis'), $filename);
            $validated['scan_polis'] = 'scan_polis/' . $filename;
        }

        $validated['status_approval'] = 'Draft Staf';
        $validated['status_serah_terima'] = 'Belum Diserahkan';
        $validated['created_by'] = auth()->id();
        $validated['kondisi_surat'] = 'Normal';

        AkdRegister::create($validated);

        // 2. Update blok surat milik AKD dengan last_number global terbaru agar produk lain (PAR/SB) membaca kelanjutannya
        if ($blokAkd) {
            $blokAkd->update([
                'last_number' => $newLastNumber,
                'terpakai' => $newLastNumber // atau disesuaikan dengan total pemakaian blok
            ]);
        }

        return redirect()->back()->with('success', 'Data Polis AKD berhasil diregistrasi dengan penomoran global berurutan!');
    }

    public function updateNomorSurat(Request $request, $id)
    {
        $request->validate([
            'kondisi_surat' => 'required|in:Rusak,Parsial,Normal',
            'alasan_ubah' => 'required|string',
            'halaman_rusak' => 'nullable|integer',
            'no_surat_pengganti' => 'nullable|string',
            'jalur_tambahan' => 'nullable|string',
            'no_surat_jalur_tambahan' => 'nullable|string',
        ]);

        $register = AkdRegister::findOrFail($id);
        $register->kondisi_surat = $request->kondisi_surat;
        $register->keterangan_audit = "Alasan Ubah: " . $request->alasan_ubah;

        if ($request->kondisi_surat === 'Rusak') {
            $arr = $register->nomor_surat_array;
            if ($request->filled('halaman_rusak') && isset($arr[$request->halaman_rusak - 1])) {
                $arr[$request->halaman_rusak - 1] = $request->no_surat_pengganti;
            }
            $register->nomor_surat_array = $arr;
            $register->keterangan_audit .= " | Halaman " . $request->halaman_rusak . " diganti karena rusak.";
        } elseif ($request->kondisi_surat === 'Parsial') {
            $register->keterangan_audit .= " | Salah Jalur / Parsial (Lintas Jalur " . ($request->jalur_tambahan ?? '1126') . ").";
            $request->filled('no_surat_jalur_tambahan') && $register->nomor_surat_array = array_merge($register->nomor_surat_array, [$request->no_surat_jalur_tambahan]);
        }

        $register->save();

        return redirect()->back()->with('success', 'Status dan riwayat nomor surat berhasil diperbarui!');
    }
}