<?php

namespace App\Http\Controllers;

use App\Models\AkdRegister;
use App\Models\ParRegister;
use App\Models\PublicLiabilityRegister;
use App\Models\SuretyBondRegister;
use App\Models\VariaRegister;
use App\Models\VehicleRegister;
use App\Events\RegisterNotification;
use Illuminate\Http\Request;

class ApprovalWorkflowController extends Controller
{
    private const MODELS = [
        'AKD' => AkdRegister::class,
        'PAR' => ParRegister::class,
        'VEHICLE' => VehicleRegister::class,
        'VARIA' => VariaRegister::class,
        'PL' => PublicLiabilityRegister::class,
        'SURETY' => SuretyBondRegister::class,
    ];

    private const EDITABLE_STATUSES = ['Draft Staf', 'Revisi Staf'];

    private function findRegister(string $type, int $id)
    {
        $model = self::MODELS[strtoupper($type)] ?? null;
        abort_unless($model, 404);

        return $model::findOrFail($id);
    }

    private function ensureStaff(Request $request): void
    {
        abort_if(
            $request->user()->role === 'kepala_staff' || $request->user()->email === 'kepstaff123@gmail.com',
            403
        );
    }

    private function ensureOwner(Request $request, $register): void
    {
        abort_unless((int) $register->created_by === (int) $request->user()->id, 403);
    }

    public function updateDraft(Request $request, string $type, int $id)
    {
        $this->ensureStaff($request);
        $register = $this->findRegister($type, $id);
        if ($register->status_approval === 'Draft Staf') {
            $this->ensureOwner($request, $register);
        }
        abort_unless(in_array($register->status_approval, self::EDITABLE_STATUSES, true), 409);

        $rules = match (strtoupper($type)) {
            'AKD' => [
                'nama_tertanggung' => 'required|string|max:255',
                'no_polis' => "required|string|max:255|unique:akd_registers,no_polis,{$id}",
                'periode_awal' => 'required|date',
                'periode_akhir' => 'required|date',
                'tsi_ab' => 'nullable|numeric',
                'premi' => 'nullable|numeric',
                'scan_polis' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ],
            'PAR' => [
                'tertanggung' => 'required|string',
                'no_polis' => "required|string|max:255|unique:par_registers,no_polis,{$id}",
                'periode_awal' => 'required|date',
                'periode_akhir' => 'required|date',
                'tsi' => 'nullable|numeric',
                'premi' => 'nullable|numeric',
                'share' => 'nullable|string|max:50',
                'agen' => 'nullable|string|max:255',
            ],
            'VEHICLE' => [
                'nama_tertanggung' => 'required|string|max:255',
                'alamat_tertanggung' => 'required|string',
                'no_polis' => "required|string|max:255|unique:vehicle_registers,no_polis,{$id}",
                'no_polisi' => 'required|string|max:50',
                'merk_tipe' => 'required|string|max:255',
                'no_rangka_mesin' => 'required|string|max:255',
                'penggunaan' => 'nullable|string|max:100',
                'periode_awal' => 'required|date',
                'periode_akhir' => 'required|date',
                'tsi_casco' => 'nullable|numeric',
                'tsi_tjh' => 'nullable|numeric',
                'total_premi' => 'nullable|numeric',
                'biaya_admin' => 'nullable|numeric',
                'scan_polis' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ],
            'VARIA' => [
                'tertanggung' => 'required|string',
                'cob_toc' => 'required|string|max:255',
                'no_polis' => "required|string|max:255|unique:varia_registers,no_polis,{$id}",
                'periode_awal' => 'required|date',
                'periode_akhir' => 'required|date',
                'tsi' => 'nullable|numeric',
                'premi' => 'nullable|numeric',
                'share' => 'nullable|string|max:50',
                'agen' => 'nullable|string|max:255',
            ],
            'PL' => [
                'tertanggung' => 'required|string',
                'cob_toc' => 'required|string|max:255',
                'no_polis' => "required|string|max:255|unique:public_liability_registers,no_polis,{$id}",
                'periode_awal' => 'required|date',
                'periode_akhir' => 'required|date',
                'tsi' => 'nullable|numeric',
                'premi' => 'nullable|numeric',
                'share' => 'nullable|string|max:50',
            ],
            'SURETY' => [
                'tertanggung' => 'required|string',
                'cob_toc' => 'required|string|max:255',
                'no_polis' => "required|string|max:255|unique:surety_bond_registers,no_polis,{$id}",
                'periode_awal' => 'required|date',
                'periode_akhir' => 'required|date',
                'tsi' => 'nullable|numeric',
                'premi' => 'nullable|numeric',
                'share' => 'nullable|string|max:50',
            ],
            default => abort(404),
        };

        $validated = $request->validate($rules);
        if ($request->hasFile('scan_polis')) {
            $folder = strtolower($type) . '_scan';
            $path = $request->file('scan_polis')->store($folder, 'public');
            $validated['scan_polis'] = $path;
        }

        $register->fill($validated);
        $register->status_approval = $register->status_approval === 'Revisi Staf'
            ? 'Revisi Staf'
            : 'Draft Staf';
        $register->save();

        return back()->with('success', 'Data polis berhasil diperbarui sebagai draft.');
    }

    public function requestApproval(Request $request, string $type, int $id)
    {
        $this->ensureStaff($request);
        $register = $this->findRegister($type, $id);
        if ($register->status_approval === 'Draft Staf') {
            $this->ensureOwner($request, $register);
        }
        abort_unless(in_array($register->status_approval, self::EDITABLE_STATUSES, true), 409);

        $register->update(['status_approval' => 'Pending Kepala Staff']);

        event(new RegisterNotification('notifications.kepala-staff', [
            'id' => strtoupper($type) . '-' . $register->id,
            'type' => strtoupper($type),
            'no_polis' => $register->no_polis,
            'nama_tertanggung' => $register->nama_tertanggung ?? $register->tertanggung,
            'status' => $register->status_approval,
            'updated_at' => $register->updated_at?->toDateTimeString(),
            'target_url' => route('kepala-staff.approvals', ['search' => $register->no_polis]),
        ]));

        return back()->with('success', 'Polis berhasil dikirim ke antrean validasi Kepala Staff.');
    }
}