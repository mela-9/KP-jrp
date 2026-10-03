import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import RegisterWorkflowActions from '@/Components/RegisterWorkflowActions';

export default function Index({ auth, parData, autoNumbers, filters }) {
    const [showModal, setShowModal] = useState(false);
    const [showDetailModal, setShowDetailModal] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);
    const [isEditing, setIsEditing] = useState(false);

    // State untuk Search dan Filter Bulan
    const [search, setSearch] = useState(filters?.search || '');
    const [selectedMonth, setSelectedMonth] = useState(filters?.bulan || '');

    // Form state untuk Input Polis PAR Baru
    const { data, setData, post, processing, reset } = useForm({
        tgl_input: new Date().toISOString().split('T')[0],
        tertanggung: '',
        no_surat: autoNumbers?.no_surat || '',
        no_polis: autoNumbers?.no_polis || '',
        jumlah_halaman: 1,
        periode_awal: new Date().toISOString().split('T')[0],
        periode_akhir: new Date(new Date().setFullYear(new Date().getFullYear() + 1)).toISOString().split('T')[0],
        tsi: '',
        premi: '',
        agen: 'Direct',
        kondisi_surat: 'Normal',
    });

    // Form state untuk Modal Rincian & Audit Perubahan
    const { data: detailData, setData: setDetailData } = useForm({
        kondisi_surat: 'Normal',
        alasan_ubah: '',
        nomor_surat_array: [],
    });

    // Handler untuk Live Search
    const handleSearchChange = (e) => {
        const querySearch = e.target.value;
        setSearch(querySearch);
        router.get(
            window.location.pathname, 
            { search: querySearch, bulan: selectedMonth }, 
            { preserveState: true, replace: true }
        );
    };

    // Handler untuk Filter Bulan
    const handleMonthChange = (e) => {
        const queryBulan = e.target.value;
        setSelectedMonth(queryBulan);
        router.get(
            window.location.pathname,
            { search: search, bulan: queryBulan },
            { preserveState: true, replace: true }
        );
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('par.store'), {
            onSuccess: () => {
                setShowModal(false);
                reset();
            },
        });
    };

    const openDetailModal = (item) => {
        setSelectedItem(item);
        setIsEditing(false);
        setDetailData({
            kondisi_surat: item.kondisi_surat || 'Normal',
            alasan_ubah: item.keterangan_audit || '',
            nomor_surat_array: item.nomor_surat_array || [item.no_surat],
        });
        setShowDetailModal(true);
    };

    const handleChangeSubmit = (e) => {
        e.preventDefault();
        router.put(route('par.update-surat', selectedItem.id), {
            kondisi_surat: detailData.kondisi_surat,
            alasan_ubah: detailData.alasan_ubah,
        }, {
            onSuccess: () => {
                setShowDetailModal(false);
                setIsEditing(false);
            }
        });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-slate-800">Buku Besar: Property All Risk (PAR)</h2>}>
            <Head title="Buku Besar PAR" />

            <div className="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                
                {/* HEADER: Keterangan, Search, Filter, dan Tombol */}
                <div className="flex flex-col lg:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-xs border border-slate-200 gap-4">
                    {/* KIRI: Judul & Deskripsi */}
                    <div className="w-full lg:w-auto">
                        <h3 className="font-bold text-slate-800 text-sm">Register Fisik Digital - PAR</h3>
                        <p className="text-xs text-slate-500">Pencatatan berurutan sesuai alokasi gudang surat resmi PT Asuransi Jasaraharja Putera.</p>
                    </div>

                    {/* KANAN: Search, Filter, dan Button */}
                    <div className="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                        
                        {/* Search Input */}
                        <input
                            type="text"
                            placeholder="Cari No. Surat / Polis / Tertanggung..."
                            value={search}
                            onChange={handleSearchChange}
                            className="w-full sm:w-64 px-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                        />

                        {/* Wrapper Filter Bulan */}
                        <div className="flex items-center gap-2 w-full sm:w-auto">
                            <span className="text-sm text-slate-600 font-medium whitespace-nowrap hidden md:block">
                                Filter Bulan:
                            </span>
                            <select
                                value={selectedMonth}
                                onChange={handleMonthChange}
                                className="w-full sm:w-auto border border-slate-200 rounded-lg text-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Semua Bulan</option>
                                <option value="01">Januari</option>
                                <option value="02">Februari</option>
                                <option value="03">Maret</option>
                                <option value="04">April</option>
                                <option value="05">Mei</option>
                                <option value="06">Juni</option>
                                <option value="07">Juli</option>
                                <option value="08">Agustus</option>
                                <option value="09">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>

                        {/* Tombol Input Polis Baru */}
                        <button 
                            onClick={() => setShowModal(true)}
                            className="w-full sm:w-auto bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-xs transition whitespace-nowrap"
                        >
                            + Input Polis PAR Baru
                        </button>
                    </div>
                </div>

                {/* TABEL DATA */}
                <div className="bg-white rounded-xl shadow-xs border border-slate-300 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-[11px] border-collapse min-w-[1450px]">
                            <thead>
                                <tr className="bg-blue-950 text-white uppercase tracking-wider text-[10px] text-center border-b border-blue-900">
                                    <th className="p-2.5 border-r border-blue-900 w-12">No.</th>
                                    <th className="p-2.5 border-r border-blue-900 w-28">COB / TOC</th>
                                    <th className="p-2.5 border-r border-blue-900 w-48">Tertanggung & Alamat</th>
                                    <th className="p-2.5 border-r border-blue-900 w-44">No. CI / No. Polis</th>
                                    <th className="p-2.5 border-r border-blue-900 w-40">Periode</th>
                                    <th className="p-2.5 border-r border-blue-900 w-36">TSI (IDR)</th>
                                    <th className="p-2.5 border-r border-blue-900 w-20">Rate</th>
                                    <th className="p-2.5 border-r border-blue-900 w-32">Premi (IDR)</th>
                                    <th className="p-2.5 border-r border-blue-900 w-28">Agen / Broker</th>
                                    <th className="p-2.5 border-r border-blue-900 w-32">PIC Branch Office</th>
                                    <th className="p-2.5 border-r border-blue-900 w-36">Telp / HP / Email</th>
                                    <th className="p-2.5 border-r border-blue-900 w-36">No. Surat & Kertas</th>
                                    <th className="p-2.5">Status & Audit</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-300 text-slate-800">
                                {parData && parData.length > 0 ? (
                                    parData.map((item, index) => (
                                        <tr key={item.id} className="hover:bg-blue-50/50 transition">
                                            <td className="p-2.5 border-r border-slate-200 text-center font-medium bg-slate-50">{index + 1}</td>
                                            <td className="p-2.5 border-r border-slate-200 font-semibold text-blue-900">Property All Risk</td>
                                            <td className="p-2.5 border-r border-slate-200">
                                                <div className="font-bold text-slate-900">{item.tertanggung}</div>
                                                <div className="text-[10px] text-slate-500">Jl. Jend. Sudirman, Palembang</div>
                                            </td>
                                            <td className="p-2.5 border-r border-slate-200 font-mono">
                                                <div className="text-blue-900 font-bold">{item.no_polis}</div>
                                            </td>
                                            <td className="p-2.5 border-r border-slate-200 text-slate-600">
                                                {item.periode_awal} s.d <br />{item.periode_akhir}
                                            </td>
                                            <td className="p-2.5 border-r border-slate-200 font-mono font-bold text-right text-slate-900">
                                                Rp {Number(item.tsi).toLocaleString('id-ID')}
                                            </td>
                                            <td className="p-2.5 border-r border-slate-200 text-center font-semibold">{item.rate || '0.15%'}</td>
                                            <td className="p-2.5 border-r border-slate-200 font-mono font-bold text-right text-slate-900">
                                                Rp {Number(item.premi).toLocaleString('id-ID')}
                                            </td>
                                            <td className="p-2.5 border-r border-slate-200 text-center">{item.agen}</td>
                                            <td className="p-2.5 border-r border-slate-200 font-medium">Firzy Akbar</td>
                                            <td className="p-2.5 border-r border-slate-200 text-slate-500 text-[10px]">
                                                par.branch@jrp.co.id
                                            </td>
                                            
                                            {/* TOMBOL RINCIAN SURAT */}
                                            <td className="p-2.5 border-r border-slate-200 text-center">
                                                <button 
                                                    onClick={() => openDetailModal(item)}
                                                    className="bg-blue-900 hover:bg-blue-800 text-white font-bold px-3 py-1 rounded text-[11px] transition shadow-xs"
                                                >
                                                    🔍 Rincian Surat
                                                </button>
                                            </td>

                                            <td className="p-2.5 text-center font-mono text-[10px] bg-slate-50 space-y-1">
    <div>
        {item.kondisi_surat === 'Normal' && <span className="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded text-[10px] font-bold">Normal</span>}
        {item.kondisi_surat === 'Rusak' && <span className="bg-red-100 text-red-800 px-2 py-0.5 rounded text-[10px] font-bold">Rusak</span>}
        {item.kondisi_surat === 'Parsial' && <span className="bg-amber-100 text-amber-800 px-2 py-0.5 rounded text-[10px] font-bold">Parsial</span>}
    </div>
    
    {/* Status Approval yang Dinamis */}
    <RegisterWorkflowActions item={item} type="PAR" />
</td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="13" className="p-6 text-center text-slate-400 italic">Belum ada data PAR.</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* MODAL INPUT POLIS PAR BARU */}
            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div className="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                        <div className="flex justify-between items-center border-b pb-3">
                            <h3 className="font-bold text-base text-blue-900">📝 Pendaftaran Polis PAR Baru</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 font-bold text-xl">&times;</button>
                        </div>
                        <form onSubmit={submit} className="space-y-3 text-xs">
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block font-semibold mb-1">Tanggal Input</label>
                                    <input type="date" value={data.tgl_input} onChange={e => setData('tgl_input', e.target.value)} className="w-full border rounded-lg p-2 text-xs" required />
                                </div>
                                <div>
                                    <label className="block font-semibold mb-1">Nama Tertanggung</label>
                                    <input type="text" value={data.tertanggung} onChange={e => setData('tertanggung', e.target.value)} className="w-full border rounded-lg p-2 text-xs" required />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block font-semibold mb-1">Nomor Polis</label>
                                    <input type="text" value={data.no_polis} onChange={e => setData('no_polis', e.target.value)} className="w-full border rounded-lg p-2 text-xs font-mono bg-slate-50" required />
                                </div>
                                <div>
                                    <label className="block font-semibold mb-1">Nomor Surat Utama (Gudang)</label>
                                    <input type="text" value={data.no_surat} onChange={e => setData('no_surat', e.target.value)} className="w-full border rounded-lg p-2 text-xs font-mono bg-slate-50" required />
                                </div>
                            </div>

                            <div>
                                <label className="block font-semibold mb-1">Jumlah Halaman Surat Fisik</label>
                                <input type="number" min="1" max="5" value={data.jumlah_halaman} onChange={e => setData('jumlah_halaman', e.target.value)} className="w-full border rounded-lg p-2 text-xs" required />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block font-semibold mb-1">TSI (Rp)</label>
                                    <input type="number" value={data.tsi} onChange={e => setData('tsi', e.target.value)} className="w-full border rounded-lg p-2 text-xs" required />
                                </div>
                                <div>
                                    <label className="block font-semibold mb-1">Premi (Rp)</label>
                                    <input type="number" value={data.premi} onChange={e => setData('premi', e.target.value)} className="w-full border rounded-lg p-2 text-xs" required />
                                </div>
                            </div>

                            <div>
                                <label className="block font-semibold mb-1">Kondisi Lembar Surat</label>
                                <select value={data.kondisi_surat} onChange={e => setData('kondisi_surat', e.target.value)} className="w-full border rounded-lg p-2 text-xs bg-blue-50 font-bold">
                                    <option value="Normal">Normal</option>
                                    <option value="Rusak">Rusak (Void)</option>
                                    <option value="Parsial">Parsial (Salah Cetak Jalur)</option>
                                </select>
                            </div>

                            <div className="flex justify-end space-x-2 pt-2 border-t">
                                <button type="button" onClick={() => setShowModal(false)} className="bg-gray-200 px-3 py-2 rounded font-bold">Batal</button>
                                <button type="submit" disabled={processing} className="bg-blue-900 text-white px-3 py-2 rounded font-bold">Simpan & Potong Kuota</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* MODAL RINCIAN & AUDIT PERUBAHAN */}
            {showDetailModal && selectedItem && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div className="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                        <div className="flex justify-between items-center border-b pb-3">
                            <h3 className="font-bold text-base text-blue-900">📋 Rincian & Audit Nomor Surat PAR</h3>
                            <button onClick={() => setShowDetailModal(false)} className="text-gray-400 font-bold text-xl">&times;</button>
                        </div>

                        <div className="space-y-3 text-xs">
                            <div className="bg-slate-50 p-3 rounded-lg border space-y-1">
                                <div><span className="font-semibold text-slate-500">No. Polis:</span> <span className="font-mono font-bold text-blue-900">{selectedItem.no_polis}</span></div>
                                <div><span className="font-semibold text-slate-500">Tertanggung:</span> <span className="font-bold">{selectedItem.tertanggung}</span></div>
                                <div><span className="font-semibold text-slate-500">Status Saat Ini:</span> <span className="font-bold uppercase text-amber-700">{selectedItem.kondisi_surat}</span></div>
                                {selectedItem.keterangan_audit && <div><span className="font-semibold text-slate-500">Catatan Audit:</span> <span className="text-slate-700 italic">{selectedItem.keterangan_audit}</span></div>}
                            </div>

                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">Nomor Surat Utama / Array:</label>
                                <input type="text" value={selectedItem.no_surat} disabled className="w-full border rounded-lg p-2 text-xs font-mono bg-slate-100" />
                            </div>

                            {isEditing && (
                                <form onSubmit={handleChangeSubmit} className="space-y-3 border-t pt-3">
                                    <div>
                                        <label className="block font-semibold mb-1">Ubah Kondisi / Status</label>
                                        <select 
                                            value={detailData.kondisi_surat} 
                                            onChange={e => setDetailData('kondisi_surat', e.target.value)} 
                                            className="w-full border rounded-lg p-2 text-xs font-bold bg-amber-50"
                                        >
                                            <option value="Normal">Normal</option>
                                            <option value="Rusak">Rusak (Void / Kertas Robek)</option>
                                            <option value="Parsial">Parsial (Lintas Jalur)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block font-semibold mb-1">Alasan Perubahan / Catatan Audit</label>
                                        <input 
                                            type="text" 
                                            placeholder="Tuliskan alasan perubahan..." 
                                            value={detailData.alasan_ubah}
                                            onChange={e => setDetailData('alasan_ubah', e.target.value)}
                                            className="w-full border rounded-lg p-2 text-xs"
                                            required 
                                        />
                                    </div>

                                    <div className="flex justify-end space-x-2 pt-2">
                                        <button type="submit" className="bg-amber-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-amber-700">Simpan Perubahan (Change)</button>
                                    </div>
                                </form>
                            )}

                            <div className="flex justify-between items-center pt-3 border-t">
                                {!isEditing ? (
                                    <>
                                        <button 
                                            type="button" 
                                            onClick={() => setIsEditing(true)} 
                                            className="bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2 rounded-lg text-xs"
                                        >
                                            ⚙️ Change (Ubah Data)
                                        </button>
                                        <button 
                                            type="button" 
                                            onClick={() => setShowDetailModal(false)} 
                                            className="bg-blue-900 hover:bg-blue-800 text-white font-bold px-4 py-2 rounded-lg text-xs"
                                        >
                                            Tutup
                                        </button>
                                    </>
                                ) : (
                                    <button 
                                        type="button" 
                                        onClick={() => setIsEditing(false)} 
                                        className="bg-gray-300 text-gray-700 font-bold px-4 py-2 rounded-lg text-xs"
                                    >
                                        Batal Edit
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}