<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'EduSpace' }}</title>
    <meta name="description" content="Platform pembelajaran modern yang menggantikan Google Classroom. Fokus pada Zen experience, anti-stres, dan dukungan kolaborasi tim.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-white text-gray-900">
    {{-- NAVBAR --}}
    <nav x-data="{ scrolled: false, mobileMenu: false }"
         @scroll.window="scrolled = window.scrollY > 20"
         :class="scrolled ? 'bg-white/80 backdrop-blur-md border-b border-gray-200' : 'bg-transparent'"
         class="fixed top-0 inset-x-0 z-50 transition-all duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('landing') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center">
                        <span class="text-white text-sm font-bold">E</span>
                    </div>
                    <span class="font-bold text-gray-900">EduSpace</span>
                </a>

                {{-- Desktop nav --}}
                <div class="hidden md:flex items-center gap-1">
                    <a href="#features" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition">Fitur</a>
                    <a href="#personas" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition">Untuk Siapa</a>
                    <a href="#workflow" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition">Cara Kerja</a>
                </div>

                {{-- CTA --}}
                <div class="hidden md:flex items-center gap-2">
                    <a href="{{ route('login') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition">
                        Mulai Gratis
                    </a>
                </div>

                {{-- Mobile menu button --}}
                <button @click="mobileMenu = !mobileMenu" class="md:hidden w-9 h-9 flex items-center justify-center">
                    <svg x-show="!mobileMenu" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenu" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Mobile menu --}}
            <div x-show="mobileMenu" x-cloak x-transition class="md:hidden py-4 border-t border-gray-200">
                <a href="#features" @click="mobileMenu = false" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">Fitur</a>
                <a href="#personas" @click="mobileMenu = false" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">Untuk Siapa</a>
                <a href="#workflow" @click="mobileMenu = false" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">Cara Kerja</a>
                <div class="border-t border-gray-200 mt-2 pt-2 space-y-1">
                    <a href="{{ route('login') }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Masuk</a>
                    <a href="{{ route('register') }}" class="block px-3 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg text-center">Mulai Gratis</a>
                </div>
            </div>
        </div>
    </nav>

    {{-- CONTENT --}}
    <main>{{ $slot }}</main>

    {{-- FOOTER --}}
    <footer class="border-t border-gray-200 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="col-span-2">
                    <a href="{{ route('landing') }}" class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center">
                            <span class="text-white text-sm font-bold">E</span>
                        </div>
                        <span class="font-bold text-gray-900">EduSpace</span>
                    </a>
                    <p class="text-sm text-gray-600 max-w-sm">
                        Platform pembelajaran yang dirancang dengan empati. Belajar lebih tenang, kolaborasi lebih mulus, mengajar lebih efisien.
                    </p>
                </div>

                <div>
                    <h4 class="text-xs font-semibold text-gray-900 uppercase tracking-wider mb-3">Produk</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="#features" class="hover:text-gray-900 transition">Fitur</a></li>
                        <li><a href="#personas" class="hover:text-gray-900 transition">Untuk Siapa</a></li>
                        <li><a href="#workflow" class="hover:text-gray-900 transition">Cara Kerja</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-semibold text-gray-900 uppercase tracking-wider mb-3">Mulai</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="{{ route('login') }}" class="hover:text-gray-900 transition">Masuk</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-gray-900 transition">Daftar</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-xs text-gray-500">© 2025 EduSpace. Tugas UX Kelompok 6.</p>
                <p class="text-xs text-gray-500">Built with Laravel + Livewire + TailwindCSS</p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
