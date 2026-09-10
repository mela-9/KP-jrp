<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VariaRegister;
use Inertia\Inertia;

class VariaController extends Controller
{
    public function index()
    {
        $variaData = VariaRegister::orderBy('created_at', 'desc')->get();
        
        $stats = [
            'total' => VariaRegister::count(),
            'pending_paraf' => VariaRegister::where('status_approval', 'LIKE', '%Pending%')->orWhereNull('status_approval')->count(),
            'siap_serah' => VariaRegister::where('status_approval', 'Diparaf Kepala Staff')
                                       ->where('status_serah_terima', 'Belum Diserahkan')
                                       ->count(),
            'selesai' => VariaRegister::where('status_serah_terima', 'Diterima Tertanggung')->count(),
        ];

        return Inertia::render('ERegist/Varia/Index', [
            'variaData' => $variaData,
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tgl_input' => 'required|date',
            'cob_toc' => 'required|string|max:255',
            'tertanggung' => 'required|string',
            'no_polis' => 'required|string|max:255|unique:varia_registers',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'tsi' => 'nullable|numeric',
            'share' => 'nullable|string|max:50',
            'premi' => 'nullable|numeric',
            'jatuh_tempo' => 'nullable|date',
            'agen' => 'nullable|string|max:255',
            'scan_polis' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $validated['tsi'] = $validated['tsi'] ?? 0;
        $validated['premi'] = $validated['premi'] ?? 0;
        $validated['share'] = $validated['share'] ?? '100%';

        // Tangani upload scan polis anti-error Windows
        if ($request->hasFile('scan_polis') && $request->file('scan_polis')->isValid()) {
            $file = $request->file('scan_polis');
            $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(storage_path('app/public/varia_scan'), $filename);
            $validated['scan_polis'] = 'varia_scan/' . $filename;
        }

        // Set status awal workflow
        $validated['status_approval'] = 'Pending Kepala Staff';
        $validated['status_serah_terima'] = 'Belum Diserahkan';
        $validated['created_by'] = auth()->id();

        VariaRegister::create($validated);

        return redirect()->back()->with('success', 'Data Polis Varia berhasil diinput dan menunggu paraf!');
    }

    // Aksi: Minta Approval (Staff)
    public function mintaApproval($id)
    {
        $varia = VariaRegister::findOrFail($id);
        $varia->update([
            'status_approval' => 'Pending Kepala Staff'
        ]);

        return redirect()->back()->with('success', 'Pengajuan approval Varia dikirim ke Kepala Staff.');
    }

    // Aksi: Berikan Paraf Digital (Kepala Staff)
    public function paraf($id)
    {
        $varia = VariaRegister::findOrFail($id);
        $varia->update([
            'status_approval' => 'Diparaf Kepala Staff',
            'paraf_timestamp' => now(),
            'paraf_oleh' => auth()->user()->name ?? 'Kepala Staff Underwriting',
        ]);

        return redirect()->back()->with('success', 'Polis Varia berhasil diparaf!');
    }

    // Aksi: Kirim ke Agen (Staff)
    public function kirimKeAgen($id)
    {
        $varia = VariaRegister::findOrFail($id);
        $varia->update([
            'status_serah_terima' => 'Belum Diserahkan'
        ]);

        return redirect()->back()->with('success', 'Polis Varia siap diserahkan ke agen lapangan.');
    }

    // Aksi: Konfirmasi Terima / Upload Delivery Receipt (Agen)
    public function konfirmasiTerima(Request $request, $id)
    {
        if (!$request->hasFile('bukti_terima')) {
            return redirect()->back()->withErrors(['bukti_terima' => 'File bukti terima tidak terbaca oleh server.']);
        }

        $file = $request->file('bukti_terima');
        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $file->move(storage_path('app/public/varia_bukti'), $filename);
        $path = 'varia_bukti/' . $filename;

        $varia = VariaRegister::findOrFail($id);
        $varia->update([
            'status_serah_terima' => 'Diterima Tertanggung',
            'bukti_terima' => $path,
            'tanggal_terima' => now(),
        ]);

        return redirect()->back()->with('success', 'Delivery Receipt Varia berhasil diunggah!');
    }
}