<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $notifications = [];
        $user = $request->user();
        $isKepalaStaff = $user && ($user->role === 'kepala_staff' || $user->email === 'kepstaff123@gmail.com');

        if ($user) {
            $registerTypes = [
                'AKD' => [\App\Models\AkdRegister::class, 'akd.index'],
                'PAR' => [\App\Models\ParRegister::class, 'par.index'],
                'VEHICLE' => [\App\Models\VehicleRegister::class, 'vehicle.index'],
                'VARIA' => [\App\Models\VariaRegister::class, 'varia.index'],
                'PL' => [\App\Models\PublicLiabilityRegister::class, 'pl.index'],
                'SURETY' => [\App\Models\SuretyBondRegister::class, 'surety.index'],
            ];

            foreach ($registerTypes as $type => [$model, $routeName]) {
                $query = $model::query();

                if ($isKepalaStaff) {
                    $query->where('status_approval', 'Pending Kepala Staff');
                } else {
                    $query->where(function ($query) use ($user) {
                        $query->where(function ($ownedQuery) use ($user) {
                            $ownedQuery->where('created_by', $user->id)
                                ->whereIn('status_approval', ['Diparaf Kepala Staff', 'Disetujui']);
                        })->orWhere('status_approval', 'Revisi Staf');
                    });
                }

                foreach ($query
                    ->orderByRaw("CASE WHEN status_approval = 'Revisi Staf' THEN 0 ELSE 1 END")
                    ->orderByDesc('updated_at')
                    ->take(10)
                    ->get() as $item) {
                    $notifications[] = [
                        'id' => $type . '-' . $item->id,
                        'type' => $type,
                        'no_polis' => $item->no_polis,
                        'nama_tertanggung' => $item->nama_tertanggung ?? $item->tertanggung,
                        'status' => $item->status_approval,
                        'catatan_revisi' => $item->keterangan_audit,
                        'paraf_oleh' => $item->paraf_oleh,
                        'updated_at' => $item->updated_at?->toDateTimeString(),
                        'target_url' => $isKepalaStaff
                            ? route('kepala-staff.approvals', ['search' => $item->no_polis])
                            : route($routeName, ['search' => $item->no_polis]),
                    ];
                }
            }

            usort($notifications, function ($a, $b) {
                $aIsRevision = $a['status'] === 'Revisi Staf';
                $bIsRevision = $b['status'] === 'Revisi Staf';
                if ($aIsRevision !== $bIsRevision) {
                    return $aIsRevision ? -1 : 1;
                }

                return strcmp($b['updated_at'] ?? '', $a['updated_at'] ?? '');
            });
            $notifications = array_slice($notifications, 0, 10);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'notifications' => $notifications,
            'isKepalaStaff' => $isKepalaStaff,
            'broadcastAuthEndpoint' => url('broadcasting/auth'),
        ];
    }
}