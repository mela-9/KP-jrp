import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Masuk" />

            <div className="mb-7">
                <p className="text-[10px] font-bold uppercase tracking-wider text-amber-600">Akses Pegawai</p>
                <h1 className="mt-2 text-2xl font-extrabold text-blue-950">Masuk ke sistem</h1>
                <p className="mt-2 text-sm leading-5 text-slate-500">Gunakan akun kerja Anda untuk melanjutkan ke E-Register.</p>
            </div>

            {status && (
                <div className="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <InputLabel htmlFor="email" value="Email" className="font-semibold text-slate-700" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1.5 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-none focus:border-blue-800 focus:ring-blue-800"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Kata sandi" className="font-semibold text-slate-700" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1.5 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-none focus:border-blue-800 focus:ring-blue-800"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="flex items-center justify-between gap-3">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData('remember', e.target.checked)
                            }
                        />
                        <span className="ms-2 text-xs text-slate-600">
                            Ingat saya
                        </span>
                    </label>
                </div>

                <div className="flex items-center justify-between gap-3">
                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="text-xs font-semibold text-blue-900 underline decoration-blue-200 underline-offset-4 hover:text-blue-700"
                        >
                            Lupa kata sandi?
                        </Link>
                    )}

                    <PrimaryButton className="rounded-lg bg-blue-900 px-5 py-2.5 text-xs font-bold tracking-normal hover:bg-blue-800 focus:bg-blue-800 focus:ring-blue-800 active:bg-blue-950" disabled={processing}>
                        {processing ? 'Memproses...' : 'Masuk'}
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
