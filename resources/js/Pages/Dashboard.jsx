import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'react-chartjs-2';

ChartJS.register(ArcElement, Tooltip, Legend);

export default function Dashboard({ auth, stats, suratBlocks, pendingRegisters = [] }) {
    const [selectedCategory, setSelectedCategory] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const isKepalaStaff = auth.user.role === 'kepala_staff' || auth.user.email === 'kepstaff123@gmail.com';

    const chartData = {
        labels: ['Asuransi Kecelakaan Diri (AKD)', 'Property All Risk (PAR)', 'Vehicle (Kendaraan)', 'Varia (Aneka)', 'Public Liability (PL)', 'Surety Bond'],
        datasets: [
            {
                data: [stats.akd, stats.par, stats.vehicle, stats.varia, stats.pl, stats.surety_bond],
                backgroundColor: ['#1e3a8a', '#0d9488', '#d97706', '#7c3aed', '#db2777', '#4b5563'],
                borderWidth: 2,
                borderColor: '#ffffff',
            },
        ],
    };

    const handleChartClick = (event, elements) => {
        if (elements.length > 0) {
            const index = elements[0].index;
            const categories = [
                { name: 'Asuransi Kecelakaan Diri (AKD)', key: 'AKD', count: stats.akd },
                { name: 'Property All Risk (PAR)', key: 'PAR', count: stats.par },
                { name: 'Vehicle (Kendaraan)', key: 'VEHICLE', count: stats.vehicle },
                { name: 'Varia (Aneka)', key: 'VARIA', count: stats.varia },
                { name: 'Public Liability (PL)', key: 'PL', count: stats.pl },
                { name: 'Surety Bond', key: 'SURETY', count: stats.surety_bond },
            ];
            setSelectedCategory(categories[index]);
            setIsModalOpen(true);
        }
    };

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        onClick: handleChartClick,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 12, font: { size: 11 } }
            }
        }
    };

    const filteredBlocks = suratBlocks && selectedCategory 
        ? suratBlocks.filter(block => block.jenis_polis === selectedCategory.key)
        : [];

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Dashboard Eksekutif E-Register</h2>}
        >
            <Head title="Dashboard" />

            <div className="py-6 max-w-7xl mx-auto space-y-6">
                
                {/* NOTIFIKASI KEPALA STAFF MENUJU HALAMAN APPROVAL KHUSUS */}
                {isKepalaStaff && (
                    <div className="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-xl shadow-xs flex justify-between items-center">
                        <div>
                            <h3 className="font-bold text-amber-900 text-sm">🔔 Panel Validasi Korporat Kepala Staff</h3>
                            <p className="text-xs text-amber-700 mt-0.5">Kelola antrean berkas masuk dan tinjau riwayat audit trail melalui menu khusus di sidebar kiri.</p>
                        </div>
                        <Link 
                            href={route('kepala-staff.approvals')}
                            className="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs px-4 py-2 rounded-lg transition shadow-xs"
                        >
                            Buka Antrean Approval →
                        </Link>
                    </div>
                )}

                {/* KARTU STATISTIK EKSEKUTIF */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div className="bg-white p-5 rounded-xl shadow-xs border-l-4 border-blue-900">
                        <div className="text-xs font-bold uppercase text-gray-400">Total Keseluruhan Polis</div>
                        <div className="text-3xl font-extrabold text-blue-900 mt-1">{stats.total} <span className="text-sm font-normal text-gray-500">Polis</span></div>
                    </div>
                    <div className="bg-white p-5 rounded-xl shadow-xs border-l-4 border-teal-600">
                        <div className="text-xs font-bold uppercase text-gray-400">Akumulasi Bulan Aktif</div>
                        <div className="text-3xl font-extrabold text-teal-600 mt-1">Juli - Okt</div>
                    </div>
                    {/* Kartu Sistem Gudang Surat / Nomor Terakhir Penggunaan */}
<div className="bg-white p-6 rounded-2xl shadow-xs border border-slate-100 border-l-4 border-l-amber-500">
    <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">No. Terakhir Penggunaan Surat</p>
    <div className="mt-2 flex items-baseline space-x-2">
        <span className="text-xl font-extrabold text-slate-800 font-mono">
            J1: {stats?.jalur1 || 0} | J2: {stats?.jalur2 || 0}
        </span>
    </div>
    <p className="text-[10px] text-slate-500 mt-1">Akumulasi alokasi kedua jalur gudang</p>
</div>
                    <div className="bg-white p-5 rounded-xl shadow-xs border-l-4 border-purple-600">
                        <div className="text-xs font-bold uppercase text-gray-400">Hak Akses Aktif</div>
                        <div className="text-xl font-bold text-purple-700 mt-2 uppercase">{auth.user.role || 'Staff / Pimpinan'}</div>
                    </div>
                </div>

                {/* GRAFIK DONAT (MELEBAR PENUH) */}
                <div className="bg-white p-6 rounded-xl shadow-xs flex flex-col justify-between">
                    <div>
                        <h3 className="font-bold text-gray-800 text-lg">Proporsi Registrasi Polis Berdasarkan Jenis</h3>
                        <p className="text-xs text-gray-500 mt-1">Klik pada irisan diagram untuk melihat rincian status gudang dan alokasi nomor surat.</p>
                    </div>
                    <div className="relative h-80 w-full my-4 flex justify-center items-center">
                        <Doughnut data={chartData} options={options} />
                    </div>
                </div>
            </div>

            {/* MODAL RINCIAN DONUT CHART & GUDANG SURAT */}
            {isModalOpen && selectedCategory && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div className="bg-white rounded-xl shadow-2xl w-full max-w-xl p-6 space-y-4">
                        <div className="flex justify-between items-center border-b pb-3">
                            <h3 className="font-bold text-base text-blue-900">📊 Rincian Gudang & Polis: {selectedCategory.name}</h3>
                            <button onClick={() => setIsModalOpen(false)} className="text-gray-400 hover:text-red-600 font-bold text-xl">&times;</button>
                        </div>
                        
                        <div className="space-y-3 text-xs text-gray-600">
                            <div className="bg-blue-50 p-3 rounded-lg flex justify-between items-center">
                                <span className="font-semibold text-gray-700">Total Polis Terdaftar di Database:</span>
                                <span className="font-bold text-blue-900 text-sm">{selectedCategory.count} Polis</span>
                            </div>

                            <div className="border-t pt-2">
                                <span className="font-bold text-gray-700 uppercase tracking-wide">Status Stok Blok Nomor Surat (Gudang):</span>
                                <div className="mt-2 space-y-2 max-h-60 overflow-y-auto">
                                    {filteredBlocks.length > 0 ? (
                                        filteredBlocks.map((block) => {
                                            const totalKuota = block.range_end - block.range_start + 1;
                                            const sisa = totalKuota - block.terpakai;
                                            return (
                                                <div key={block.id} className="bg-slate-50 border p-3 rounded-lg space-y-1">
                                                    <div className="flex justify-between font-bold text-slate-800">
                                                        <span>Pakem: {block.kode_pakem} (Jalur {block.jalur})</span>
                                                        <span className="text-blue-900">Tahun {block.tahun}</span>
                                                    </div>
                                                    <div className="text-slate-500 flex justify-between">
                                                        <span>Rentang: {String(block.range_start).padStart(4, '0')} s.d {String(block.range_end).padStart(4, '0')}</span>
                                                        <span className="font-semibold text-emerald-700">Terpakai: {block.terpakai} | Sisa: {sisa} Lembar</span>
                                                    </div>
                                                </div>
                                            );
                                        })
                                    ) : (
                                        <div className="p-3 bg-gray-50 rounded text-center text-gray-400 italic">
                                            Belum ada blok surat gudang yang didaftarkan untuk lini produk ini.
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="bg-amber-50 p-2.5 rounded border border-amber-200 text-amber-800 text-[11px]">
                                ℹ️ Penomoran surat ditarik secara berurutan murni dari gudang untuk mencegah duplikasi data.
                            </div>
                        </div>

                        <div className="flex justify-end pt-2 border-t">
                            <button onClick={() => setIsModalOpen(false)} className="bg-blue-900 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-blue-800 transition">
                                Tutup Rincian
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}