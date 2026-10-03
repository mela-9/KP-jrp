import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import NotificationBell from '@/Components/NotificationBell';

export default function AuthenticatedLayout({ user, header, children }) {
    const { url } = usePage();
    const [sidebarOpen, setSidebarOpen] = useState(true);

    const isKepalaStaff = user?.role === 'kepala_staff' || user?.email === 'kepstaff123@gmail.com';

    return (
        <div className="min-h-screen bg-slate-100 flex font-sans">
            
            {/* SIDEBAR NAVBAR (KIRI) */}
            <aside className={`${sidebarOpen ? 'w-64' : 'w-20'} bg-blue-950 text-slate-300 hidden md:flex flex-col justify-between shadow-xl transition-all duration-300 relative`}>
                <div>
                    <div className="h-16 flex items-center px-4 bg-blue-900 border-b border-blue-900/50 justify-between">
                        <div className="flex items-center space-x-2 overflow-hidden">
                            <div className="bg-amber-400 text-blue-950 font-black px-2 py-1 rounded text-sm tracking-wider shrink-0">
                                JRP
                            </div>
                            {sidebarOpen && (
                                <div className="truncate">
                                    <span className="font-bold text-white text-xs block">E-REGISTER</span>
                                    <span className="text-[10px] text-slate-400 block">Jasaraharja Putera</span>
                                </div>
                            )}
                        </div>
                        <button 
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="bg-blue-950 hover:bg-blue-800 text-slate-200 p-1.5 rounded-lg text-xs transition border border-blue-800"
                        >
                            {sidebarOpen ? '◀' : '▶'}
                        </button>
                    </div>

                    <nav className="p-3 space-y-1.5 text-xs font-semibold">
                        {sidebarOpen && <div className="text-[10px] uppercase tracking-wider text-slate-400 px-3 pb-1">Menu Utama</div>}
                        
                        <Link href={route('dashboard')} className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition ${url.startsWith('/dashboard') ? 'bg-blue-900 text-white shadow-sm' : 'hover:bg-blue-900/50 text-slate-300'}`}>
                            <span>📊</span>
                            {sidebarOpen && <span>Dashboard Eksekutif</span>}
                        </Link>

                        {isKepalaStaff ? (
                            <>
                                {sidebarOpen && <div className="pt-4 text-[10px] uppercase tracking-wider text-amber-400 px-3 pb-1">Validasi Korporat</div>}
                                <Link href={route('kepala-staff.approvals')} className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition ${url.includes('/approvals') ? 'bg-blue-900 text-white shadow-sm' : 'hover:bg-blue-900/50 text-slate-300'}`}>
                                    <span>📥</span>
                                    {sidebarOpen && <span>Antrean Approval</span>}
                                </Link>
                                <Link href={route('kepala-staff.history')} className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition ${url.includes('/history') ? 'bg-blue-900 text-white shadow-sm' : 'hover:bg-blue-900/50 text-slate-300'}`}>
                                    <span>📜</span>
                                    {sidebarOpen && <span>History Persetujuan</span>}
                                </Link>
                            </>
                        ) : (
                            <>
                                {sidebarOpen && <div className="pt-4 text-[10px] uppercase tracking-wider text-slate-400 px-3 pb-1">Manajemen Gudang</div>}
                                <Link href={route('surat-blocks.index')} className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition ${url.includes('/surat-blocks') ? 'bg-blue-900 text-white shadow-sm' : 'hover:bg-blue-900/50 text-slate-300'}`}>
                                    <span>📦</span>
                                    {sidebarOpen && <span>Pendaftaran Blok Surat</span>}
                                </Link>

                                {sidebarOpen && <div className="pt-4 text-[10px] uppercase tracking-wider text-slate-400 px-3 pb-1">6 Buku Besar E-Register</div>}
                                <Link href={route('akd.index')} className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition ${url.includes('/akd') ? 'bg-blue-900 text-white shadow-sm' : 'hover:bg-blue-900/50 text-slate-300'}`}>
                                    <span>📁</span>
                                    {sidebarOpen && <span>Buku Besar AKD</span>}
                                </Link>
                                <Link href={route('par.index')} className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition ${url.includes('/par') ? 'bg-blue-900 text-white shadow-sm' : 'hover:bg-blue-900/50 text-slate-300'}`}>
                                    <span>🏢</span>
                                    {sidebarOpen && <span>Buku Besar PAR</span>}
                                </Link>
                                <Link href={route('vehicle.index')} className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition ${url.includes('/vehicle') ? 'bg-blue-900 text-white shadow-sm' : 'hover:bg-blue-900/50 text-slate-300'}`}>
                                    <span>🚗</span>
                                    {sidebarOpen && <span>Buku Besar Vehicle</span>}
                                </Link>
                                <Link href={route('varia.index')} className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition ${url.includes('/varia') ? 'bg-blue-900 text-white shadow-sm' : 'hover:bg-blue-900/50 text-slate-300'}`}>
                                    <span>📦</span>
                                    {sidebarOpen && <span>Buku Besar Varia</span>}
                                </Link>
                                <Link href={route('pl.index')} className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition ${url.includes('/pl') ? 'bg-blue-900 text-white shadow-sm' : 'hover:bg-blue-900/50 text-slate-300'}`}>
                                    <span>⚖️</span>
                                    {sidebarOpen && <span>Buku Besar PL</span>}
                                </Link>
                                <Link href={route('surety.index')} className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition ${url.includes('/surety-bond') ? 'bg-blue-900 text-white shadow-sm' : 'hover:bg-blue-900/50 text-slate-300'}`}>
                                    <span>📝</span>
                                    {sidebarOpen && <span>Buku Besar Surety Bond</span>}
                                </Link>
                            </>
                        )}
                    </nav>
                </div>

                <div className="p-3 bg-blue-950/80 border-t border-blue-900/50">
                    {sidebarOpen && (
                        <div className="flex items-center justify-between">
                            <div className="truncate">
                                <div className="text-xs font-bold text-white truncate">{user?.name}</div>
                                <div className="text-[10px] text-amber-400 uppercase">
                                    {isKepalaStaff ? 'Kepala Staff' : 'Staff Underwriting'}
                                </div>
                            </div>
                            <Link href={route('logout')} method="post" as="button" className="text-[10px] bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded">
                                Logout
                            </Link>
                        </div>
                    )}
                </div>
            </aside>

            {/* MAIN CONTENT AREA */}
            <div className="flex-1 flex flex-col min-w-0 overflow-hidden">
                <header className="bg-white shadow-xs border-b border-slate-200 h-16 flex items-center justify-between px-6 z-10">
                    <div className="flex items-center">{header}</div>

                    <NotificationBell />
                </header>

                <main className="flex-1 overflow-x-hidden overflow-y-auto bg-slate-100">
                    {children}
                </main>
            </div>

        </div>
    );
}