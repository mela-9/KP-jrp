<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PublicLiabilityRegister;
use Inertia\Inertia;

class PublicLiabilityController extends Controller
{
    public function index()
    {
        $plData = PublicLiabilityRegister::orderBy('created_at', 'desc')->get();
        
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
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tgl_input' => 'required|date',
            'cob_toc' => 'required|string|max:255',
            'tertanggung' => 'required|string',
            'no_polis' => 'required|string|max:255|unique:public_liability_registers',
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

        // Set status awal workflow
        $validated['status_approval'] = 'Pending Kepala Staff';
        $validated['status_serah_terima'] = 'Belum Diserahkan';
        $validated['created_by'] = auth()->id();

        PublicLiabilityRegister::create($validated);

        return redirect()->back()->with('success', 'Data Polis Public Liability berhasil diinput dan menunggu paraf!');
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