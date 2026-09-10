<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ParRegister;
use Inertia\Inertia;

class ParController extends Controller
{
    public function index()
    {
        $parData = ParRegister::orderBy('created_at', 'desc')->get();
        
        $stats = [
            'total' => ParRegister::count(),
            'pending_paraf' => ParRegister::where('status_approval', 'LIKE', '%Pending%')->orWhereNull('status_approval')->count(),
            'siap_serah' => ParRegister::where('status_approval', 'Diparaf Kepala Staff')
                                       ->where('status_serah_terima', 'Belum Diserahkan')
                                       ->count(),
            'selesai' => ParRegister::where('status_serah_terima', 'Diterima Tertanggung')->count(),
        ];

        return Inertia::render('ERegist/PAR/Index', [
            'parData' => $parData,
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tgl_input' => 'required|date',
            'tertanggung' => 'required|string',
            'no_polis' => 'nullable|string|max:255',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'tsi' => 'nullable|numeric',
            'rate' => 'nullable|string|max:50',
            'premi' => 'nullable|numeric',
            'jatuh_tempo' => 'nullable|date',
            'agen' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'kontak' => 'nullable|string|max:255',
            'scan_polis' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $validated['tsi'] = $validated['tsi'] ?? 0;
        $validated['premi'] = $validated['premi'] ?? 0;

        // Tangani upload scan polis anti-error Windows
        if ($request->hasFile('scan_polis') && $request->file('scan_polis')->isValid()) {
            $file = $request->file('scan_polis');
            $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(storage_path('app/public/par_scan'), $filename);
            $validated['scan_polis'] = 'par_scan/' . $filename;
        }

        // Set status awal workflow
        $validated['status_approval'] = 'Pending Kepala Staff';
        $validated['status_serah_terima'] = 'Belum Diserahkan';
        $validated['created_by'] = auth()->id();

        ParRegister::create($validated);

        return redirect()->back()->with('success', 'Data Polis PAR berhasil diinput dan menunggu paraf!');
    }

    // Aksi: Minta Approval (Staff)
    public function mintaApproval($id)
    {
        $par = ParRegister::findOrFail($id);
        $par->update([
            'status_approval' => 'Pending Kepala Staff'
        ]);

        return redirect()->back()->with('success', 'Pengajuan approval PAR dikirim ke Kepala Staff.');
    }

    // Aksi: Berikan Paraf Digital (Kepala Staff)
    public function paraf($id)
    {
        $par = ParRegister::findOrFail($id);
        $par->update([
            'status_approval' => 'Diparaf Kepala Staff',
            'paraf_timestamp' => now(),
            'paraf_oleh' => auth()->user()->name ?? 'Kepala Staff Underwriting',
        ]);

        return redirect()->back()->with('success', 'Polis PAR berhasil diparaf!');
    }

    // Aksi: Kirim ke Agen (Staff)
    public function kirimKeAgen($id)
    {
        $par = ParRegister::findOrFail($id);
        $par->update([
            'status_serah_terima' => 'Belum Diserahkan'
        ]);

        return redirect()->back()->with('success', 'Polis PAR siap diserahkan ke agen lapangan.');
    }

    // Aksi: Konfirmasi Terima / Upload Delivery Receipt (Agen)
    public function konfirmasiTerima(Request $request, $id)
    {
        if (!$request->hasFile('bukti_terima')) {
            return redirect()->back()->withErrors(['bukti_terima' => 'File bukti terima tidak terbaca oleh server.']);
        }

        $file = $request->file('bukti_terima');
        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $file->move(storage_path('app/public/par_bukti'), $filename);
        $path = 'par_bukti/' . $filename;

        $par = ParRegister::findOrFail($id);
        $par->update([
            'status_serah_terima' => 'Diterima Tertanggung',
            'bukti_terima' => $path,
            'tanggal_terima' => now(),
        ]);

        return redirect()->back()->with('success', 'Delivery Receipt PAR berhasil diunggah!');
    }
}