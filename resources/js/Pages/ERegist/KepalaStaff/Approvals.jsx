import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function Approvals({ auth, pendingData, filters }) {
    const [activeDropdown, setActiveDropdown] = useState(null);
    const [showRejectModal, setShowRejectModal] = useState(false);
    const [showDetailModal, setShowDetailModal] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);
    const [alasanKategori, setAlasanKategori] = useState('Dokumen Scan Kurang Jelas');
    const [catatanRevisi, setCatatanRevisi] = useState('');

    const [searchQuery, setSearchQuery] = useState(filters?.search || '');
    const [selectedLini, setSelectedLini] = useState(filters?.jenis_polis || '');
    const [selectedTanggal, setSelectedTanggal] = useState(filters?.tanggal || '');
    const [selectedBulan, setSelectedBulan] = useState(filters?.bulan || '');
    const [selectedTahun, setSelectedTahun] = useState(filters?.tahun || '');

    const handleFilterChange = (field, value) => {
        const query = { 
            search: field === 'search' ? value : searchQuery, 
            jenis_polis: field === 'jenis_polis' ? value : selectedLini,
            tanggal: field === 'tanggal' ? value : selectedTanggal,
            bulan: field === 'bulan' ? value : selectedBulan,
            tahun: field === 'tahun' ? value : selectedTahun,
        };
        
        if (field === 'search') setSearchQuery(value);
        if (field === 'jenis_polis') setSelectedLini(value);
        if (field === 'tanggal') setSelectedTanggal(value);
        if (field === 'bulan') setSelectedBulan(value);
        if (field === 'tahun') setSelectedTahun(value);

        router.get(route('kepala-staff.approvals'), query, { preserveState: true, replace: true });
    };

    const filteredData = pendingData?.filter((item) => {
        const keyword = searchQuery.toLowerCase();
        const tertanggung = (item.nama_tertanggung || item.tertanggung || '').toLowerCase();
        const noPolis = (item.no_polis || '').toLowerCase();
        const noSurat = (item.no_surat || '').toLowerCase();

        const matchesSearch = tertanggung.includes(keyword) || noPolis.includes(keyword) || noSurat.includes(keyword);
        const matchesLini = !selectedLini || item.jenis_polis === selectedLini;

        const itemDate = item.tgl_input || '';
        const [iYear, iMonth, iDay] = itemDate.split('-');

        const matchesTanggal = !selectedTanggal || iDay === selectedTanggal;
        const matchesBulan = !selectedBulan || iMonth === selectedBulan;
        const matchesTahun = !selectedTahun || iYear === selectedTahun;

        return matchesSearch && matchesLini && matchesTanggal && matchesBulan && matchesTahun;
    }) || [];

    const handleApprove = (item) => {
        router.post(route('kepala-staff.update-approval', { type: item.jenis_polis, id: item.id }), {
            status: 'Disetujui',
        }, {
            onSuccess: () => setActiveDropdown(null)
        });
    };

    const openDetailModal = (item) => {
        setSelectedItem(item);
        setActiveDropdown(null);
        setShowDetailModal(true);
    };

    const openRejectModal = (item) => {
        setSelectedItem(item);
        setActiveDropdown(null);
        setAlasanKategori('Dokumen Scan Kurang Jelas');
        setCatatanRevisi('');
        setShowRejectModal(true);
    };

    const handleRejectSubmit = (e) => {
    e.preventDefault();

    if (!selectedItem) return;

    router.post(
        route('kepala-staff.update-approval', { 
            type: selectedItem.jenis_polis, 
            id: selectedItem.id 
        }),
        {
            status: 'Ditolak',
            alasan_penolakan: `[${alasanKategori}] ${catatanRevisi}`,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                setShowRejectModal(false);
                setSelectedItem(null);
                setCatatanRevisi(''); // Reset isi textarea
                setAlasanKategori('Dokumen Scan Kurang Jelas'); // Reset dropdown
            },
            onError: (errors) => {
                console.error('Gagal mengirim revisi:', errors);
            }
        }
    );
};

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-slate-800">Panel Validasi Pimpinan: Antrean Approval</h2>}>
            <Head title="Antrean Approval Korporat" />

            <div className="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                
                {/* FILTER & SEARCH SECTION YANG DIPERCANTIK (GRID LAYOUT) */}
                <div className="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 space-y-4">
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        
                        {/* Filter Lini Produk */}
                        <div>
                            <label className="block text-[11px] font-bold uppercase text-slate-500 mb-1.5">Lini Produk</label>
                            <select 
                                value={selectedLini} 
                                onChange={(e) => handleFilterChange('jenis_polis', e.target.value)}
                                className="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-slate-50 font-medium focus:ring-2 focus:ring-blue-900"
                            >
                                <option value="">Semua Lini Produk</option>
                                <option value="AKD">AKD</option>
                                <option value="PAR">PAR</option>
                                <option value="VEHICLE">Vehicle</option>
                                <option value="VARIA">Varia</option>
                                <option value="PL">Public Liability</option>
                                <option value="SURETY">Surety Bond</option>
                            </select>
                        </div>

                        {/* Filter Tanggal */}
                        <div>
                            <label className="block text-[11px] font-bold uppercase text-slate-500 mb-1.5">Tanggal</label>
                            <select 
                                value={selectedTanggal} 
                                onChange={(e) => handleFilterChange('tanggal', e.target.value)}
                                className="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-slate-50 font-medium focus:ring-2 focus:ring-blue-900"
                            >
                                <option value="">Semua Tanggal</option>
                                {[...Array(31)].map((_, i) => {
                                    const day = String(i + 1).padStart(2, '0');
                                    return <option key={day} value={day}>{day}</option>;
                                })}
                            </select>
                        </div>

                        {/* Filter Bulan */}
                        <div>
                            <label className="block text-[11px] font-bold uppercase text-slate-500 mb-1.5">Bulan</label>
                            <select 
                                value={selectedBulan} 
                                onChange={(e) => handleFilterChange('bulan', e.target.value)}
                                className="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-slate-50 font-medium focus:ring-2 focus:ring-blue-900"
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

                        {/* Filter Tahun */}
                        <div>
                            <label className="block text-[11px] font-bold uppercase text-slate-500 mb-1.5">Tahun</label>
                            <select 
                                value={selectedTahun} 
                                onChange={(e) => handleFilterChange('tahun', e.target.value)}
                                className="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-slate-50 font-medium focus:ring-2 focus:ring-blue-900"
                            >
                                <option value="">Semua Tahun</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                            </select>
                        </div>

                        {/* Kotak Pencarian */}
                        <div>
                            <label className="block text-[11px] font-bold uppercase text-slate-500 mb-1.5">Pencarian</label>
                            <input 
                                type="text"
                                placeholder="No. polis / surat / tertanggung..."
                                value={searchQuery}
                                onChange={(e) => handleFilterChange('search', e.target.value)}
                                className="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-slate-50 font-medium focus:ring-2 focus:ring-blue-900"
                            />
                        </div>

                    </div>

                    <div className="flex justify-between items-center pt-3 border-t border-slate-100 text-xs">
                        <span className="text-slate-500 font-medium">
                            Menampilkan: <strong className="text-blue-900">{filteredData.length}</strong> dari {pendingData?.length || 0} Berkas Antrean
                        </span>
                        {(selectedLini || selectedTanggal || selectedBulan || selectedTahun || searchQuery) && (
                            <button 
                                onClick={() => router.get(route('kepala-staff.approvals'))}
                                className="text-blue-600 hover:text-blue-800 font-bold underline"
                            >
                                Reset Filter
                            </button>
                        )}
                    </div>
                </div>

                {/* Tabel Antrean */}
                <div className="bg-white rounded-2xl shadow-xs border border-slate-300 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-[11px] border-collapse min-w-[1300px]">
                            <thead>
                                <tr className="bg-blue-950 text-white uppercase tracking-wider text-[10px] text-center border-b border-blue-900">
                                    <th className="p-3 border-r border-blue-900 w-12">No.</th>
                                    <th className="p-3 border-r border-blue-900 w-28">Lini Produk</th>
                                    <th className="p-3 border-r border-blue-900 w-32">Tanggal Input</th>
                                    <th className="p-3 border-r border-blue-900 w-48">Tertanggung</th>
                                    <th className="p-3 border-r border-blue-900 w-44">No. Polis & Surat</th>
                                    <th className="p-3 border-r border-blue-900 w-36">Periode Polis</th>
                                    <th className="p-3 border-r border-blue-900 w-36">Premi / TSI (IDR)</th>
                                    <th className="p-3 border-r border-blue-900 w-28">Agen / Broker</th>
                                    <th className="p-3 border-r border-blue-900 w-32">Status Surat</th>
                                    <th className="p-3 border-r border-blue-900 w-28">Rincian</th>
                                    <th className="p-3 w-36">Aksi Validasi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-300 text-slate-800">
                                {filteredData && filteredData.length > 0 ? (
                                    filteredData.map((item, index) => (
                                        <tr key={`${item.jenis_polis}-${item.id}`} className="hover:bg-blue-50/50 transition">
                                            <td className="p-3 border-r border-slate-200 text-center font-medium bg-slate-50">{index + 1}</td>
                                            <td className="p-3 border-r border-slate-200 font-bold text-blue-900">{item.jenis_polis}</td>
                                            <td className="p-3 border-r border-slate-200 text-center font-mono">{item.tgl_input}</td>
                                            <td className="p-3 border-r border-slate-200 font-bold text-slate-900">{item.nama_tertanggung || item.tertanggung}</td>
                                            <td className="p-3 border-r border-slate-200 font-mono">
                                                <div className="text-blue-900 font-bold">{item.no_polis}</div>
                                                <div className="text-amber-700 text-[10px]">{item.no_surat}</div>
                                            </td>
                                            <td className="p-3 border-r border-slate-200 text-slate-600 text-[10px]">
                                                {item.periode_awal} s.d <br />{item.periode_akhir}
                                            </td>
                                            <td className="p-3 border-r border-slate-200 font-mono text-right">
                                                <div className="font-bold">Rp {Number(item.premi || item.total_premi || 0).toLocaleString('id-ID')}</div>
                                                <div className="text-[10px] text-slate-500">TSI: Rp {Number(item.tsi || item.tsi_ab || item.tsi_casco || 0).toLocaleString('id-ID')}</div>
                                            </td>
                                            <td className="p-3 border-r border-slate-200 text-center">{item.agen || item.sumber_bisnis || 'Direct'}</td>
                                            <td className="p-3 border-r border-slate-200 text-center">
                                                <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                                                    (item.kondisi_surat || 'Normal') === 'Normal' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                                                }`}>
                                                    {item.kondisi_surat || 'Normal'}
                                                </span>
                                            </td>

                                            <td className="p-3 border-r border-slate-200 text-center">
                                                <button 
                                                    onClick={() => openDetailModal(item)}
                                                    className="bg-blue-900 hover:bg-blue-800 text-white font-bold px-2.5 py-1 rounded text-[10px] transition shadow-xs"
                                                >
                                                    🔍 Rincian
                                                </button>
                                            </td>

                                            <td className="p-3 text-center relative">
                                                <div className="inline-block relative">
                                                    <button 
                                                        onClick={() => setActiveDropdown(activeDropdown === item.id ? null : item.id)}
                                                        className="bg-blue-900 hover:bg-blue-800 text-white font-bold px-3 py-1 rounded-lg text-xs shadow-xs transition flex items-center space-x-1"
                                                    >
                                                        <span>Validasi</span>
                                                        <span className="text-[10px]">▾</span>
                                                    </button>

                                                    {activeDropdown === item.id && (
                                                        <div className="absolute right-0 mt-1 w-40 bg-white border border-slate-200 rounded-xl shadow-xl z-20 overflow-hidden py-1 text-left">
                                                            <button 
                                                                onClick={() => handleApprove(item)}
                                                                className="w-full text-left px-4 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-50 flex items-center space-x-2 transition"
                                                            >
                                                                <span>✓</span>
                                                                <span>Setujui (Paraf)</span>
                                                            </button>
                                                            <button 
                                                                onClick={() => openRejectModal(item)}
                                                                className="w-full text-left px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50 flex items-center space-x-2 transition border-t border-slate-100"
                                                            >
                                                                <span>✕</span>
                                                                <span>Minta Revisi</span>
                                                            </button>
                                                        </div>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="11" className="p-8 text-center text-slate-400 italic">
                                            Tidak ada antrean dokumen yang sesuai dengan kriteria pencarian atau filter.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* MODAL RINCIAN LENGKAP POLIS */}
            {showDetailModal && selectedItem && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-xs">
                    <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto animate-in fade-in zoom-in duration-200">
                        <div className="flex justify-between items-center border-b pb-3">
                            <h3 className="font-bold text-sm text-blue-900 flex items-center space-x-2">
                                <span>📋 Rincian Parameter Polis: {selectedItem.jenis_polis}</span>
                            </h3>
                            <button onClick={() => setShowDetailModal(false)} className="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                        </div>

                        <div className="space-y-3 text-xs">
                            <div className="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
                                <div className="grid grid-cols-2 gap-2">
                                    <div><span className="font-semibold text-slate-500">No. Polis:</span> <div className="font-mono font-bold text-blue-900">{selectedItem.no_polis}</div></div>
                                    <div><span className="font-semibold text-slate-500">No. Surat Utama:</span> <div className="font-mono text-amber-700 font-bold">{selectedItem.no_surat}</div></div>
                                </div>
                                <div><span className="font-semibold text-slate-500">Nama Tertanggung:</span> <div className="font-bold text-slate-900">{selectedItem.nama_tertanggung || selectedItem.tertanggung}</div></div>
                                <div><span className="font-semibold text-slate-500">Periode Perlindungan:</span> <div className="font-medium">{selectedItem.periode_awal} s.d {selectedItem.periode_akhir}</div></div>
                                <div className="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200">
                                    <div><span className="font-semibold text-slate-500">Total Premi:</span> <div className="font-mono font-bold text-emerald-700">Rp {Number(selectedItem.premi || selectedItem.total_premi || 0).toLocaleString('id-ID')}</div></div>
                                    <div><span className="font-semibold text-slate-500">Nilai TSI:</span> <div className="font-mono font-bold text-slate-900">Rp {Number(selectedItem.tsi || selectedItem.tsi_ab || selectedItem.tsi_casco || 0).toLocaleString('id-ID')}</div></div>
                                </div>
                            </div>

                            <div>
                                <label className="block font-bold text-slate-700 mb-1">Daftar Array Lembar Nomor Surat Fisik:</label>
                                <div className="bg-slate-100 p-2.5 rounded-xl font-mono text-[11px] text-slate-700 border max-h-28 overflow-y-auto space-y-1">
                                    {selectedItem.nomor_surat_array && Array.isArray(selectedItem.nomor_surat_array) ? (
                                        selectedItem.nomor_surat_array.map((surat, idx) => (
                                            <div key={idx} className="flex justify-between items-center bg-white px-2 py-1 rounded border border-slate-200">
                                                <span>Halaman {idx + 1}:</span>
                                                <span className="font-bold text-blue-900">{surat}</span>
                                            </div>
                                        ))
                                    ) : (
                                        <div>{selectedItem.no_surat}</div>
                                    )}
                                </div>
                            </div>

                            <div className="flex justify-end pt-3 border-t">
                                <button 
                                    type="button" 
                                    onClick={() => setShowDetailModal(false)} 
                                    className="bg-blue-900 hover:bg-blue-800 text-white font-bold px-5 py-2 rounded-xl transition shadow-sm"
                                >
                                    Tutup Rincian
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* MODAL ALASAN PENOLAKAN / REVISI */}
            {showRejectModal && selectedItem && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-xs">
                    <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4 animate-in fade-in zoom-in duration-200">
                        <div className="flex justify-between items-center border-b pb-3">
                            <h3 className="font-bold text-sm text-red-600 flex items-center space-x-2">
                                <span>⚠️ Form Permintaan Ulang / Revisi ke Staf</span>
                            </h3>
                            <button onClick={() => setShowRejectModal(false)} className="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                        </div>

                        <form onSubmit={handleRejectSubmit} className="space-y-3 text-xs">
                            <div className="bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-1">
                                <div className="text-[11px]"><span className="font-semibold text-slate-500">Lini Produk:</span> <span className="font-bold text-blue-900">{selectedItem.jenis_polis}</span></div>
                                <div className="text-[11px]"><span className="font-semibold text-slate-500">No. Polis:</span> <span className="font-mono font-bold">{selectedItem.no_polis}</span></div>
                                <div className="text-[11px]"><span className="font-semibold text-slate-500">Tertanggung:</span> {selectedItem.nama_tertanggung || selectedItem.tertanggung}</div>
                            </div>

                            <div>
                                <label className="block font-bold text-slate-700 mb-1">Pilih Kategori Alasan Penolakan:</label>
                                <select 
                                    value={alasanKategori}
                                    onChange={e => setAlasanKategori(e.target.value)}
                                    className="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white font-medium focus:ring-2 focus:ring-blue-900"
                                >
                                    <option value="Dokumen Scan Kurang Jelas">Dokumen Scan Kurang Jelas / Buram</option>
                                    <option value="Kesalahan Input Nominal Premi/TSI">Kesalahan Input Nominal Premi / TSI</option>
                                    <option value="Ketidaksesuaian Periode Polis">Ketidaksesuaian Periode Tanggal Polis</option>
                                    <option value="Data Tertanggung Belum Lengkap">Data Identitas Tertanggung Belum Lengkap</option>
                                    <option value="Lainnya (Catatan Manual)">Lainnya (Tuliskan catatan di bawah)</option>
                                </select>
                            </div>

                            <div>
                                <label className="block font-bold text-slate-700 mb-1">Catatan Tambahan / Detail Revisi:</label>
                                <textarea 
                                    rows="3"
                                    value={catatanRevisi}
                                    onChange={e => setCatatanRevisi(e.target.value)}
                                    className="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-blue-900"
                                    placeholder="Tuliskan instruksi perbaikan khusus untuk staf underwriter..."
                                    required
                                ></textarea>
                            </div>

                            <div className="flex justify-end space-x-2 pt-3 border-t">
                                <button 
                                    type="button" 
                                    onClick={() => setShowRejectModal(false)} 
                                    className="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-4 py-2 rounded-xl transition"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="submit" 
                                    className="bg-red-600 hover:bg-red-700 text-white font-bold px-5 py-2 rounded-xl shadow-md transition"
                                >
                                    Kirim Permintaan Ulang ke Staf
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}