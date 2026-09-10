import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function SharedModuleInfo({ auth, moduleName, code, description, coverages, registerRoute }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">Informasi Modul: {moduleName}</h2>
                    <span className="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-md">
                        {code}
                    </span>
                </div>
            }
        >
            <Head title={`Info - ${moduleName}`} />

            <div className="py-12">
                <div className="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    {/* Card Utama Informasi Polis */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-xl p-8 border border-gray-100">
                        <div className="flex items-start justify-between">
                            <div>
                                <span className="text-xs font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2.5 py-1 rounded">
                                    Panduan & Cakupan Produk Asuransi
                                </span>
                                <h3 className="text-2xl font-bold text-gray-900 mt-3">{moduleName}</h3>
                                <p className="text-gray-600 mt-2 text-sm leading-relaxed max-w-3xl">
                                    {description}
                                </p>
                            </div>
                        </div>

                        {/* Cakupan Jaminan / Proteksi */}
                        <div className="mt-8 border-t pt-6">
                            <h4 className="font-bold text-gray-800 text-sm uppercase tracking-wide mb-3">
                                Cakupan & Risiko yang Dilindungi:
                            </h4>
                            <ul className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                {coverages.map((item, index) => (
                                    <li key={index} className="flex items-start text-xs text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                        <span className="text-emerald-600 font-bold mr-2 text-sm">✓</span>
                                        <span className="leading-relaxed">{item}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        {/* Tombol Aksi Navigasi ke Register / Buku Besar */}
                        <div className="mt-10 pt-6 border-t flex items-center justify-between">
                            <Link
                                href={route('dashboard')}
                                className="text-sm font-semibold text-gray-500 hover:text-gray-800 transition"
                            >
                                &larr; Kembali ke Dashboard
                            </Link>

                            <Link
                                href={route(registerRoute)}
                                className="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition text-sm flex items-center space-x-2"
                            >
                                <span>Buka Buku Besar E-Register & Workflow</span>
                                <span className="text-base">&rarr;</span>
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}