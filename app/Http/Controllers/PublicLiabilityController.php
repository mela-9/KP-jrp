<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PublicLiabilityRegister;
use App\Models\VehicleRegister;
use App\Models\VariaRegister;
use App\Models\SuratBlock;
use Inertia\Inertia;

class PublicLiabilityController extends Controller
{
    public function index(Request $request)
    {
        $query = PublicLiabilityRegister::query();

        if ($request->filled('bulan')) {
            $query->whereMonth('tgl_input', $request->bulan);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_polis', 'LIKE', "%{$search}%")
                  ->orWhere('no_surat', 'LIKE', "%{$search}%")
                  ->orWhere('tertanggung', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('jalur')) {
            $query->where('no_surat', 'LIKE', "%/{$request->jalur}/%");
        }

        $plData = $query->orderBy('created_at', 'desc')->get();
        
        // KUNCI UTAMA: Public Liability menggunakan Jalur 1126 (Bersama Vehicle & Varia)
        $jalurFisik = '1126';

        // CARA AMBIL NOMOR URUT BERIKUTNYA YANG BENAR (GLOBAL LINTAS PRODUK JALUR 1126: Vehicle, Varia, PL)
        $lastVeh = VehicleRegister::where('no_surat', 'LIKE', "%/{$jalurFisik}/%")->latest('id')->value('no_surat');
        $lastVar = VariaRegister::where('no_surat', 'LIKE', "%/{$jalurFisik}/%")->latest('id')->value('no_surat');
        $lastPl  = PublicLiabilityRegister::where('no_surat', 'LIKE', "%/{$jalurFisik}/%")->latest('id')->value('no_surat');

        $maxSeq = 0;
        foreach ([$lastVeh, $lastVar, $lastPl] as $surat) {
            if ($surat) {
                $parts = explode('/', $surat);
                $seq = (int) end($parts);
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }
        
        $nextNumber = $maxSeq + 1;

        $blokPl = SuratBlock::where('jenis_polis', 'PL')->where('jalur', $jalurFisik)->first();
        $kodePakem = $blokPl ? $blokPl->kode_pakem : 'PL-JRP';

        $tahun = date('Y');
        $bulanTahun = '0926';

        $autoNumbers = [
            'no_polis' => 'POL/PL/' . $tahun . $bulanTahun . '/' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'no_surat' => $kodePakem . '/' . $jalurFisik . '/' . $tahun . '/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT),
        ];

        $stats = [
            'total' => PublicLiabilityRegister::count(),
            'pending_paraf' => PublicLiabilityRegister::where('status_approval', 'LIKE', '%Pending%')->orWhereNull('status_approval')->count(),
            'siap_serah' => PublicLiabilityRegister::where('status_approval', 'Diparaf Kepala Staff')
                                                    ->where('status_serah_terima', 'Belum Diserahkan')
                                                    ->count(),
            'selesai' => PublicLiabilityRegister::where('status_serah_terima', 'Diterima Tertanggung')->count(),
        ];

        return Inertia::render('ERegist/PublicLiability/Index', [
            'plData' => $plData,
            'stats' => $stats,
            'autoNumbers' => $autoNumbers,
            'filters' => $request->only(['bulan', 'jalur', 'search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tgl_input' => 'required|date',
            'cob_toc' => 'required|string|max:255',
            'tertanggung' => 'required|string',
            'no_polis' => 'required|string|max:255|unique:public_liability_registers',
            'no_surat' => 'required|string',
            'jumlah_halaman' => 'required|integer|min:1|max:5',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'tsi' => 'nullable|numeric',
            'share' => 'nullable|string|max:50',
            'premi' => 'nullable|numeric',
            'jatuh_tempo' => 'nullable|date',
            'agen' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'scan_polis' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $jalurFisik = '1126';

        // 1. Tarik nomor urut berdasarkan antrean global tertinggi di Jalur 1126 (Vehicle, Varia, PL)
        $lastVeh = VehicleRegister::where('no_surat', 'LIKE', "%/{$jalurFisik}/%")->latest('id')->value('no_surat');
        $lastVar = VariaRegister::where('no_surat', 'LIKE', "%/{$jalurFisik}/%")->latest('id')->value('no_surat');
        $lastPl  = PublicLiabilityRegister::where('no_surat', 'LIKE', "%/{$jalurFisik}/%")->latest('id')->value('no_surat');

        $maxSeq = 0;
        foreach ([$lastVeh, $lastVar, $lastPl] as $surat) {
            if ($surat) {
                $parts = explode('/', $surat);
                $seq = (int) end($parts);
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }
        
        $startNum = $maxSeq + 1;
        
        $nomorSuratArray = [];
        $blokPl = SuratBlock::where('jenis_polis', 'PL')->where('jalur', $jalurFisik)->first();
        $kodePakem = $blokPl ? $blokPl->kode_pakem : 'PL-JRP';

        for ($i = 0; $i < $validated['jumlah_halaman']; $i++) {
            $currentNo = $startNum + $i;
            $nomorSuratArray[] = $kodePakem . '/' . $jalurFisik . '/' . date('Y') . '/' . str_pad($currentNo, 4, '0', STR_PAD_LEFT);
        }
        
        $validated['nomor_surat_array'] = $nomorSuratArray;
        $newLastNumber = $startNum + $validated['jumlah_halaman'] - 1;
        
        unset($validated['jumlah_halaman']);

        $validated['tsi'] = $validated['tsi'] ?? 0;
        $validated['premi'] = $validated['premi'] ?? 0;
        $validated['share'] = $validated['share'] ?? '100%';

        // Tangani upload scan polis anti-error Windows
        if ($request->hasFile('scan_polis') && $request->file('scan_polis')->isValid()) {
            $file = $request->file('scan_polis');
            $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(storage_path('app/public/pl_scan'), $filename);
            $validated['scan_polis'] = 'pl_scan/' . $filename;
        }

        // Set status awal workflow & kondisi surat normal
        $validated['status_approval'] = 'Draft Staf';
        $validated['status_serah_terima'] = 'Belum Diserahkan';
        $validated['created_by'] = auth()->id();
        $validated['kondisi_surat'] = 'Normal';

        PublicLiabilityRegister::create($validated);

        // 2. Update last_number global pada blok PL di jalur 1126
        if ($blokPl) {
            $blokPl->update([
                'last_number' => $newLastNumber,
                'terpakai' => $newLastNumber
            ]);
        }

        return redirect()->back()->with('success', 'Data Polis Public Liability berhasil diinput, kuota surat terpotong otomatis, dan menunggu paraf!');
    }

    // Aksi Audit Perubahan Nomor Surat (Normal / Rusak / Parsial) tanpa daftar ulang
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

        $register = PublicLiabilityRegister::findOrFail($id);
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
            $register->keterangan_audit .= " | Salah Jalur / Parsial (Lintas Jalur " . ($request->jalur_tambahan ?? '1125') . ").";
            if ($request->filled('no_surat_jalur_tambahan')) {
                $arr = $register->nomor_surat_array;
                $arr[] = $request->no_surat_jalur_tambahan;
                $register->nomor_surat_array = $arr;
            }
        }

        $register->save();

        return redirect()->back()->with('success', 'Status audit dan riwayat nomor surat Public Liability berhasil diperbarui!');
    }

    // Aksi: Minta Approval (Staff)
    public function mintaApproval($id)
    {
        $pl = PublicLiabilityRegister::findOrFail($id);
        $pl->update([
            'status_approval' => 'Pending Kepala Staff'
        ]);

        return redirect()->back()->with('success', 'Pengajuan approval Public Liability dikirim ke Kepala Staff.');
    }

    // Aksi: Berikan Paraf Digital (Kepala Staff)
    public function paraf($id)
    {
        $pl = PublicLiabilityRegister::findOrFail($id);
        $pl->update([
            'status_approval' => 'Diparaf Kepala Staff',
            'paraf_timestamp' => now(),
            'paraf_oleh' => auth()->user()->name ?? 'Kepala Staff Underwriting',
        ]);

        return redirect()->back()->with('success', 'Polis Public Liability berhasil diparaf!');
    }

    // Aksi: Kirim ke Agen (Staff)
    public function kirimKeAgen($id)
    {
        $pl = PublicLiabilityRegister::findOrFail($id);
        $pl->update([
            'status_serah_terima' => 'Belum Diserahkan'
        ]);

        return redirect()->back()->with('success', 'Polis Public Liability siap diserahkan ke agen lapangan.');
    }

    // Aksi: Konfirmasi Terima / Upload Delivery Receipt (Agen)
    public function konfirmasiTerima(Request $request, $id)
    {
        if (!$request->hasFile('bukti_terima')) {
            return redirect()->back()->withErrors(['bukti_terima' => 'File bukti terima tidak terbaca oleh server.']);
        }

        $file = $request->file('bukti_terima');
        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $file->move(storage_path('app/public/pl_bukti'), $filename);
        $path = 'pl_bukti/' . $filename;

        $pl = PublicLiabilityRegister::findOrFail($id);
        $pl->update([
            'status_serah_terima' => 'Diterima Tertanggung',
            'bukti_terima' => $path,
            'tanggal_terima' => now(),
        ]);

        return redirect()->back()->with('success', 'Delivery Receipt Public Liability berhasil diunggah!');
    }
}