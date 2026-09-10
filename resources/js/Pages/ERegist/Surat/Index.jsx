import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';

export default function SuratIndex({ auth, suratData, stats, filters }) {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [search, setSearch] = useState(filters.search || '');

    const { data, setData, post, processing, reset } = useForm({
        kode_produk: '1101',
        prefix_surat: 'JRP-1101/2026/',
        nomor_awal: '',
        nomor_akhir: ''
    });

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('surat.index'), { search }, { preserveState: true });
    };

    const submitGenerate = (e) => {
        e.preventDefault();
        post(route('surat.store'), {
            onSuccess: () => {
                setIsModalOpen(false);
                reset();
            }
        });
    };

    const handleStatusChange = (id, newStatus) => {
        const ket = prompt("Masukkan keterangan (opsional, misal: Kertas rusak/salah cetak):");
        router.patch(route('surat.updateStatus', id), {
            status: newStatus,
            keterangan: ket
        }, { preserveScroll: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pusat Manajemen & Alokasi Nomor Surat (Lintas Polis)</h2>}>
            <Head title="Manajemen Nomor Surat" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    
                    {/* WIDGET STATISTIK STOK SURAT */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div className="bg-white p-5 rounded-xl shadow-sm border-l-4 border-blue-500">
                            <div className="text-xs font-bold text-gray-400 uppercase">Total Nomor Surat</div>
                            <div className="text-2xl font-black text-gray-800 mt-1">{stats.total}</div>
                        </div>
                        <div className="bg-white p-5 rounded-xl shadow-sm border-l-4 border-emerald-500">
                            <div className="text-xs font-bold text-gray-400 uppercase">Tersedia (Available)</div>
                            <div className="text-2xl font-black text-emerald-600 mt-1">{stats.available}</div>
                        </div>
                        <div className="bg-white p-5 rounded-xl shadow-sm border-l-4 border-purple-500">
                            <div className="text-xs font-bold text-gray-400 uppercase">Terpakai (Used)</div>
                            <div className="text-2xl font-black text-purple-600 mt-1">{stats.used}</div>
                        </div>
                        <div className="bg-white p-5 rounded-xl shadow-sm border-l-4 border-rose-500">
                            <div className="text-xs font-bold text-gray-400 uppercase">Rusak / Void (Damaged)</div>
                            <div className="text-2xl font-black text-rose-600 mt-1">{stats.damaged}</div>
                        </div>
                    </div>

                    {/* KOTAK KONTROL & TABEL */}
                    <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 space-y-4">
                        
                        <div className="flex flex-col md:flex-row justify-between items-center gap-4">
                            <form onSubmit={handleSearch} className="flex w-full md:w-96 space-x-2">
                                <input 
                                    type="text" 
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Cari nomor surat atau kode produk..." 
                                    className="w-full text-xs rounded-lg border-gray-300 shadow-sm"
                                />
                                <button type="submit" className="bg-gray-800 text-white px-4 py-2 rounded-lg text-xs font-bold">Cari</button>
                            </form>

                            <button onClick={() => setIsModalOpen(true)} className="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-xs shadow-md">
                                + Generate Rentang Nomor Surat
                            </button>
                        </div>

                        {/* TABEL STOK SURAT */}
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm text-left text-gray-500 border border-gray-200 rounded-lg">
                                <thead className="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 border-b">Kode Produk (4 Angka)</th>
                                        <th className="px-4 py-3 border-b">Nomor Surat Lengkap</th>
                                        <th className="px-4 py-3 border-b">Status</th>
                                        <th className="px-4 py-3 border-b">Keterangan / Catatan</th>
                                        <th className="px-4 py-3 border-b text-center">Aksi Kontrol</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {suratData.data.length > 0 ? (
                                        suratData.data.map((item) => (
                                            <tr key={item.id} className="bg-white border-b hover:bg-gray-50">
                                                <td className="px-4 py-3 font-mono font-bold text-gray-700">{item.kode_produk}</td>
                                                <td className="px-4 py-3 font-mono font-semibold text-blue-600">{item.nomor_surat}</td>
                                                <td className="px-4 py-3">
                                                    <span className={`px-2.5 py-1 rounded-full text-[10px] font-bold ${
                                                        item.status === 'AVAILABLE' ? 'bg-emerald-100 text-emerald-800' :
                                                        item.status === 'USED' ? 'bg-purple-100 text-purple-800' : 'bg-rose-100 text-rose-800'
                                                    }`}>
                                                        {item.status}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3 text-xs text-gray-500">{item.keterangan || '-'}</td>
                                                <td className="px-4 py-3 text-center space-x-1">
                                                    {item.status !== 'DAMAGED' && (
                                                        <button 
                                                            onClick={() => handleStatusChange(item.id, 'DAMAGED')}
                                                            className="bg-rose-50 text-rose-600 hover:bg-rose-100 px-2.5 py-1 rounded text-[11px] font-bold"
                                                        >
                                                            Tandai Rusak
                                                        </button>
                                                    )}
                                                    {item.status === 'DAMAGED' && (
                                                        <button 
                                                            onClick={() => handleStatusChange(item.id, 'AVAILABLE')}
                                                            className="bg-emerald-50 text-emerald-600 hover:bg-emerald-100 px-2.5 py-1 rounded text-[11px] font-bold"
                                                        >
                                                            Pulihkan Stok
                                                        </button>
                                                    )}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr><td colSpan="5" className="px-4 py-8 text-center text-gray-500">Belum ada data nomor surat yang terdaftar.</td></tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                {/* MODAL GENERATE NOMOR SURAT MASAL */}
                {isModalOpen && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm p-4">
                        <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                            <div className="flex justify-between items-center border-b pb-2">
                                <h3 className="text-base font-bold text-gray-800">Generate Rentang Nomor Surat</h3>
                                <button onClick={() => setIsModalOpen(false)} className="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                            </div>

                            <form onSubmit={submitGenerate} className="space-y-3">
                                <div>
                                    <label className="block text-xs font-medium text-gray-700">Kode Produk (4 Angka)</label>
                                    <select 
                                        value={data.kode_produk}
                                        onChange={e => {
                                            const val = e.target.value;
                                            setData({
                                                ...data,
                                                kode_produk: val,
                                                prefix_surat: val === 'OTHERS' ? '' : `JRP-${val}/2026/`
                                            });
                                        }}
                                        className="w-full text-xs rounded-md border-gray-300"
                                    >
                                        <option value="1101">1101 - Asuransi Kecelakaan Diri (AKD)</option>
                                        <option value="1202">1202 - Property All Risk (PAR)</option>
                                        <option value="1303">1303 - Kendaraan Bermotor</option>
                                        <option value="1404">1404 - Aneka / Varia</option>
                                        <option value="1505">1505 - Public Liability (PL)</option>
                                        <option value="1606">1606 - Surety Bond</option>
                                        <option value="OTHERS">Others (Custom Bebas)</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-gray-700">Prefix / Format Depan</label>
                                    <input 
                                        type="text" 
                                        required 
                                        value={data.prefix_surat} 
                                        onChange={e => setData('prefix_surat', e.target.value)} 
                                        className="w-full text-xs rounded-md border-gray-300 font-mono" 
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-2">
                                    <div>
                                        <label className="block text-xs font-medium text-gray-700">Nomor Awal (Angka)</label>
                                        <input type="number" required placeholder="1" value={data.nomor_awal} onChange={e => setData('nomor_awal', e.target.value)} className="w-full text-xs rounded-md border-gray-300" />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-gray-700">Nomor Akhir (Angka)</label>
                                        <input type="number" required placeholder="50" value={data.nomor_akhir} onChange={e => setData('nomor_akhir', e.target.value)} className="w-full text-xs rounded-md border-gray-300" />
                                    </div>
                                </div>

                                <div className="flex justify-end space-x-2 pt-3 border-t">
                                    <button type="button" onClick={() => setIsModalOpen(false)} className="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-xs font-semibold">Batal</button>
                                    <button type="submit" disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-md">Generate Stok Surat</button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}

            </div>
        </AuthenticatedLayout>
    );
}