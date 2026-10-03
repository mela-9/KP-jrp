<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratBlock;
use App\Models\AkdRegister;
use App\Models\ParRegister;
use App\Models\VehicleRegister;
use App\Models\VariaRegister;
use App\Models\PublicLiabilityRegister;
use App\Models\SuretyBondRegister;
use Inertia\Inertia;

class SuratBlockController extends Controller
{
    private function getAllRegisters()
    {
        $models = [
            'AKD' => AkdRegister::class,
            'PAR' => ParRegister::class,
            'VEHICLE' => VehicleRegister::class,
            'VARIA' => VariaRegister::class,
            'PL' => PublicLiabilityRegister::class,
            'SURETY' => SuretyBondRegister::class,
        ];

        $registers = collect();
        foreach ($models as $type => $model) {
            $registers = $registers->concat($model::all()->map(function ($item) use ($type) {
                $item->jenis_polis = $type;
                return $item;
            }));
        }

        return $registers;
    }

    private function numberBelongsToBlock(string $number, SuratBlock $block): bool
    {
        $parts = explode('/', $number);
        if (count($parts) < 4) {
            return false;
        }

        $jalur = $parts[count($parts) - 3];
        $tahun = $parts[count($parts) - 2];
        $sequencePart = $parts[count($parts) - 1];

        if ($jalur !== (string) $block->jalur || $tahun !== (string) $block->tahun) {
            return false;
        }

        if (!preg_match('/^(\d+)/', $sequencePart, $matches)) {
            return false;
        }

        $sequence = (int) $matches[1];
        return $sequence >= $block->range_start && $sequence <= $block->range_end;
    }

    private function getBlockUsage(SuratBlock $block, $registers): array
    {
        $usedNumbers = [];
        $blockRegisters = collect();

        foreach ($registers as $register) {
            $numbers = $register->nomor_surat_array;
            if (is_string($numbers)) {
                $numbers = json_decode($numbers, true);
            }
            $numbers = is_array($numbers) ? $numbers : [];
            if ($register->no_surat) {
                $numbers[] = $register->no_surat;
            }

            $matchingNumbers = collect($numbers)
                ->filter(fn ($number) => is_string($number) && $this->numberBelongsToBlock($number, $block))
                ->unique()
                ->values();

            if ($matchingNumbers->isEmpty()) {
                continue;
            }

            $newNumbers = $matchingNumbers->reject(fn ($number) => isset($usedNumbers[$number]))->values();
            if ($newNumbers->isEmpty()) {
                continue;
            }

            foreach ($newNumbers as $number) {
                $usedNumbers[$number] = true;
            }

            $detail = clone $register;
            $detail->setAttribute('nomor_surat_array', $newNumbers->all());
            $blockRegisters->push($detail);
        }

        return [
            'terpakai' => count($usedNumbers),
            'registers' => $blockRegisters->sortBy('tgl_input')->values(),
        ];
    }

    public function index()
    {
        $blocks = SuratBlock::all();
        $registers = $this->getAllRegisters();

        foreach ($blocks as $block) {
            $usage = $this->getBlockUsage($block, $registers);
            $block->setAttribute('terpakai', $usage['terpakai']);
        }

        return Inertia::render('ERegist/SuratBlockIndex', [
            'suratBlocks' => $blocks,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_polis' => 'required|string',
            'kode_pakem' => 'required|string',
            'jalur' => 'required|string',
            'tahun' => 'required|integer',
            'range_start' => 'required|integer',
            'range_end' => 'required|integer|gt:range_start',
        ]);

        SuratBlock::create([
            'jenis_polis' => $request->jenis_polis,
            'kode_pakem' => $request->kode_pakem,
            'jalur' => $request->jalur,
            'tahun' => $request->tahun,
            'range_start' => $request->range_start,
            'range_end' => $request->range_end,
            'terpakai' => 0,
            'last_number' => 0,
        ]);

        return redirect()->back()->with('success', 'Blok nomor surat gudang berhasil didaftarkan!');
    }

    public function detail($id)
    {
        $block = SuratBlock::findOrFail($id);
        $usage = $this->getBlockUsage($block, $this->getAllRegisters());
        $block->setAttribute('terpakai', $usage['terpakai']);

        return response()->json([
            'block' => $block,
            'registers' => $usage['registers'],
        ]);
    }
}