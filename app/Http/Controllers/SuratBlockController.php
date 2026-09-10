<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratBlock;
use App\Models\SuratStockDetail;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SuratBlockController extends Controller
{
    public function index()
    {
        $blocks = SuratBlock::with('details')->orderBy('created_at', 'desc')->get();
        return Inertia::render('ERegist/Surat/Index', [
            'blocks' => $blocks
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:50',
            'seri_produk' => 'required|string|max:50',
            'nomor_awal' => 'required|integer',
            'nomor_akhir' => 'required|integer|gte:nomor_awal',
            'allowed_polis' => 'required|array|min:1',
            'is_other_flag' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $block = SuratBlock::create([
                'prefix' => $validated['prefix'],
                'seri_produk' => $validated['seri_produk'],
                'nomor_awal' => $validated['nomor_awal'],
                'nomor_akhir' => $validated['nomor_akhir'],
                'allowed_polis' => $validated['allowed_polis'],
                'is_other_flag' => $request->has('is_other_flag') ? true : false,
            ]);

            for ($i = $validated['nomor_awal']; $i <= $validated['nomor_akhir']; $i++) {
                $formattedNo = str_pad($i, 4, '0', STR_PAD_LEFT);
                $nomorLengkap = "{$validated['prefix']}-{$validated['seri_produk']}-{$formattedNo}";

                SuratStockDetail::create([
                    'surat_block_id' => $block->id,
                    'nomor_surat_lengkap' => $nomorLengkap,
                    'status' => 'AVAILABLE'
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Blok nomor surat berhasil didaftarkan dan digenerate!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal mendaftarkan blok surat: ' . $e->getMessage()]);
        }
    }
}