<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\RegisterNotification;
use App\Models\AkdRegister;
use App\Models\ParRegister;
use App\Models\VehicleRegister;
use App\Models\VariaRegister;
use App\Models\PublicLiabilityRegister;
use App\Models\SuretyBondRegister;
use Inertia\Inertia;

class KepalaStaffController extends Controller
{
    // Helper untuk menggabungkan semua register dari berbagai lini produk secara utuh tanpa batasan semu
    private function getAllRegisters()
    {
        $collection = collect();

        $models = [
            'AKD' => AkdRegister::class,
            'PAR' => ParRegister::class,
            'VEHICLE' => VehicleRegister::class,
            'VARIA' => VariaRegister::class,
            'PL' => PublicLiabilityRegister::class,
            'SURETY' => SuretyBondRegister::class,
        ];

        foreach ($models as $key => $model) {
            if (class_exists($model)) {
                $items = $model::all()->map(function($item) use ($key) {
                    $item->jenis_polis = $key;
                    return $item;
                });
                $collection = $collection->concat($items);
            }
        }

        return $collection->sortByDesc('created_at');
    }

    // Halaman Antrean Approval (Pending) - HANYA menampilkan yang statusnya belum diparaf / masih pending murni
    public function approvals(Request $request)
    {
        $models = [
            'AKD' => AkdRegister::class,
            'PAR' => ParRegister::class,
            'VEHICLE' => VehicleRegister::class,
            'VARIA' => VariaRegister::class,
            'PL' => PublicLiabilityRegister::class,
            'SURETY' => SuretyBondRegister::class,
        ];

        $pendingCollection = collect();

        foreach ($models as $key => $model) {
            if (class_exists($model)) {
                // KUNCI UTAMA: Hanya ambil yang status approval-nya null, kosong, atau mengandung kata 'Pending'.
                // Dan pastikan yang sudah 'Diparaf Kepala Staff' atau 'Ditolak' DISINGKIRKAN mutlak!
                $items = $model::where(function($query) {
                                  $query->whereNull('status_approval')
                                        ->orWhere('status_approval', '')
                                        ->orWhere('status_approval', 'LIKE', '%Pending%');
                              })
                              ->where('status_approval', 'NOT LIKE', '%Diparaf%')
                              ->where('status_approval', 'NOT LIKE', '%Ditolak%')
                              ->orderBy('created_at', 'desc')
                              ->get()
                              ->map(function($item) use ($key) {
                                  $item->jenis_polis = $key;
                                  return $item;
                              });
                $pendingCollection = $pendingCollection->concat($items);
            }
        }

        // Filter berdasarkan jenis polis jika dipilih dari menu dropdown
        if ($request->filled('jenis_polis')) {
            $pendingCollection = $pendingCollection->where('jenis_polis', $request->jenis_polis);
        }

        return Inertia::render('ERegist/KepalaStaff/Approvals', [
            'pendingData' => $pendingCollection->values(),
            'filters' => $request->only(['jenis_polis', 'search', 'tanggal', 'bulan', 'tahun'])
        ]);
    }

    public function updateApproval(Request $request, $type, $id)
    {
        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak',
            'alasan_penolakan' => 'nullable|string',
        ]);

        // Pemetaan model berdasarkan jenis polis yang dikirim dari frontend
        $modelMap = [
            'AKD' => AkdRegister::class,
            'PAR' => ParRegister::class,
            'VEHICLE' => VehicleRegister::class,
            'VARIA' => VariaRegister::class,
            'PL' => PublicLiabilityRegister::class,
            'SURETY' => SuretyBondRegister::class,
        ];

        $modelClass = $modelMap[strtoupper($type)] ?? null;

        if (!$modelClass) {
            return back()->withErrors(['message' => 'Jenis polis tidak dikenali.']);
        }

        $polis = $modelClass::findOrFail($id);
        $routeNames = [
            'AKD' => 'akd.index',
            'PAR' => 'par.index',
            'VEHICLE' => 'vehicle.index',
            'VARIA' => 'varia.index',
            'PL' => 'pl.index',
            'SURETY' => 'surety.index',
        ];

        if ($request->status === 'Ditolak') {
            $polis->update([
                'status_approval' => 'Revisi Staf',
                'keterangan_audit' => $request->alasan_penolakan,
            ]);
            $channel = 'notifications.staff';
            $message = 'Berkas berhasil dikembalikan ke Staf untuk revisi.';
        } else {
            $polis->update([
                'status_approval' => 'Diparaf Kepala Staff',
                'paraf_timestamp' => now(),
                'paraf_oleh' => $request->user()->name,
            ]);
            $channel = $polis->created_by
                ? 'App.Models.User.' . $polis->created_by
                : 'notifications.staff';
            $message = 'Berkas berhasil disetujui / diparaf.';
        }

        event(new RegisterNotification($channel, [
            'id' => strtoupper($type) . '-' . $polis->id,
            'type' => strtoupper($type),
            'no_polis' => $polis->no_polis,
            'nama_tertanggung' => $polis->nama_tertanggung ?? $polis->tertanggung,
            'status' => $polis->status_approval,
            'catatan_revisi' => $polis->keterangan_audit,
            'paraf_oleh' => $polis->paraf_oleh,
            'updated_at' => $polis->updated_at?->toDateTimeString(),
            'target_url' => route($routeNames[strtoupper($type)], ['search' => $polis->no_polis]),
        ]));

        return back()->with('success', $message);
    }

    // Halaman History Persetujuan & Audit Trail (Menampilkan SELURUH total polis terinput)
    public function history(Request $request)
    {
        // Mengambil seluruh data master dari fungsi helper tanpa pembatasan status
        $history = $this->getAllRegisters();

        if ($request->filled('jenis_polis')) {
            $history = $history->where('jenis_polis', $request->jenis_polis);
        }

        return Inertia::render('ERegist/KepalaStaff/History', [
            'historyData' => $history->values(),
            'filters' => $request->only(['jenis_polis', 'search', 'tanggal', 'bulan', 'tahun'])
        ]);
    }
}