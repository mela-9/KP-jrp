import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function AkdIndex({ auth, akdData }) {
    
    const handlePrint = () => {
        window.print();
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">E-Register Polis: Asuransi Kecelakaan Diri (AKD)</h2>}
        >
            <Head title="E-Register AKD" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        
                        {/* Header & Tombol Aksi */}
                        <div className="flex justify-between items-center mb-4 print:hidden">
                            <h3 className="text-lg font-bold text-gray-700">Buku Besar Digital AKD</h3>
                            <div className="space-x-2">
                                <button className="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    Export Excel
                                </button>
                                <button onClick={handlePrint} className="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    Print / PDF
                                </button>
                                <button className="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded text-sm">
                                    + Input Polis (Manual)
                                </button>
                            </div>
                        </div>

                        {/* Tabel Data */}
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm text-left text-gray-500 border border-gray-200">
                                <thead className="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr>
                                        <th scope="col" className="px-4 py-3 border-b">Tgl Input</th>
                                        <th scope="col" className="px-4 py-3 border-b">Tertanggung</th>
                                        <th scope="col" className="px-4 py-3 border-b">No. Surat & Polis</th>
                                        <th scope="col" className="px-4 py-3 border-b">Periode</th>
                                        <th scope="col" className="px-4 py-3 border-b">Rincian TSI</th>
                                        <th scope="col" className="px-4 py-3 border-b">Premi</th>
                                        <th scope="col" className="px-4 py-3 border-b">Sumber</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {akdData && akdData.length > 0 ? (
                                        akdData.map((item) => (
                                            <tr key={item.id} className="bg-white border-b hover:bg-gray-50">
                                                <td className="px-4 py-4">{item.tgl_input}</td>
                                                <td className="px-4 py-4 font-medium text-gray-900">{item.nama_tertanggung}</td>
                                                <td className="px-4 py-4">
                                                    <div className="text-xs text-gray-500">{item.no_surat}</div>
                                                    <div className="font-semibold">{item.no_polis}</div>
                                                </td>
                                                <td className="px-4 py-4">
                                                    {item.periode_awal} s/d {item.periode_akhir}
                                                </td>
                                                <td className="px-4 py-4 text-xs">
                                                    <div>A/B: {item.tsi_ab}</div>
                                                    <div>C: {item.tsi_c}</div>
                                                    <div>D: {item.tsi_d}</div>
                                                    <div>E: {item.tsi_e}</div>
                                                </td>
                                                <td className="px-4 py-4 font-semibold text-gray-800">{item.premi}</td>
                                                <td className="px-4 py-4">{item.sumber_bisnis}</td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="7" className="px-4 py-8 text-center text-gray-500">
                                                Belum ada data polis yang diinput.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}