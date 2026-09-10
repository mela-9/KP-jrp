<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SuratManagementController extends Controller
{
    // Menampilkan halaman utama inventaris & alokasi surat lintas polis
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Query master stok surat beserta pemetaan polis jika ada
        $query = DB::table('surat_stocks')
            ->leftJoin('polis_surat_mappings', 'surat_stocks.id', '=', 'polis_surat_mappings.surat_stock_id')
            ->select('surat_stocks.*', 'polis_surat_mappings.modul_tipe', 'polis_surat_mappings.polis_id');

        if ($search) {
            $query->where('surat_stocks.nomor_surat', 'like', "%{$search}%")
                  ->orWhere('surat_stocks.kode_produk', 'like', "%{$search}%");
        }

        $suratData = $query->orderBy('surat_stocks.created_at', 'desc')->paginate(15);

        // Statistik inventaris surat secara global
        $stats = [
            'total' => DB::table('surat_stocks')->count(),
            'available' => DB::table('surat_stocks')->where('status', 'AVAILABLE')->count(),
            'used' => DB::table('surat_stocks')->where('status', 'USED')->count(),
            'damaged' => DB::table('surat_stocks')->where('status', 'DAMAGED')->count(),
        ];

        return Inertia::render('ERegist/Surat/Index', [
            'suratData' => $suratData,
            'stats' => $stats,
            'filters' => $request->only(['search'])
        ]);
    }

    // Generate rentang nomor surat secara masal (Berdasarkan kode 4 angka: 1101, 1202, dll)
    public function store(Request $request)
    {
        $request->validate([
            'kode_produk' => 'required|string', // 1101 (AKD), 1202 (PAR), atau OTHERS
            'prefix_surat' => 'required|string', // Contoh: JRP-1101/2026/
            'nomor_awal' => 'required|integer', 
            'nomor_akhir' => 'required|integer|gte:nomor_awal',
        ]);

        DB::beginTransaction();
        try {
            for ($i = $request->nomor_awal; $i <= $request->nomor_akhir; $i++) {
                $formattedNo = str_pad($i, 4, '0', STR_PAD_LEFT);
                $nomorSuratLengkap = $request->prefix_surat . $formattedNo;

                DB::table('surat_stocks')->updateOrInsert(
                    ['nomor_surat' => $nomorSuratLengkap],
                    [
                        'kode_produk' => $request->kode_produk,
                        'status' => 'AVAILABLE',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            DB::commit();
            return redirect()->back()->with('success', 'Rentang nomor surat berhasil ditambahkan ke inventaris!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal generate nomor surat: ' . $e->getMessage()]);
        }
    }

    // Menandai nomor surat rusak / salah cetak (Void) agar tidak sembarangan terpakai
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:AVAILABLE,USED,DAMAGED',
            'keterangan' => 'nullable|string|max:255'
        ]);

        DB::table('surat_stocks')->where('id', $id)->update([
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Status nomor surat berhasil diperbarui!');
    }
}