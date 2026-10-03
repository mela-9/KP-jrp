import React, { useEffect, useState } from 'react';
import { router, usePage } from '@inertiajs/react';

export default function NotificationBell() {
    const [isOpen, setIsOpen] = useState(false);
    const { props } = usePage();
    const sharedNotifications = props.notifications || [];
    const [notifications, setNotifications] = useState(sharedNotifications);
    const isKepalaStaff = props.isKepalaStaff;
    const userId = props.auth?.user?.id;

    useEffect(() => {
        setNotifications(sharedNotifications);
    }, [sharedNotifications]);

    useEffect(() => {
        if (!window.Echo) return undefined;

        const channels = isKepalaStaff
            ? ['notifications.kepala-staff']
            : ['notifications.staff', ...(userId ? [`App.Models.User.${userId}`] : [])];
        const refreshNotifications = (event) => {
            if (event?.notification) {
                setNotifications((current) => [
                    event.notification,
                    ...current.filter((item) => item.id !== event.notification.id),
                ].slice(0, 10));
            }

            router.reload({ only: ['notifications'], preserveScroll: true, preserveState: true });
        };

        channels.forEach((channelName) => {
            window.Echo.private(channelName).listen('.register.notification', refreshNotifications);
        });

        return () => channels.forEach((channelName) => window.Echo.leave(channelName));
    }, [isKepalaStaff, userId]);

    const openTarget = (notification) => {
        setIsOpen(false);
        router.visit(notification.target_url);
    };

    return (
        <div className="relative">
            <button 
                onClick={() => setIsOpen(!isOpen)}
                aria-label={`${notifications.length} notifikasi`}
                className="relative p-2 text-slate-600 hover:text-blue-900 transition focus:outline-none"
            >
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                {notifications.length > 0 && (
                    <span className="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                )}
            </button>

            {isOpen && (
                <div className="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-slate-200 z-50 p-4 space-y-3">
                    <div className="flex justify-between items-center border-b pb-2">
                        <h4 className="font-bold text-xs text-slate-800">
                            {isKepalaStaff ? 'Antrean validasi polis' : 'Pembaruan status polis'}
                        </h4>
                        <span className="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full font-bold">
                            {notifications.length}
                        </span>
                    </div>

                    <div className="space-y-2 max-h-60 overflow-y-auto text-xs">
                        {notifications.length > 0 ? (
                            notifications.map((notif) => {
                                const isPending = notif.status === 'Pending Kepala Staff';
                                const isApproved = notif.status === 'Diparaf Kepala Staff' || notif.status === 'Disetujui';

                                return (
                                    <button
                                        key={notif.id}
                                        onClick={() => openTarget(notif)}
                                        className={`block w-full rounded-md border p-3 text-left transition ${
                                            isPending
                                                ? 'border-amber-200 bg-amber-50 hover:bg-amber-100'
                                                : isApproved
                                                    ? 'border-emerald-200 bg-emerald-50 hover:bg-emerald-100'
                                                    : 'border-red-200 bg-red-50 hover:bg-red-100'
                                        }`}
                                    >
                                        <span className={`font-bold ${isPending ? 'text-amber-800' : isApproved ? 'text-emerald-800' : 'text-red-700'}`}>
                                            {isPending
                                                ? `Polis ${notif.no_polis} meminta validasi.`
                                                : isApproved
                                                    ? `Polis ${notif.no_polis} telah disetujui.`
                                                    : `Polis ${notif.no_polis} perlu revisi.`}
                                        </span>
                                        <span className="mt-1 block text-[10px] text-slate-600">
                                            {isPending
                                                ? `${notif.nama_tertanggung || ''} · ${notif.type} · Buka antrean validasi`
                                                : isApproved
                                                    ? `Diparaf oleh ${notif.paraf_oleh || 'Kepala Staff'} · Buka buku besar`
                                                    : `${notif.catatan_revisi || 'Silakan periksa dan perbarui data.'} · Buka buku besar`}
                                        </span>
                                    </button>
                                );
                            })
                        ) : (
                            <p className="text-center text-slate-400 py-4 italic">
                                {isKepalaStaff ? 'Belum ada polis menunggu validasi.' : 'Belum ada pembaruan status polis.'}
                            </p>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}