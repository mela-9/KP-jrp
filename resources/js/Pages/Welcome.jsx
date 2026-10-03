import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function Welcome({ canLogin, canRegister }) {
    return (
        <>
            <Head title="Selamat Datang - E-Register & Digital Validation JRP Insurance" />
            <div className="min-h-screen bg-slate-50 text-slate-800 flex flex-col justify-between font-sans scroll-smooth">
                
                {/* NAVBAR */}
                <header className="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200 py-4 px-6 md:px-12 flex justify-between items-center shadow-xs">
                    <div className="flex items-center space-x-3">
                        <div className="bg-blue-900 text-white font-black px-3 py-1.5 rounded-lg text-lg tracking-wider">JRP</div>
                        <div>
                            <span className="font-bold text-blue-900 text-sm md:text-base block">ASURANSI JASARAHARJA PUTERA</span>
                            <span className="text-[10px] text-slate-500 tracking-wide block">E-Register & Digital Validation System</span>
                        </div>
                    </div>
                    <div className="flex items-center space-x-4">
                        <a href="#tentang" className="text-xs md:text-sm font-medium text-slate-600 hover:text-blue-900 transition hidden md:block">Tentang Asuransi</a>
                        <a href="#sistem" className="text-xs md:text-sm font-medium text-slate-600 hover:text-blue-900 transition hidden md:block">Tentang Sistem</a>
                        {canLogin && <Link href={route('login')} className="text-xs md:text-sm font-semibold text-blue-900 px-4 py-2">Masuk Sistem</Link>}
                        {canRegister && <Link href={route('register')} className="text-xs md:text-sm font-bold bg-blue-900 text-white px-4 py-2.5 rounded-xl shadow-md">Daftar Akun</Link>}
                    </div>
                </header>

                {/* HERO SECTION */}
                <main>
                    <section className="max-w-7xl mx-auto px-6 md:px-12 py-12 md:py-20 grid grid-cols-1 md:grid-cols-12 gap-12 items-center">
                        <div className="md:col-span-7 space-y-6">
                            <span className="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Official Portal</span>
                            <h1 className="text-3xl md:text-5xl font-extrabold text-blue-950 leading-tight">Solusi Tertib Administrasi & Validasi Polis Digital.</h1>
                            <p className="text-slate-600 text-sm md:text-base leading-relaxed">Platform pencatatan buku besar terpadu untuk seluruh lini produk dengan manajemen stok nomor surat otomatis serta kolaborasi persetujuan yang transparan.</p>
                            <div className="pt-2 flex flex-wrap items-center gap-4">
                                <a href="#sistem" className="bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 px-6 rounded-xl text-sm shadow-md transition flex items-center space-x-2">
                                    <span>Pelajari Sistem</span>
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </a>
                                <a href="https://jrp.co.id" target="_blank" rel="noopener noreferrer" className="bg-white border border-slate-300 text-slate-700 font-semibold py-3 px-6 rounded-xl text-sm hover:bg-slate-50 transition">Situs Resmi JRP</a>
                            </div>
                        </div>
                        <div className="md:col-span-5">
                            <div className="bg-gradient-to-br from-blue-900 to-slate-800 p-8 rounded-3xl shadow-xl text-white relative overflow-hidden">
                                <div className="absolute -right-10 -bottom-10 w-40 h-40 bg-blue-600/20 rounded-full blur-2xl"></div>
                                <div className="space-y-4 relative z-10">
                                    <span className="text-amber-400 text-xs font-bold block mb-1">⚡ WORKFLOW TERPADU</span>
                                    <h3 className="font-bold text-xl">Staff & Kepala Staff Kolaborasi</h3>
                                    <p className="text-slate-300 text-sm leading-relaxed">
                                        Memastikan setiap penerbitan nomor polis dan validasi dokumen melalui verifikasi ketat secara real-time.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {/* SECTION 1: EDUKASI ASURANSI & PROFIL JRP */}
                    <section id="tentang" className="bg-white py-20 border-y border-slate-200">
                        <div className="max-w-7xl mx-auto px-6 md:px-12">
                            <div className="text-center max-w-2xl mx-auto mb-16 space-y-3">
                                <h2 className="text-2xl md:text-3xl font-bold text-blue-950">Mengenal Asuransi & PT Jasaraharja Putera</h2>
                                <p className="text-slate-600 text-sm md:text-base">Pahami lebih dekat mengenai pentingnya proteksi finansial dan profil perusahaan asuransi terkemuka di Indonesia.</p>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div className="bg-slate-50 p-8 rounded-2xl border border-slate-200 space-y-4">
                                    <div className="w-12 h-12 rounded-xl bg-blue-100 text-blue-900 flex items-center justify-center font-bold text-xl">💡</div>
                                    <h3 className="text-xl font-bold text-blue-950">Konsep Dasar Asuransi</h3>
                                    <p className="text-slate-600 text-sm leading-relaxed">
                                        Asuransi adalah mekanisme pengalihan risiko dari tertanggung kepada penanggung dengan menghimpun dana melalui premi. Tujuannya adalah memberikan rasa aman dan jaminan perlindungan finansial dari ketidakpastian kerugian di masa depan.
                                    </p>
                                </div>

                                <div className="bg-slate-50 p-8 rounded-2xl border border-slate-200 space-y-4">
                                    <div className="w-12 h-12 rounded-xl bg-blue-100 text-blue-900 flex items-center justify-center font-bold text-xl">🛡️️</div>
                                    <h3 className="text-xl font-bold text-blue-950">Tentang PT Jasaraharja Putera (JRP)</h3>
                                    <p className="text-slate-600 text-sm leading-relaxed">
                                        PT Jasaraharja Putera merupakan anak perusahaan PT Jasa Raharja yang bergerak di bidang asuransi umum, menyediakan berbagai solusi perlindungan mulai dari kendaraan, properti, kecelakaan diri, hingga penjaminan (*suretyship*).
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {/* SECTION 2: PENJELASAN SISTEM */}
                    <section id="sistem" className="py-20 max-w-7xl mx-auto px-6 md:px-12">
                        <div className="text-center max-w-2xl mx-auto mb-16 space-y-3">
                            <h2 className="text-2xl md:text-3xl font-bold text-blue-950">Tentang Sistem E-Register & Validasi Digital</h2>
                            <p className="text-slate-600 text-sm md:text-base">Transformasi digital administrasi polis untuk menciptakan efisiensi, akurasi, dan transparansi operasional.</p>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                                <div className="text-blue-600 font-bold text-sm">01. E-Register Blok Surat</div>
                                <h4 className="font-semibold text-blue-950">Manajemen Nomor Otomatis</h4>
                                <p className="text-slate-600 text-sm">
                                    Pencatatan nomor urut polis dikelola secara digital untuk mencegah duplikasi data (*register ganda*) dan inkonsistensi administrasi.
                                </p>
                            </div>
                            <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                                <div className="text-blue-600 font-bold text-sm">02. Validasi Digital (Approval)</div>
                                <h4 className="font-semibold text-blue-950">Persetujuan Berjenjang</h4>
                                <p className="text-slate-600 text-sm">
                                    Staf memasukkan data polis, kemudian Kepala Staff melakukan peninjauan dan persetujuan secara elektronik dengan cepat dan akurat.
                                </p>
                            </div>
                            <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                                <div className="text-blue-600 font-bold text-sm">03. Riwayat & Pelacakan</div>
                                <h4 className="font-semibold text-blue-950">Audit Trail Terpadu</h4>
                                <p className="text-slate-600 text-sm">
                                    Setiap transaksi dilengkapi riwayat (*history*) status dokumen secara real-time untuk memastikan akuntabilitas operasional cabang.
                                </p>
                            </div>
                        </div>
                    </section>
                </main>

                {/* FOOTER */}
                {/* FOOTER */}
                <footer className="bg-white border-t border-slate-200 py-6 px-6 md:px-12 flex flex-col md:flex-row justify-between items-center text-xs text-slate-500">
                    <p>© 2026 PT Asuransi Jasaraharja Putera • All Rights Reserved.</p>
                    <p className="mt-2 md:mt-0 font-medium text-blue-900/70">
                        E-Register & Digital Validation System
                    </p>
                </footer>
            </div>
        </>
    );
}