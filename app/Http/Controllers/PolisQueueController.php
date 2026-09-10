<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PolisQueue;
use App\Models\SuratStockDetail;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PolisQueueController extends Controller
{
    public function index()
    {
        $queues = PolisQueue::orderBy('created_at', 'desc')->get();
        return Inertia::render('ERegist/Queue/Index', [
            'queues' => $queues
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_polis' => 'required|string',
            'nama_tertanggung' => 'required|string',
            'no_polis' => 'required|string',
            'tgl_input' => 'required|date',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'premi' => 'nullable|numeric',
            'jumlah_halaman' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $availableStocks = SuratStockDetail::where('status', 'AVAILABLE')
                ->whereHas('block', function($query) use ($validated) {
                    $query->whereJsonContains('allowed_polis', $validated['jenis_polis']);
                })
                ->take($validated['jumlah_halaman'])
                ->get();

            if ($availableStocks->count() < $validated['jumlah_halaman']) {
                return redirect()->back()->withErrors(['jumlah_halaman' => 'Stok nomor surat untuk jenis polis ' . $validated['jenis_polis'] . ' tidak mencukupi sejumlah halaman yang diminta!']);
            }

            $allocatedNumbers = [];
            foreach ($availableStocks as $stock) {
                $stock->update(['status' => 'USED']);
                $allocatedNumbers[] = $stock->nomor_surat_lengkap;
            }

            PolisQueue::create([
                'jenis_polis' => $validated['jenis_polis'],
                'nama_tertanggung' => $validated['nama_tertanggung'],
                'no_polis' => $validated['no_polis'],
                'tgl_input' => $validated['tgl_input'],
                'periode_awal' => $validated['periode_awal'],
                'periode_akhir' => $validated['periode_akhir'],
                'premi' => $validated['premi'] ?? 0,
                'jumlah_halaman' => $validated['jumlah_halaman'],
                'status_antrian' => 'SIAP_DISETUJUI',
                'allocated_surat_numbers' => $allocatedNumbers,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Polis berhasil masuk antrian dan nomor surat otomatis teralokasi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal memproses antrian polis: ' . $e->getMessage()]);
        }
    }
}