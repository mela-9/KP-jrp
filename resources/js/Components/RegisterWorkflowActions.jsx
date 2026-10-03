import React, { useState } from 'react';
import { router, useForm } from '@inertiajs/react';

const fieldsByType = {
    AKD: [
        { key: 'nama_tertanggung', label: 'Nama Tertanggung' },
        { key: 'no_polis', label: 'Nomor Polis' },
        { key: 'periode_awal', label: 'Periode Awal', type: 'date' },
        { key: 'periode_akhir', label: 'Periode Akhir', type: 'date' },
        { key: 'tsi_ab', label: 'TSI A/B', type: 'number' },
        { key: 'premi', label: 'Premi', type: 'number' },
    ],
    PAR: [
        { key: 'tertanggung', label: 'Nama Tertanggung' },
        { key: 'no_polis', label: 'Nomor Polis' },
        { key: 'periode_awal', label: 'Periode Awal', type: 'date' },
        { key: 'periode_akhir', label: 'Periode Akhir', type: 'date' },
        { key: 'tsi', label: 'TSI', type: 'number' },
        { key: 'premi', label: 'Premi', type: 'number' },
        { key: 'agen', label: 'Agen / Broker' },
    ],
    VEHICLE: [
        { key: 'nama_tertanggung', label: 'Nama Tertanggung' },
        { key: 'alamat_tertanggung', label: 'Alamat Tertanggung' },
        { key: 'no_polis', label: 'Nomor Polis' },
        { key: 'no_polisi', label: 'Nomor Polisi Kendaraan' },
        { key: 'merk_tipe', label: 'Merk / Tipe' },
        { key: 'no_rangka_mesin', label: 'Nomor Rangka / Mesin' },
        { key: 'penggunaan', label: 'Penggunaan' },
        { key: 'periode_awal', label: 'Periode Awal', type: 'date' },
        { key: 'periode_akhir', label: 'Periode Akhir', type: 'date' },
        { key: 'tsi_casco', label: 'TSI Casco', type: 'number' },
        { key: 'tsi_tjh', label: 'TSI TJH', type: 'number' },
        { key: 'total_premi', label: 'Total Premi', type: 'number' },
        { key: 'biaya_admin', label: 'Biaya Administrasi', type: 'number' },
    ],
    VARIA: [
        { key: 'tertanggung', label: 'Nama Tertanggung' },
        { key: 'cob_toc', label: 'COB / TOC' },
        { key: 'no_polis', label: 'Nomor Polis' },
        { key: 'periode_awal', label: 'Periode Awal', type: 'date' },
        { key: 'periode_akhir', label: 'Periode Akhir', type: 'date' },
        { key: 'tsi', label: 'TSI', type: 'number' },
        { key: 'premi', label: 'Premi', type: 'number' },
        { key: 'share', label: 'Share' },
        { key: 'agen', label: 'Agen / Broker' },
    ],
    PL: [
        { key: 'tertanggung', label: 'Nama Tertanggung' },
        { key: 'cob_toc', label: 'COB / TOC' },
        { key: 'no_polis', label: 'Nomor Polis' },
        { key: 'periode_awal', label: 'Periode Awal', type: 'date' },
        { key: 'periode_akhir', label: 'Periode Akhir', type: 'date' },
        { key: 'tsi', label: 'TSI', type: 'number' },
        { key: 'premi', label: 'Premi', type: 'number' },
        { key: 'share', label: 'Share' },
    ],
    SURETY: [
        { key: 'tertanggung', label: 'Nama Prinsipal / Tertanggung' },
        { key: 'cob_toc', label: 'Jenis Jaminan' },
        { key: 'no_polis', label: 'Nomor Polis' },
        { key: 'periode_awal', label: 'Periode Awal', type: 'date' },
        { key: 'periode_akhir', label: 'Periode Akhir', type: 'date' },
        { key: 'tsi', label: 'Nilai Jaminan', type: 'number' },
        { key: 'premi', label: 'Premi', type: 'number' },
        { key: 'share', label: 'Share' },
    ],
};

export default function RegisterWorkflowActions({ item, type }) {
    const [isEditing, setIsEditing] = useState(false);
    const fields = fieldsByType[type] || [];
    const { data, setData, put, processing, errors, clearErrors } = useForm({});
    const status = item.status_approval || 'Pending Kepala Staff';
    const canUpdate = status === 'Draft Staf' || status === 'Revisi Staf';

    const openEditor = () => {
        clearErrors();
        setData(Object.fromEntries(fields.map(({ key }) => [key, item[key] ?? ''])));
        setIsEditing(true);
    };

    const saveDraft = (event) => {
        event.preventDefault();
        put(route('register.draft.update', { type, id: item.id }), {
            preserveScroll: true,
            onSuccess: () => setIsEditing(false),
        });
    };

    const requestApproval = () => {
        router.post(route('register.approval.request', { type, id: item.id }), {}, {
            preserveScroll: true,
        });
    };

    return (
        <div className="flex flex-col items-center gap-1.5">
            {status === 'Diparaf Kepala Staff' || status === 'Disetujui' ? (
                <div className="text-emerald-700 font-bold">
                    <div>✓ Diparaf oleh Kepala Staff</div>
                    {item.paraf_oleh && <div className="text-[9px] font-medium">{item.paraf_oleh}</div>}
                </div>
            ) : status === 'Pending Kepala Staff' || status === 'Pending' ? (
                <span className="text-amber-700 font-bold">⏳ Menunggu Validasi</span>
            ) : (
                <span className={status === 'Revisi Staf' ? 'text-red-600 font-bold' : 'text-slate-600 font-bold'}>{status}</span>
            )}

            {canUpdate && (
                <div className="flex flex-wrap justify-center gap-1">
                    <button type="button" onClick={openEditor} className="rounded bg-slate-600 px-2 py-1 text-[10px] font-bold text-white hover:bg-slate-700">
                        Ubah Data
                    </button>
                    <button type="button" onClick={requestApproval} className="rounded bg-blue-800 px-2 py-1 text-[10px] font-bold text-white hover:bg-blue-900">
                        Minta Validasi
                    </button>
                </div>
            )}

            {isEditing && (
                <div className="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
                    <form onSubmit={saveDraft} className="max-h-[90vh] w-full max-w-xl space-y-4 overflow-y-auto rounded-lg bg-white p-5 shadow-2xl">
                        <div className="flex items-center justify-between border-b pb-3">
                            <h3 className="font-bold text-slate-900">Ubah Data Polis {item.no_polis}</h3>
                            <button type="button" onClick={() => setIsEditing(false)} className="px-2 text-xl font-bold text-slate-500" aria-label="Tutup">&times;</button>
                        </div>
                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            {fields.map(({ key, label, type: inputType = 'text' }) => (
                                <label key={key} className="block text-xs font-semibold text-slate-700">
                                    {label}
                                    <input
                                        type={inputType}
                                        value={data[key] ?? ''}
                                        onChange={(event) => setData(key, event.target.value)}
                                        required
                                        className="mt-1 w-full rounded border border-slate-300 p-2 text-xs font-normal"
                                    />
                                    {errors[key] && <span className="mt-1 block text-red-600">{errors[key]}</span>}
                                </label>
                            ))}
                        </div>
                        <div className="flex justify-end gap-2 border-t pt-3">
                            <button type="button" onClick={() => setIsEditing(false)} className="rounded border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700">
                                Batal
                            </button>
                            <button type="submit" disabled={processing} className="rounded bg-blue-900 px-3 py-2 text-xs font-bold text-white disabled:opacity-50">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </div>
    );
}