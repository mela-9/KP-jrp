<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleRegister;
use Inertia\Inertia;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicleData = VehicleRegister::orderBy('created_at', 'desc')->get();
        
        $stats = [
            'total' => VehicleRegister::count(),
            'pending_paraf' => VehicleRegister::where('status_approval', 'LIKE', '%Pending%')->orWhereNull('status_approval')->count(),
            'siap_serah' => VehicleRegister::where('status_approval', 'Diparaf Kepala Staff')
                                          ->where('status_serah_terima', 'Belum Diserahkan')
                                          ->count(),
            'selesai' => VehicleRegister::where('status_serah_terima', 'Diterima Tertanggung')->count(),
        ];

        return Inertia::render('ERegist/Vehicle/Index', [
            'vehicleData' => $vehicleData,
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tgl_input' => 'required|date',
            'no_polis' => 'required|string|max:255|unique:vehicle_registers',
            'nama_tertanggung' => 'required|string|max:255',
            'alamat_tertanggung' => 'required|string',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'no_polisi' => 'required|string|max:50',
            'merk_tipe' => 'required|string|max:255',
            'no_rangka_mesin' => 'required|string|max:255',
            'penggunaan' => 'nullable|string|max:100',
            'tsi_casco' => 'nullable|numeric',
            'tsi_tjh' => 'nullable|numeric',
            'total_premi' => 'nullable|numeric',
            'biaya_admin' => 'nullable|numeric',
            'scan_polis' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $validated['tsi_casco'] = $validated['tsi_casco'] ?? 0;
        $validated['tsi_tjh'] = $validated['tsi_tjh'] ?? 0;
        $validated['total_premi'] = $validated['total_premi'] ?? 0;
        $validated['biaya_admin'] = $validated['biaya_admin'] ?? 0;

        // Tangani upload scan polis anti-error Windows
        if ($request->hasFile('scan_polis') && $request->file('scan_polis')->isValid()) {
            $file = $request->file('scan_polis');
            $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(storage_path('app/public/vehicle_scan'), $filename);
            $validated['scan_polis'] = 'vehicle_scan/' . $filename;
        }

        // Set status awal workflow
        $validated['status_approval'] = 'Pending Kepala Staff';
        $validated['status_serah_terima'] = 'Belum Diserahkan';
        $validated['created_by'] = auth()->id();

        VehicleRegister::create($validated);

        return redirect()->back()->with('success', 'Data Polis Kendaraan berhasil diinput dan menunggu paraf!');
    }

    // Aksi: Minta Approval (Staff)
    public function mintaApproval($id)
    {
        $vehicle = VehicleRegister::findOrFail($id);
        $vehicle->update([
            'status_approval' => 'Pending Kepala Staff'
        ]);

        return redirect()->back()->with('success', 'Pengajuan approval Kendaraan dikirim ke Kepala Staff.');
    }

    // Aksi: Berikan Paraf Digital (Kepala Staff)
    public function paraf($id)
    {
        $vehicle = VehicleRegister::findOrFail($id);
        $vehicle->update([
            'status_approval' => 'Diparaf Kepala Staff',
            'paraf_timestamp' => now(),
            'paraf_oleh' => auth()->user()->name ?? 'Kepala Staff Underwriting',
        ]);

        return redirect()->back()->with('success', 'Polis Kendaraan berhasil diparaf!');
    }

    // Aksi: Kirim ke Agen (Staff)
    public function kirimKeAgen($id)
    {
        $vehicle = VehicleRegister::findOrFail($id);
        $vehicle->update([
            'status_serah_terima' => 'Belum Diserahkan'
        ]);

        return redirect()->back()->with('success', 'Polis Kendaraan siap diserahkan ke agen lapangan.');
    }

    // Aksi: Konfirmasi Terima / Upload Delivery Receipt (Agen)
    public function konfirmasiTerima(Request $request, $id)
    {
        if (!$request->hasFile('bukti_terima')) {
            return redirect()->back()->withErrors(['bukti_terima' => 'File bukti terima tidak terbaca oleh server.']);
        }

        $file = $request->file('bukti_terima');
        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $file->move(storage_path('app/public/vehicle_bukti'), $filename);
        $path = 'vehicle_bukti/' . $filename;

        $vehicle = VehicleRegister::findOrFail($id);
        $vehicle->update([
            'status_serah_terima' => 'Diterima Tertanggung',
            'bukti_terima' => $path,
            'tanggal_terima' => now(),
        ]);

        return redirect()->back()->with('success', 'Delivery Receipt Kendaraan berhasil diunggah!');
    }
}