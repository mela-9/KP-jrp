<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PolisQueue;
use App\Models\SuratStockDetail;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Statistik chart berdasarkan jenis polis di antrian
        $chartStats = PolisQueue::select('jenis_polis', \DB::raw('count(*) as total'))
            ->groupBy('jenis_polis')
            ->get();

        // Daily Reminder & Activity Log berdasarkan waktu hari ini
        $doneToday = PolisQueue::whereDate('created_at', $today)->get();
        $inProgress = PolisQueue::where('status_antrian', 'SIAP_DISETUJUI')->get();
        $lowStockWarning = SuratStockDetail::where('status', 'AVAILABLE')->count();

        return Inertia::render('Dashboard', [
            'chartStats' => $chartStats,
            'reminders' => [
                'done_today' => $doneToday,
                'in_progress' => $inProgress,
                'low_stock_count' => $lowStockWarning,
                'current_date' => $today->translatedFormat('l, d F Y'),
            ]
        ]);
    }
}