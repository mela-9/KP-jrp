import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';

export default function SuratBlockIndex({ auth, suratBlocks }) {
    const [showModal, setShowModal] = useState(false);
    const [showDetailModal, setShowDetailModal] = useState(false);
    const [selectedBlock, setSelectedBlock] = useState(null);
    const [blockDetails, setBlockDetails] = useState([]);
    const [loadingDetail, setLoadingDetail] = useState(false);

    const { data, setData, post, processing, reset } = useForm({
        jenis_polis: 'AKD',
        kode_pakem: 'AKD-JRP',
        jalur: '1125',
        tahun: 2026,
        range_start: 1,
        range_end: 3000,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('surat-blocks.store'), {
            onSuccess: () => {
                setShowModal(false);
                reset();
            },
        });
    };

    // Fungsi saat baris blok surat diklik
    const handleRowClick = async (block) => {
        setSelectedBlock(block);
        setShowDetailModal(true);
        setLoadingDetail(true);

        try {
            const response = await axios.get(route('surat-blocks.detail', block.id));
            setSelectedBlock(response.data.block);
            setBlockDetails(response.data.registers);
        } catch (error) {
            console.error("Gagal mengambil rincian blok surat", error);
        } finally {
            setLoadingDetail(false);
        }
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800">Manajemen Gudang & Pendaftaran Blok Surat</h2>}>
            <Head title="Pendaftaran Blok Surat" />

            <div className="py-6 max-w-7xl mx-auto space-y-6">
                <div className="flex justify-between items-center bg-white p-4 rounded-xl shadow-xs">
                    <div>
                        <h3 className="font-bold text-gray-800">Daftar Alokasi Nomor Surat Resmi (Jalur 1125 & 1126)</h3>
                        <p className="text-xs text-gray-500">Klik pada salah satu baris tabel di bawah untuk melihat rincian pemakaian nomor surat secara menyeluruh.</p>
                    </div>
                    <button
                        onClick={() => setShowModal(true)}
                        className="bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-lg transition shadow-sm"
                    >
                        + Daftarkan Blok Surat Baru
                    </button>
                </div>

                <div className="bg-white rounded-xl shadow-xs overflow-hidden">
                    <table className="w-full text-left text-xs">
                        <thead className="bg-slate-50 border-b text-slate-600 uppercase">
                            <tr>
                                <th className="p-3">Jenis Lini Produk</th>
                                <th className="p-3">Kode Pakem</th>
                                <th className="p-3">Jalur / Tahun</th>
                                <th className="p-3">Rentang Nomor</th>
                                <th className="p-3">Terpakai</th>
                                <th className="p-3">Sisa Kuota</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y text-slate-700">
                            {suratBlocks.map((block) => {
                                const totalKuota = block.range_end - block.range_start + 1;
                                const sisa = totalKuota - block.terpakai;
                                return (
                                    <tr 
                                        key={block.id} 
                                        onClick={() => handleRowClick(block)}
                                        className="hover:bg-blue-50 cursor-pointer transition"
                                        title="Klik untuk melihat rincian pemakaian"
                                    >
                                        <td className="p-3 font-bold text-blue-900">{block.jenis_polis}</td>
                                        <td className="p-3 font-mono">{block.kode_pakem}</td>
                                        <td className="p-3">Jalur {block.jalur} ({block.tahun})</td>
                                        <td className="p-3 font-mono">{String(block.range_start).padStart(4, '0')} s.d {String(block.range_end).padStart(4, '0')}</td>
                                        <td className="p-3 font-semibold text-amber-600">{block.terpakai} Lembar</td>
                                        <td className="p-3 font-semibold text-emerald-600">{sisa} Lembar</td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* MODAL RINCIAN BLOK SURAT MENYELURUH */}
            {showDetailModal && selectedBlock && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div className="bg-white rounded-xl shadow-2xl w-full max-w-4xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                        <div className="flex justify-between items-center border-b pb-3">
                            <div>
                                <h3 className="font-bold text-base text-blue-900">📦 Rincian Pemakaian Blok Surat: {selectedBlock.jenis_polis}</h3>
                                <p className="text-xs text-slate-500 font-mono">Pakem: {selectedBlock.kode_pakem} | Jalur {selectedBlock.jalur} ({selectedBlock.tahun})</p>
                            </div>
                            <button onClick={() => setShowDetailModal(false)} className="text-gray-400 hover:text-red-600 font-bold text-xl">&times;</button>
                        </div>

                        <div className="grid grid-cols-3 gap-3 bg-slate-50 p-3 rounded-lg text-xs">
                            <div><span className="text-slate-500 block">Rentang Kuota:</span> <span className="font-mono font-bold">{selectedBlock.range_start} s.d {selectedBlock.range_end}</span></div>
                            <div><span className="text-slate-500 block">Total Terpakai:</span> <span className="font-bold text-amber-600">{selectedBlock.terpakai} Lembar</span></div>
                            <div><span className="text-slate-500 block">Skema Kuota:</span> <span className="font-bold text-emerald-600">Bersama pada jalur {selectedBlock.jalur}</span></div>
                        </div>

                        <div>
                            <h4 className="font-bold text-slate-700 text-xs mb-2">Daftar Pemakaian Kuota Bersama Jalur Ini:</h4>
                            {loadingDetail ? (
                                <p className="text-center text-xs text-slate-400 py-6">Memuat rincian data...</p>
                            ) : blockDetails.length > 0 ? (
                                <div className="border rounded-lg overflow-hidden max-h-64 overflow-y-auto">
                                    <table className="w-full text-left text-[11px]">
                                        <thead className="bg-slate-100 border-b text-slate-600">
                                            <tr>
                                                <th className="p-2">Jenis Polis</th>
                                                <th className="p-2">No. Polis</th>
                                                <th className="p-2">Tertanggung</th>
                                                <th className="p-2">Nomor Surat / Halaman</th>
                                                <th className="p-2">Tanggal Input</th>
                                                <th className="p-2">Kondisi</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y text-slate-800">
                                            {blockDetails.map((item, idx) => (
                                                <tr key={idx} className="hover:bg-slate-50">
                                                    <td className="p-2 font-bold text-slate-600">{item.jenis_polis}</td>
                                                    <td className="p-2 font-mono font-bold text-blue-900">{item.no_polis}</td>
                                                    <td className="p-2">{item.nama_tertanggung || item.tertanggung}</td>
                                                    <td className="p-2 font-mono text-amber-800">
                                                        {Array.isArray(item.nomor_surat_array) ? item.nomor_surat_array.join(', ') : item.no_surat}
                                                    </td>
                                                    <td className="p-2 text-slate-500">{item.tgl_input}</td>
                                                    <td className="p-2">
                                                        <span className={`px-2 py-0.5 rounded font-bold text-[10px] ${
                                                            (item.kondisi_surat || 'Normal') === 'Normal' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                                                        }`}>
                                                            {item.kondisi_surat || 'Normal'}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <p className="text-center text-xs text-slate-400 py-6 italic border rounded-lg bg-slate-50">Belum ada nomor surat pada jalur dan rentang blok ini yang tercatat digunakan.</p>
                            )}
                        </div>

                        <div className="flex justify-end pt-2 border-t">
                            <button onClick={() => setShowDetailModal(false)} className="bg-blue-900 text-white text-xs font-bold px-4 py-2 rounded-lg">Tutup</button>
                        </div>
                    </div>
                </div>
            )}

            {/* MODAL PENDAFTARAN BLOK BARU */}
            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div className="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 space-y-4">
                        <div className="flex justify-between items-center border-b pb-3">
                            <h3 className="font-bold text-base text-blue-900">📦 Pendaftaran Blok Nomor Surat</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-red-600 font-bold text-xl">&times;</button>
                        </div>

                        <form onSubmit={submit} className="space-y-4 text-xs">
                            <div>
                                <label className="block font-semibold text-gray-700 mb-1">Jenis Polis / Produk</label>
                                <select
                                    value={data.jenis_polis}
                                    onChange={e => setData('jenis_polis', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg p-2.5 text-xs"
                                >
                                    <option value="AKD">Asuransi Kecelakaan Diri (AKD)</option>
                                    <option value="PAR">Property All Risk (PAR)</option>
                                    <option value="VEHICLE">Vehicle (Kendaraan)</option>
                                    <option value="VARIA">Varia (Aneka)</option>
                                    <option value="PL">Public Liability (PL)</option>
                                    <option value="SURETY">Surety Bond</option>
                                </select>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block font-semibold text-gray-700 mb-1">Kode Pakem</label>
                                    <input type="text" value={data.kode_pakem} onChange={e => setData('kode_pakem', e.target.value)} className="w-full border rounded-lg p-2.5 text-xs font-mono" required />
                                </div>
                                <div>
                                    <label className="block font-semibold text-gray-700 mb-1">Jalur Surat</label>
                                    <input type="text" value={data.jalur} onChange={e => setData('jalur', e.target.value)} placeholder="1125" className="w-full border rounded-lg p-2.5 text-xs" required />
                                </div>
                            </div>

                            <div className="grid grid-cols-3 gap-2">
                                <div>
                                    <label className="block font-semibold text-gray-700 mb-1">Tahun</label>
                                    <input type="number" value={data.tahun} onChange={e => setData('tahun', e.target.value)} className="w-full border rounded-lg p-2.5 text-xs" required />
                                </div>
                                <div>
                                    <label className="block font-semibold text-gray-700 mb-1">Start</label>
                                    <input type="number" value={data.range_start} onChange={e => setData('range_start', e.target.value)} className="w-full border rounded-lg p-2.5 text-xs" required />
                                </div>
                                <div>
                                    <label className="block font-semibold text-gray-700 mb-1">End</label>
                                    <input type="number" value={data.range_end} onChange={e => setData('range_end', e.target.value)} className="w-full border rounded-lg p-2.5 text-xs" required />
                                </div>
                            </div>

                            <div className="flex justify-end pt-2 border-t space-x-2">
                                <button type="button" onClick={() => setShowModal(false)} className="bg-gray-200 px-4 py-2 rounded-lg font-bold">Batal</button>
                                <button type="submit" disabled={processing} className="bg-blue-900 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-800">Simpan Blok</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}