<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Daftar - EduSpace</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page auth-register min-h-screen bg-[#f7f8fb] text-gray-900 antialiased">
    <main class="min-h-screen grid lg:grid-cols-[0.95fr_1.05fr]">
        <section class="flex items-center justify-center px-4 py-10 sm:px-6 lg:px-10">
            <div class="auth-card-wrap w-full max-w-lg">
                <div class="mb-8">
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
                        <p class="text-sm font-medium text-gray-500">Mulai dalam satu menit</p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-950">Buat akun EduSpace</h1>
                        <p class="mt-2 text-sm text-gray-500">Pilih role sesuai kebutuhan. Setelah daftar kamu langsung masuk ke dashboard.</p>
                    </div>

                    @if ($errors->any())
                        <div role="alert" aria-live="assertive" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <p class="font-semibold">Ada yang perlu dicek:</p>
                            <ul class="mt-1 list-disc space-y-1 pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama lengkap</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                                   class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                                   class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
                        </div>

                        <div>
                            <label for="nim_nip" class="block text-sm font-medium text-gray-700">NIM/NIP <span class="font-normal text-gray-400">(opsional)</span></label>
                            <input id="nim_nip" name="nim_nip" type="text" value="{{ old('nim_nip') }}" autocomplete="off"
                                   class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
                        </div>

                        <fieldset>
                            <legend class="block text-sm font-medium text-gray-700">Role akun</legend>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <label class="cursor-pointer rounded-xl border border-gray-200 bg-white p-3 text-sm transition has-[:checked]:border-gray-900 has-[:checked]:bg-gray-950 has-[:checked]:text-white">
                                    <input type="radio" name="role" value="student" class="sr-only" @checked(old('role', 'student') === 'student')>
                                    <span class="block font-semibold">Mahasiswa</span>
                                    <span class="mt-1 block text-xs opacity-70">Dashboard tugas dan grup</span>
                                </label>
                                <label class="cursor-pointer rounded-xl border border-gray-200 bg-white p-3 text-sm transition has-[:checked]:border-gray-900 has-[:checked]:bg-gray-950 has-[:checked]:text-white">
                                    <input type="radio" name="role" value="lecturer" class="sr-only" @checked(old('role') === 'lecturer')>
                                    <span class="block font-semibold">Dosen</span>
                                    <span class="mt-1 block text-xs opacity-70">Pantau kelas dan penilaian</span>
                                </label>
                            </div>
                        </fieldset>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <input id="password" name="password" type="password" required autocomplete="new-password"
                                       class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                                       class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
                            </div>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-gray-900/15 transition hover:bg-black">
                            Buat akun
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-gray-600">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" data-auth-nav="login" class="font-semibold text-gray-950 underline underline-offset-4">Masuk</a>
                    </p>
                </div>
            </div>
        </section>

        <section class="auth-visual hidden lg:flex relative overflow-hidden bg-gray-950 px-12 py-10 text-white">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_25%,rgba(250,204,21,0.24),transparent_26%),radial-gradient(circle_at_70%_60%,rgba(59,130,246,0.28),transparent_30%)]"></div>
            <div class="relative z-10 flex min-h-full max-w-xl flex-col justify-center">
                <p class="mb-4 text-sm font-medium text-yellow-200">Belajar tanpa tab yang berantakan</p>
                <h2 class="text-5xl font-bold leading-tight tracking-tight">Satu tempat untuk tugas, materi, forum, dan kolaborasi.</h2>
                <div class="mt-8 space-y-3">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="font-semibold">Zen Dashboard</p>
                        <p class="mt-1 text-sm text-gray-300">Prioritas tugas ditampilkan dari yang paling butuh perhatian.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="font-semibold">Contextual Forum</p>
                        <p class="mt-1 text-sm text-gray-300">Diskusi melekat ke tugas, bukan tercecer di banyak tempat.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="font-semibold">Group Hub</p>
                        <p class="mt-1 text-sm text-gray-300">Progress tim terlihat tanpa perlu saling menebak.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
