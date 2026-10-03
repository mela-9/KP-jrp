import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="flex min-h-screen flex-col bg-slate-50 text-slate-800">
            <header className="flex h-20 items-center justify-between border-b border-slate-200 bg-white px-5 sm:px-8 lg:px-12">
                <Link href="/" className="flex items-center gap-3">
                    <span className="rounded-lg bg-blue-900 px-3 py-1.5 text-lg font-black tracking-wider text-white">JRP</span>
                    <span>
                        <span className="block text-xs font-bold text-blue-950 sm:text-sm">ASURANSI JASARAHARJA PUTERA</span>
                        <span className="block text-[10px] text-slate-500">E-Register & Digital Validation System</span>
                    </span>
                </Link>
                <Link href="/" className="text-xs font-semibold text-blue-900 transition hover:text-blue-700 sm:text-sm">
                    Kembali ke beranda
                </Link>
            </header>

            <main className="flex flex-1 items-center justify-center px-4 py-8 sm:px-8 sm:py-12">
                <div className="grid w-full max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl md:min-h-[520px] md:grid-cols-2">
                    <section className="relative flex flex-col justify-between overflow-hidden bg-blue-950 p-7 text-white sm:p-10">
                        <div className="absolute inset-y-0 right-0 w-2 bg-amber-400" />
                        <div className="relative">
                            <span className="inline-flex items-center rounded-full border border-blue-700 bg-blue-900/70 px-3 py-1 text-[10px] font-bold uppercase text-amber-300">
                                Official Portal
                            </span>
                            <div className="mt-10 flex h-16 w-16 items-center justify-center rounded-xl border border-blue-700 bg-blue-900 text-xl font-black tracking-wider text-amber-300">
                                JRP
                            </div>
                            <h2 className="mt-6 max-w-sm text-3xl font-extrabold leading-tight sm:text-4xl">
                                E-Register & Validasi Digital
                            </h2>
                            <p className="mt-4 max-w-sm text-sm leading-6 text-blue-100">
                                Portal pencatatan buku besar polis dan validasi dokumen untuk operasional Jasaraharja Putera.
                            </p>
                        </div>
                        <div className="relative mt-10 border-t border-blue-800 pt-4 text-xs text-blue-200">
                            Sistem administrasi polis terpadu
                        </div>
                    </section>

                    <section className="flex items-center justify-center p-6 sm:p-10">
                        <div className="w-full max-w-sm">{children}</div>
                    </section>
                </div>
            </main>
        </div>
    );
}
