<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Masuk - EduSpace</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page auth-login min-h-screen bg-[#f7f8fb] text-gray-900 antialiased">
    <main class="min-h-screen grid lg:grid-cols-[1.05fr_0.95fr]">
        <section class="auth-visual hidden lg:flex relative overflow-hidden bg-gray-950 text-white px-12 py-10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(59,130,246,0.35),transparent_28%),radial-gradient(circle_at_70%_70%,rgba(16,185,129,0.24),transparent_24%)]"></div>
            <div class="relative z-10 flex min-h-full flex-col justify-between max-w-xl">
                <a href="{{ route('landing') }}" data-auth-nav="landing" class="auth-brand inline-flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-sm font-bold text-gray-950">E</span>
                    <span class="font-semibold">EduSpace</span>
                </a>

                <div>
                    <p class="mb-4 text-sm font-medium text-emerald-200">Zen Dashboard untuk belajar yang lebih tenang</p>
                    <h1 class="text-5xl font-bold leading-tight tracking-tight">Masuk, lihat prioritas, lalu lanjut kerja tanpa ribet.</h1>
                    <p class="mt-5 max-w-lg text-base leading-7 text-gray-300">
                        Deadline, materi, notifikasi, dan progress kelompok disusun supaya kamu tahu langkah berikutnya tanpa perlu mencari-cari.
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-3 text-sm">
                    <div class="rounded-xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-2xl font-bold">H-1</p>
                        <p class="mt-1 text-gray-300">prioritas jelas</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-2xl font-bold">1</p>
                        <p class="mt-1 text-gray-300">hub tugas</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-2xl font-bold">0</p>
                        <p class="mt-1 text-gray-300">noise ekstra</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center px-4 py-10 sm:px-6 lg:px-10">
            <div class="auth-card-wrap w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <a href="{{ route('landing') }}" data-auth-nav="landing" class="auth-brand inline-flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-900 text-sm font-bold text-white">E</span>
                        <span class="font-semibold">EduSpace</span>
                    </a>
                </div>

                <a href="{{ route('landing') }}" data-auth-nav="landing" class="mb-4 inline-flex items-center gap-2 rounded-full px-2 py-1 text-sm font-medium text-gray-500 transition hover:bg-white hover:text-gray-900">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali ke landing
                </a>

                <div class="auth-card rounded-2xl border border-gray-200 bg-white p-6 shadow-xl shadow-gray-200/60">
                    <div class="mb-6">
                        <p class="text-sm font-medium text-gray-500">Selamat datang kembali</p>
                        <h2 class="mt-1 text-2xl font-bold tracking-tight text-gray-950">Masuk ke EduSpace</h2>
                        <p class="mt-2 text-sm text-gray-500">Pakai akun demo atau akun yang sudah kamu buat.</p>
                    </div>

                    @if (session('status'))
                        <div role="status" aria-live="polite" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            <p class="font-semibold">Berhasil</p>
                            <p class="mt-1">{{ session('status') }}</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div role="alert" aria-live="assertive" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <p class="font-semibold">Login belum berhasil</p>
                            <ul class="mt-1 list-disc space-y-1 pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-5 grid gap-2 rounded-xl border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Akun demo</p>
                        <div class="grid gap-2">
                            <button type="button" data-demo-email="raka@eduspace.id" class="demo-login flex items-center justify-between rounded-lg bg-white px-3 py-2 text-left text-xs text-gray-600 ring-1 ring-gray-200 transition hover:bg-gray-100">
                                <span><strong class="text-gray-900">Raka</strong> mahasiswa</span>
                                <span class="font-mono text-gray-500">raka@eduspace.id</span>
                            </button>
                            <button type="button" data-demo-email="dimas@eduspace.id" class="demo-login flex items-center justify-between rounded-lg bg-white px-3 py-2 text-left text-xs text-gray-600 ring-1 ring-gray-200 transition hover:bg-gray-100">
                                <span><strong class="text-gray-900">Dimas</strong> ketua kelompok</span>
                                <span class="font-mono text-gray-500">dimas@eduspace.id</span>
                            </button>
                            <button type="button" data-demo-email="clara@eduspace.id" class="demo-login flex items-center justify-between rounded-lg bg-white px-3 py-2 text-left text-xs text-gray-600 ring-1 ring-gray-200 transition hover:bg-gray-100">
                                <span><strong class="text-gray-900">Clara</strong> dosen</span>
                                <span class="font-mono text-gray-500">clara@eduspace.id</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500">Klik salah satu untuk mengisi form. Password demo: <span class="font-mono text-gray-800">password</span></p>
                    </div>

                    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                   class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <span class="text-xs text-gray-400">Minimal 8 karakter untuk akun baru</span>
                            </div>
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                   class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                            Ingat saya
                        </label>

                        <button type="submit" class="w-full rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-gray-900/15 transition hover:bg-black">
                            Masuk
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-gray-600">
                        Belum punya akun?
                        <a href="{{ route('register') }}" data-auth-nav="register" class="font-semibold text-gray-950 underline underline-offset-4">Daftar sekarang</a>
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('.demo-login').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('email').value = button.dataset.demoEmail;
                document.getElementById('password').value = 'password';
            });
        });
    </script>
</body>
</html>
