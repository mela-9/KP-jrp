<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AkdRegister;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AkdController extends Controller
{
    // Fungsi Menampilkan Halaman
    public function index()
    {
        $user = auth()->user();
        $akdData = AkdRegister::orderBy('created_at', 'desc')->get();
        
        $stats = [
            'total' => AkdRegister::count(),
            'pending_paraf' => AkdRegister::where('status_approval', 'LIKE', '%Pending%')->count(),
            'siap_serah' => AkdRegister::where('status_approval', 'Diparaf Kepala Staff')
                                      ->where('status_serah_terima', '!=', 'Diterima Tertanggung')->count(),
            'selesai' => AkdRegister::where('status_serah_terima', 'Diterima Tertanggung')->count(),
        ];

        return Inertia::render('ERegist/AKD/Index', [
            'akdData' => $akdData,
            'stats' => $stats
        ]);
    }

    // Fungsi Menyimpan Data Baru (Lengkap dengan Upload File & Workflow)
    public function store(Request $request)
    {
        // 1. Validasi data yang masuk
        $validated = $request->validate([
            'tgl_input' => 'required|date',
            'nama_tertanggung' => 'required|string|max:255',
            'no_surat' => 'nullable|string|max:255',
            'no_polis' => 'nullable|string|max:255',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'tsi_ab' => 'nullable|numeric',
            'tsi_c' => 'nullable|numeric',
            'tsi_d' => 'nullable|numeric',
            'tsi_e' => 'nullable|numeric',
            'premi' => 'nullable|numeric',
            'sumber_bisnis' => 'nullable|string|max:255',
            'nomor_surat_array' => 'required|array|min:1',
            'scan_polis' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // 2. Jika nilai angka kosong, otomatis jadikan 0
        $kolomAngka = ['tsi_ab', 'tsi_c', 'tsi_d', 'tsi_e', 'premi'];
        foreach ($kolomAngka as $kolom) {
            $validated[$kolom] = $validated[$kolom] ?? 0;
        }

        // 3. Simpan file scan polis ke storage public (Gunakan move agar tidak error di Windows)
        if ($request->hasFile('scan_polis') && $request->file('scan_polis')->isValid()) {
            $file = $request->file('scan_polis');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Simpan langsung ke folder storage/app/public/scan_polis
            $file->move(storage_path('app/public/scan_polis'), $filename);
            
            $validated['scan_polis'] = 'scan_polis/' . $filename;
        }

        $validated['status_approval'] = 'Pending Kepala Staff';
        $validated['status_serah_terima'] = 'Belum Diserahkan';
        $validated['created_by'] = auth()->id();

        AkdRegister::create($validated);

        return redirect()->back();
    }

    // Aksi Kepala Staff: Memberikan Paraf Digital
    public function paraf(Request $request, $id)
    {
        $akd = AkdRegister::findOrFail($id);
        
        $akd->update([
            'status_approval' => 'Diparaf Kepala Staff',
            'paraf_timestamp' => now(),
            'paraf_oleh' => auth()->user()->name ?? 'Kepala Staff Underwriting',
        ]);

        return redirect()->back();
    }

public function konfirmasiTerima(Request $request, $id)
    {
        $request->validate([
            'bukti_terima' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $akd = AkdRegister::findOrFail($id);
        $path = null;

        if ($request->hasFile('bukti_terima') && $request->file('bukti_terima')->isValid()) {
            $file = $request->file('bukti_terima');
            
            // Generate nama file unik
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // GUNAKAN move() SEBAGAI GANTI store() UNTUK MENCEGAH ERROR WINDOWS/XAMPP
            $file->move(storage_path('app/public/bukti_terima'), $filename);
            
            $path = 'bukti_terima/' . $filename;
        }

        if (!$path) {
            return redirect()->back()->withErrors(['bukti_terima' => 'File gagal diproses oleh server.']);
        }

        $akd->update([
            'status_serah_terima' => 'Diterima Tertanggung',
            'bukti_terima' => $path,
            'tanggal_terima' => now(),
        ]);

        return redirect()->back()->with('success', 'Delivery Receipt berhasil diunggah!');
    }
    // Aksi Staff 1: Mengajukan data ke Kepala Staff untuk minta approval
    public function mintaApproval(Request $request, $id)
    {
        $akd = AkdRegister::findOrFail($id);
        
        $akd->update([
            'status_approval' => 'Pending Kepala Staff',
        ]);

        return redirect()->back();
    }

    // Aksi Staff 2: Menyerahkan polis yang sudah diparaf ke Agen lapangan
    public function kirimKeAgen(Request $request, $id)
    {
        $akd = AkdRegister::findOrFail($id);
        
        $akd->update([
            'status_serah_terima' => 'Siap Dikirim Agen',
        ]);

        return redirect()->back();
    }
}