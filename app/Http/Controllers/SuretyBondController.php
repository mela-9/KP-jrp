<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuretyBondRegister;
use Inertia\Inertia;

class SuretyBondController extends Controller
{
    public function index()
    {
        $sbData = SuretyBondRegister::orderBy('created_at', 'desc')->get();
        
        $stats = [
            'total' => SuretyBondRegister::count(),
            'pending_paraf' => SuretyBondRegister::where('status_approval', 'LIKE', '%Pending%')->orWhereNull('status_approval')->count(),
            'siap_serah' => SuretyBondRegister::where('status_approval', 'Diparaf Kepala Staff')
                                              ->where('status_serah_terima', 'Belum Diserahkan')
                                              ->count(),
            'selesai' => SuretyBondRegister::where('status_serah_terima', 'Diterima Tertanggung')->count(),
        ];

        return Inertia::render('ERegist/SuretyBond/Index', [
            'sbData' => $sbData,
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tgl_input' => 'required|date',
            'cob_toc' => 'required|string|max:255',
            'tertanggung' => 'required|string',
            'no_polis' => 'required|string|max:255|unique:surety_bond_registers',
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
            $file->move(storage_path('app/public/surety_scan'), $filename);
            $validated['scan_polis'] = 'surety_scan/' . $filename;
        }

        // Set status awal workflow
        $validated['status_approval'] = 'Pending Kepala Staff';
        $validated['status_serah_terima'] = 'Belum Diserahkan';
        $validated['created_by'] = auth()->id();

        SuretyBondRegister::create($validated);

        return redirect()->back()->with('success', 'Data Polis Surety Bond berhasil diinput dan menunggu paraf!');
    }

    // Aksi: Minta Approval (Staff)
    public function mintaApproval($id)
    {
        $sb = SuretyBondRegister::findOrFail($id);
        $sb->update([
            'status_approval' => 'Pending Kepala Staff'
        ]);

        return redirect()->back()->with('success', 'Pengajuan approval Surety Bond dikirim ke Kepala Staff.');
    }

    // Aksi: Berikan Paraf Digital (Kepala Staff)
    public function paraf($id)
    {
        $sb = SuretyBondRegister::findOrFail($id);
        $sb->update([
            'status_approval' => 'Diparaf Kepala Staff',
            'paraf_timestamp' => now(),
            'paraf_oleh' => auth()->user()->name ?? 'Kepala Staff Underwriting',
        ]);

        return redirect()->back()->with('success', 'Polis Surety Bond berhasil diparaf!');
    }

    // Aksi: Kirim ke Agen (Staff)
    public function kirimKeAgen($id)
    {
        $sb = SuretyBondRegister::findOrFail($id);
        $sb->update([
            'status_serah_terima' => 'Belum Diserahkan'
        ]);

        return redirect()->back()->with('success', 'Polis Surety Bond siap diserahkan ke agen lapangan.');
    }

    // Aksi: Konfirmasi Terima / Upload Delivery Receipt (Agen)
    public function konfirmasiTerima(Request $request, $id)
    {
        if (!$request->hasFile('bukti_terima')) {
            return redirect()->back()->withErrors(['bukti_terima' => 'File bukti terima tidak terbaca oleh server.']);
        }

        $file = $request->file('bukti_terima');
        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $file->move(storage_path('app/public/surety_bukti'), $filename);
        $path = 'surety_bukti/' . $filename;

        $sb = SuretyBondRegister::findOrFail($id);
        $sb->update([
            'status_serah_terima' => 'Diterima Tertanggung',
            'bukti_terima' => $path,
            'tanggal_terima' => now(),
        ]);

        return redirect()->back()->with('success', 'Delivery Receipt Surety Bond berhasil diunggah!');
    }
}