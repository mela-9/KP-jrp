import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ auth }) {
    // Daftar menu e-register diarahkan ke halaman informasi (.info) terlebih dahulu
    const menus = [
        {
            title: 'Asuransi Kecelakaan Diri (AKD)',
            description: 'Pelajari cakupan jaminan, panduan polis, dan akses buku besar AKD.',
            route: 'akd.info',
            color: 'border-blue-500 text-blue-600 bg-blue-50',
            badge: 'AKD'
        },
        {
            title: 'Property All Risk (PAR)',
            description: 'Pelajari cakupan jaminan, panduan polis, dan akses buku besar PAR.',
            route: 'par.info',
            color: 'border-indigo-500 text-indigo-600 bg-indigo-50',
            badge: 'PAR'
        },
        {
            title: 'Kendaraan Bermotor',
            description: 'Pelajari cakupan jaminan, panduan polis, dan akses buku besar Kendaraan.',
            route: 'vehicle.info',
            color: 'border-emerald-500 text-emerald-600 bg-emerald-50',
            badge: 'VEH'
        },
        {
            title: 'Aneka / Varia',
            description: 'Pelajari cakupan jaminan, panduan polis, dan akses buku besar Varia.',
            route: 'varia.info',
            color: 'border-amber-500 text-amber-600 bg-amber-50',
            badge: 'VAR'
        },
        {
            title: 'Public Liability',
            description: 'Pelajari cakupan jaminan, panduan polis, dan akses buku besar Public Liability.',
            route: 'pl.info',
            color: 'border-purple-500 text-purple-600 bg-purple-50',
            badge: 'PL'
        },
        {
            title: 'Surety Bond',
            description: 'Pelajari cakupan jaminan, panduan polis, dan akses buku besar Surety Bond.',
            route: 'surety.info',
            color: 'border-rose-500 text-rose-600 bg-rose-50',
            badge: 'SB'
        },
        {
            title: 'Pusat Manajemen Nomor Surat',
            description: 'Inventarisasi nomor surat lintas polis, pelacakan kertas rusak, & void secara terpusat.',
            route: 'surat.index',
            color: 'border-cyan-500 text-cyan-600 bg-cyan-50',
            badge: 'SURAT'
        },
    ];

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard Utama JRP Care
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                    
                    {/* Welcome Banner */}
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-600 flex justify-between items-center">
                        <div>
                            <h3 className="text-lg font-bold text-gray-800">Selamat datang kembali, {auth.user.name}! 👋</h3>
                            <p className="text-sm text-gray-500 mt-1">
                                Pilih salah satu modul E-Register di bawah ini untuk melihat informasi produk atau mengelola buku besar.
                            </p>
                        </div>
                        <span className="hidden md:inline-block bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1 rounded-full">
                            Role: {auth.user.role || 'Staff'}
                        </span>
                    </div>

                    {/* Grid Menu Cards */}
                    <div>
                        <h3 className="text-md font-bold text-gray-700 mb-4 px-1">Menu JRP Care / Modul E-Register</h3>
                        
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            {menus.map((menu, index) => (
                                <Link
                                    key={index}
                                    href={route(menu.route)}
                                    className="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-6 border border-gray-100 flex flex-col justify-between group hover:-translate-y-1"
                                >
                                    <div>
                                        <div className="flex justify-between items-center mb-3">
                                            <span className={`text-xs font-extrabold px-2.5 py-1 rounded-md ${menu.color}`}>
                                                {menu.badge}
                                            </span>
                                            <span className="text-gray-300 group-hover:text-blue-600 transition font-bold">
                                                &rarr;
                                            </span>
                                        </div>
                                        <h4 className="font-bold text-gray-800 group-hover:text-blue-600 transition text-base">
                                            {menu.title}
                                        </h4>
                                        <p className="text-xs text-gray-500 mt-2 leading-relaxed">
                                            {menu.description}
                                        </p>
                                    </div>
                                    <div className="mt-6 pt-3 border-t border-gray-50 flex items-center text-xs font-semibold text-blue-600">
                                        Lihat Informasi Modul &rarr;
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}