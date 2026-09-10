import * as XLSX from 'xlsx';
import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router, usePage } from '@inertiajs/react';

export default function PublicLiabilityIndex({ auth, plData = [], stats = { total: 0, pending_paraf: 0, siap_serah: 0, selesai: 0 } }) {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [isTerimaModalOpen, setIsTerimaModalOpen] = useState(false);
    const [selectedPlId, setSelectedPlId] = useState(null);

    const role = auth.user?.role;

    const { data, setData, post, processing, reset } = useForm({
        tgl_input: '',
        cob_toc: '',
        tertanggung: '',
        no_polis: '',
        periode_awal: '',
        periode_akhir: '',
        tsi: '',
        share: '100%',
        premi: '',
        jatuh_tempo: '',
        agen: '',
        pic: '',
        scan_polis: null
    });

    const handlePrint = () => window.print();

    const handleExport = () => {
        if (!plData || plData.length === 0) {
            alert("Belum ada data Public Liability untuk diekspor!");
            return;
        }

        const dataToExport = plData.map((item, index) => ({
            "No": index + 1,
            "Tanggal Input": item.tgl_input,
            "COB / TOC": item.cob_toc,
            "Tertanggung & Alamat": item.tertanggung,
            "No. Polis / Cert": item.no_polis,
            "Periode Awal": item.periode_awal,
            "Periode Akhir": item.periode_akhir,
            "TSI (Rp)": Number(item.tsi),
            "Share (%)": item.share,
            "Premi (Rp)": Number(item.premi),
            "Jatuh Tempo": item.jatuh_tempo,
            "Agen / Broker": item.agen,
            "PIC Branch": item.pic,
            "Status Approval": item.status_approval,
            "Status Serah Terima": item.status_serah_terima,
        }));

        const worksheet = XLSX.utils.json_to_sheet(dataToExport);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Buku Besar PL");
        XLSX.writeFile(workbook, "E_Register_Public_Liability_Export.xlsx");
    };

    const submitForm = (e) => {
        e.preventDefault();
        post(route('pl.store'), {
            forceFormData: true,
            onSuccess: () => {
                setIsModalOpen(false);
                reset();
            },
        });
    };

    const submitKonfirmasiTerima = (e) => {
        e.preventDefault();
        
        const fileInput = document.getElementById('file_upload_pl_terima');
        if (!fileInput || !fileInput.files[0]) {
            alert('Pilih file bukti terima terlebih dahulu!');
            return;
        }

        const formData = new FormData();
        formData.append('bukti_terima', fileInput.files[0]);

        router.post(route('pl.terima', selectedPlId), formData, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setIsTerimaModalOpen(false);
                setSelectedPlId(null);
            },
            onError: () => {
                alert('Gagal mengunggah file. Pastikan format PDF/Gambar maksimal 2MB.');
            }
        });
    };

    const getRoleBadge = () => {
        if (role === 'staff') return <span className="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">Mode: Staff (Operasional)</span>;
        if (role === 'kepala_staff') return <span className="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-0.5 rounded">Mode: Kepala Staff (Otorisasi)</span>;
        if (role === 'agen') return <span className="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-0.5 rounded">Mode: Agen (Lapangan)</span>;
        return null;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">E-Register Polis: Public Liability (PL) & Pelayanan Umum</h2>
                    {getRoleBadge()}
                </div>
            }
        >
            <Head title="E-Register Public Liability" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    {usePage().props.flash?.success && (
                        <div className="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded relative shadow-sm">
                            <span className="block sm:inline font-medium text-sm">🔔 {usePage().props.flash.success}</span>
                        </div>
                    )}

                    {/* WIDGET DASHBOARD STATISTIK */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 print:hidden">
                        <div className="bg-white p-5 rounded-lg shadow-sm border-l-4 border-blue-500">
                            <div className="text-sm font-medium text-gray-500">Total Buku Besar PL</div>
                            <div className="text-2xl font-bold text-gray-800 mt-1">{stats.total} Polis</div>
                            <div className="text-xs text-gray-400 mt-1">Seluruh data tercatat di sistem</div>
                        </div>

                        <div className={`bg-white p-5 rounded-lg shadow-sm border-l-4 ${role === 'kepala_staff' ? 'border-amber-500 bg-amber-50/30' : 'border-purple-500'}`}>
                            <div className="text-sm font-medium text-gray-500">Menunggu Paraf (Pending)</div>
                            <div className="text-2xl font-bold text-amber-600 mt-1">{stats.pending_paraf} Polis</div>
                            <div className="text-xs text-gray-400 mt-1">
                                {role === 'kepala_staff' ? '⚠️ Butuh tindakan paraf digital segera!' : 'Belum disetujui Kepala Staff'}
                            </div>
                        </div>

                        <div className={`bg-white p-5 rounded-lg shadow-sm border-l-4 ${role === 'agen' ? 'border-emerald-500 bg-emerald-50/30' : 'border-emerald-500'}`}>
                            <div className="text-sm font-medium text-gray-500">Siap Diterima / Proses</div>
                            <div className="text-2xl font-bold text-emerald-600 mt-1">{stats.siap_serah} Polis</div>
                            <div className="text-xs text-gray-400 mt-1">
                                {role === 'agen' ? '📋 Polis siap diambil & upload tanda terima' : 'Dalam proses penyerahan lapangan'}
                            </div>
                        </div>
                    </div>

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                        {/* Header & Tombol Aksi */}
                        <div className="flex justify-between items-center mb-4 print:hidden">
                            <div>
                                <h3 className="text-lg font-bold text-gray-700">Buku Besar Digital Public Liability</h3>
                            </div>
                            <div className="space-x-2 flex">
                                <button onClick={handleExport} className="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded text-xs">
                                    Export Excel
                                </button>
                                <button onClick={handlePrint} className="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-xs">
                                    Print / PDF
                                </button>
                                {role === 'staff' && (
                                    <button onClick={() => setIsModalOpen(true)} className="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded text-xs shadow-md">
                                        + Input Polis PL
                                    </button>
                                )}
                            </div>
                        </div>

                        {/* Tabel Data PL */}
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm text-left text-gray-500 border border-gray-200">
                                <thead className="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr>
                                        <th className="px-3 py-3 border-b">Tgl Input</th>
                                        <th className="px-3 py-3 border-b">COB / TOC</th>
                                        <th className="px-3 py-3 border-b">Tertanggung & Alamat</th>
                                        <th className="px-3 py-3 border-b">No. Polis</th>
                                        <th className="px-3 py-3 border-b">Periode</th>
                                        <th className="px-3 py-3 border-b">TSI & Share</th>
                                        <th className="px-3 py-3 border-b">Premi</th>
                                        <th className="px-3 py-3 border-b">Jatuh Tempo & Agen</th>
                                        <th className="px-3 py-3 border-b">Status Workflow</th>
                                        <th className="px-3 py-3 border-b text-center">Aksi / Kontrol</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {plData && plData.length > 0 ? (
                                        plData.map((item) => (
                                            <tr key={item.id} className="bg-white border-b hover:bg-gray-50">
                                                <td className="px-3 py-4">{item.tgl_input}</td>
                                                <td className="px-3 py-4 font-semibold text-blue-600">{item.cob_toc}</td>
                                                <td className="px-3 py-4 font-medium text-gray-900 max-w-xs">{item.tertanggung}</td>
                                                <td className="px-3 py-4 text-xs font-semibold">{item.no_polis}</td>
                                                <td className="px-3 py-4 text-xs">{item.periode_awal} s/d <br/>{item.periode_akhir}</td>
                                                <td className="px-3 py-4 text-xs">
                                                    <div className="font-bold">Rp {Number(item.tsi).toLocaleString('id-ID')}</div>
                                                    <div className="text-gray-400">Share: {item.share}</div>
                                                </td>
                                                <td className="px-3 py-4 font-semibold text-gray-800">Rp {Number(item.premi).toLocaleString('id-ID')}</td>
                                                <td className="px-3 py-4 text-xs">
                                                    <div>Tempo: {item.jatuh_tempo}</div>
                                                    <div className="text-blue-600">{item.agen} ({item.pic})</div>
                                                </td>
                                                
                                                {/* Kolom Workflow Status */}
                                                <td className="px-3 py-4 text-xs">
                                                    <div className="font-semibold text-purple-600">Approval: {item.status_approval || 'Pending Kepala Staff'}</div>
                                                    {item.paraf_timestamp && <div className="text-gray-400">Paraf: {item.paraf_oleh}</div>}
                                                    
                                                    <div className="font-semibold text-orange-600 mt-1">Serah Terima: {item.status_serah_terima || 'Belum Diserahkan'}</div>
                                                    
                                                    {item.bukti_terima ? (
                                                        <div className="mt-1 bg-emerald-50 p-1.5 rounded border border-emerald-200">
                                                            <span className="text-emerald-700 font-bold block">✓ Diterima Tertanggung</span>
                                                            <span className="text-gray-500 text-[10px]">Tgl: {item.tanggal_terima}</span>
                                                            <div className="mt-1">
                                                                <a 
                                                                    href={`/storage/${item.bukti_terima}`} 
                                                                    target="_blank" 
                                                                    rel="noopener noreferrer" 
                                                                    className="text-blue-600 underline font-semibold hover:text-blue-800 text-[10px]"
                                                                >
                                                                    🔍 Lihat Delivery Receipt
                                                                </a>
                                                            </div>
                                                        </div>
                                                    ) : (
                                                        <div className="text-gray-400 italic text-[10px] mt-1">Belum ada Delivery Receipt</div>
                                                    )}
                                                </td>

                                                {/* Kolom Aksi Berdasarkan Role */}
                                                <td className="px-3 py-4 text-center align-middle">
                                                    {role === 'staff' && (
                                                        <div className="space-y-1.5">
                                                            {(!item.status_approval || item.status_approval.includes('Pending')) && (
                                                                <button onClick={() => router.post(route('pl.mintaApproval', item.id))} className="bg-purple-600 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-purple-700 w-full">
                                                                    📤 Minta Approval
                                                                </button>
                                                            )}
                                                            {item.status_approval === 'Diparaf Kepala Staff' && item.status_serah_terima === 'Belum Diserahkan' && (
                                                                <button onClick={() => router.post(route('pl.kirimKeAgen', item.id))} className="bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-blue-700 w-full">
                                                                    🚀 Kirim ke Agen
                                                                </button>
                                                            )}
                                                        </div>
                                                    )}

                                                    {role === 'kepala_staff' && (!item.status_approval || item.status_approval.includes('Pending')) && (
                                                        <button onClick={() => router.post(route('pl.paraf', item.id))} className="bg-amber-600 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-amber-700 w-full">
                                                            ✍️ Berikan Paraf
                                                        </button>
                                                    )}

                                                    {role === 'agen' && item.status_approval === 'Diparaf Kepala Staff' && item.status_serah_terima !== 'Diterima Tertanggung' && (
                                                        <button onClick={() => { setSelectedPlId(item.id); setIsTerimaModalOpen(true); }} className="bg-emerald-600 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-emerald-700 w-full">
                                                            📥 Konfirmasi Diterima
                                                        </button>
                                                    )}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr><td colSpan="10" className="px-4 py-8 text-center text-gray-500">Belum ada data polis Public Liability yang diinput.</td></tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                {/* MODAL FORM INPUT PUBLIC LIABILITY */}
                {isModalOpen && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 print:hidden">
                        <div className="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto p-6">
                            <div className="flex justify-between items-center mb-4 border-b pb-2">
                                <h3 className="text-lg font-bold text-gray-800">Form Input Manual Public Liability (PL)</h3>
                                <button onClick={() => setIsModalOpen(false)} className="text-gray-500 hover:text-red-500 font-bold text-xl">&times;</button>
                            </div>
                            
                            <form onSubmit={submitForm}>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-3">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Tanggal Input</label>
                                            <input type="date" required value={data.tgl_input} onChange={e => setData('tgl_input', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">COB / TOC (Jenis Polis)</label>
                                            <input type="text" required value={data.cob_toc} onChange={e => setData('cob_toc', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="Contoh: Public Liability (GI)" />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Nama Tertanggung & Alamat</label>
                                            <textarea required rows="2" value={data.tertanggung} onChange={e => setData('tertanggung', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="Contoh: Amanzi Waterpark..." />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">No. Polis / Cert No.</label>
                                            <input type="text" required value={data.no_polis} onChange={e => setData('no_polis', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="110000901122500021-000005" />
                                        </div>
                                        <div className="grid grid-cols-2 gap-2">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Periode Awal</label>
                                                <input type="date" required value={data.periode_awal} onChange={e => setData('periode_awal', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Periode Akhir</label>
                                                <input type="date" required value={data.periode_akhir} onChange={e => setData('periode_akhir', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" />
                                            </div>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Upload Scan Polis (PDF/Foto)</label>
                                            <input type="file" onChange={e => setData('scan_polis', e.target.files[0])} className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                        </div>
                                    </div>

                                    <div className="space-y-3 bg-gray-50 p-3 rounded-lg border">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Total TSI (Angka Saja)</label>
                                            <input type="number" value={data.tsi} onChange={e => setData('tsi', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="15000000" />
                                        </div>
                                        <div className="grid grid-cols-2 gap-2">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Share (%)</label>
                                                <input type="text" value={data.share} onChange={e => setData('share', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="100%" />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Jumlah Premi (Rp)</label>
                                                <input type="number" value={data.premi} onChange={e => setData('premi', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="1500000" />
                                            </div>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Tgl Jatuh Tempo Pembayaran</label>
                                            <input type="date" value={data.jatuh_tempo} onChange={e => setData('jatuh_tempo', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" />
                                        </div>
                                        <div className="grid grid-cols-2 gap-2">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Agen / Direct</label>
                                                <input type="text" value={data.agen} onChange={e => setData('agen', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="Direct" />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">PIC Branch</label>
                                                <input type="text" value={data.pic} onChange={e => setData('pic', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="Rifqi" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div className="mt-6 flex justify-end space-x-3 border-t pt-4">
                                    <button type="button" onClick={() => setIsModalOpen(false)} className="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded shadow-sm hover:bg-gray-50">Batal</button>
                                    <button type="submit" disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded shadow-sm hover:bg-blue-700 disabled:opacity-50">
                                        {processing ? 'Menyimpan...' : 'Simpan Data PL'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}

                {/* MODAL UPLOAD DELIVERY RECEIPT PL (KHUSUS AGEN) */}
                {isTerimaModalOpen && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div className="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                            <h3 className="text-lg font-bold text-gray-800 mb-2">Upload Delivery Receipt (PL)</h3>
                            <p className="text-xs text-gray-500 mb-4">Unggah lembar Berita Acara / Tanda Terima fisik untuk mengonfirmasi penyerahan polis Public Liability.</p>
                            
                            <form onSubmit={submitKonfirmasiTerima}>
                                <div className="mb-4">
                                    <input 
                                        type="file" 
                                        id="file_upload_pl_terima"
                                        required 
                                        className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" 
                                    />
                                </div>

                                <div className="flex justify-end space-x-2">
                                    <button 
                                        type="button" 
                                        onClick={() => setIsTerimaModalOpen(false)} 
                                        className="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-300"
                                    >
                                        Batal
                                    </button>
                                    <button 
                                        type="submit" 
                                        className="bg-emerald-600 text-white px-4 py-2 rounded text-sm font-bold"
                                    >
                                        Konfirmasi & Unggah
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}

            </div>
        </AuthenticatedLayout>
    );
}