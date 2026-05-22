<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'EduSpace' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-white text-gray-900">
<div x-data="{ mobileMenuOpen: false }"
     @keydown.escape.window="mobileMenuOpen = false"
     x-on:livewire:navigated.window="mobileMenuOpen = false"
     class="min-h-screen flex">
    <aside class="hidden md:flex flex-col w-60 border-r border-gray-200 bg-gray-50/50 fixed inset-y-0 left-0 z-30">
        <div class="h-14 flex items-center px-4 border-b border-gray-200">
            <a href="{{ auth()->user()->isLecturer() ? route('lecturer.dashboard') : route('dashboard') }}"
               wire:navigate
               class="flex items-center gap-2 group">
                <div class="w-7 h-7 bg-gray-900 rounded-md flex items-center justify-center">
                    <span class="text-white text-sm font-bold">E</span>
                </div>
                <span class="font-semibold text-gray-900 group-hover:text-black">EduSpace</span>
            </a>
        </div>

        <div class="px-3 py-3 border-b border-gray-200">
            <div class="flex items-center gap-2.5 px-2 py-1.5 rounded-md hover:bg-gray-100 transition">
                <div class="w-7 h-7 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-medium text-white">{{ auth()->user()->initials() }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">
                        {{ auth()->user()->isLecturer() ? 'Dosen' : 'Mahasiswa' }}
                    </p>
                </div>
            </div>
        </div>

        @if(auth()->user()->isStudent())
            <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-y-auto">
                <p class="px-2 pt-2 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Menu</p>

                <a href="{{ route('dashboard') }}" wire:navigate
                   class="flex items-center gap-2.5 px-2 py-1.5 rounded-md text-sm transition {{ request()->routeIs('dashboard') ? 'bg-gray-200/70 text-gray-900 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                    Dashboard
                </a>

                <a href="{{ route('vault') }}" wire:navigate
                   class="flex items-center gap-2.5 px-2 py-1.5 rounded-md text-sm transition {{ request()->routeIs('vault') ? 'bg-gray-200/70 text-gray-900 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                    Materi Kuliah
                </a>

                <div class="pt-4">
                    <p class="px-2 pt-2 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Upcoming</p>
                    @php
                        $upcoming = \App\Models\Assignment::whereHas('course.students', fn($q) => $q->where('users.id', auth()->id()))
                            ->where('due_at', '>=', now())
                            ->orderBy('due_at')
                            ->limit(3)
                            ->get();
                    @endphp

                    @forelse($upcoming as $up)
                        <a href="{{ route('assignments.show', $up) }}" wire:navigate
                           class="block px-2 py-1.5 rounded-md hover:bg-gray-100 transition group">
                            <div class="flex items-start gap-2">
                                <div class="w-1 h-8 rounded-full mt-0.5 flex-shrink-0"
                                     style="background-color: {{ $up->course->color }}"></div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-gray-700 truncate group-hover:text-gray-900">
                                        {{ $up->title }}
                                    </p>
                                    <p class="text-[10px] text-gray-500">
                                        {{ $up->due_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <p class="px-2 py-1.5 text-xs text-gray-400 italic">Tidak ada deadline</p>
                    @endforelse
                </div>
            </nav>
        @endif

        @if(auth()->user()->isLecturer())
            <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-y-auto">
                <p class="px-2 pt-2 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Pengajar</p>

                <a href="{{ route('lecturer.dashboard') }}" wire:navigate
                   class="flex items-center gap-2.5 px-2 py-1.5 rounded-md text-sm transition {{ request()->routeIs('lecturer.dashboard') ? 'bg-gray-200/70 text-gray-900 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                    Pusat Kelas
                </a>

                <a href="{{ route('lecturer.vault') }}" wire:navigate
                   class="flex items-center gap-2.5 px-2 py-1.5 rounded-md text-sm transition {{ request()->routeIs('lecturer.vault') ? 'bg-gray-200/70 text-gray-900 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                    Bank Materi
                </a>

                <p class="px-2 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Kelas Saya</p>
                @foreach(auth()->user()->teachingCourses as $c)
                    <a href="{{ route('lecturer.courses.show', $c) }}" wire:navigate
                       class="px-2 py-1.5 rounded-md text-sm flex items-center gap-2.5 transition
                              {{ request()->routeIs('lecturer.courses.show') && request()->route('course')?->id === $c->id ? 'bg-gray-200/70 text-gray-900 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="w-2 h-2 rounded-full" style="background-color: {{ $c->color }}"></div>
                        <span class="truncate">{{ $c->name }}</span>
                    </a>
                @endforeach
            </nav>
        @endif

        <div class="border-t border-gray-200 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-md text-sm text-gray-700 hover:bg-gray-100 transition">
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <div x-cloak x-show="mobileMenuOpen" class="md:hidden fixed inset-0 z-40">
        <div x-show="mobileMenuOpen"
             x-transition.opacity
             @click="mobileMenuOpen = false"
             class="absolute inset-0 bg-black/40"></div>

        <aside x-show="mobileMenuOpen"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="relative h-full w-[84vw] max-w-xs bg-white shadow-2xl flex flex-col">
            <div class="h-14 flex items-center justify-between px-4 border-b border-gray-200">
                <a href="{{ auth()->user()->isLecturer() ? route('lecturer.dashboard') : route('dashboard') }}"
                   wire:navigate
                   class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-gray-900 rounded-md flex items-center justify-center">
                        <span class="text-white text-sm font-bold">E</span>
                    </div>
                    <span class="font-semibold text-gray-900">EduSpace</span>
                </a>
                <button type="button"
                        @click="mobileMenuOpen = false"
                        class="w-9 h-9 flex items-center justify-center rounded-md hover:bg-gray-100"
                        aria-label="Tutup menu">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-4 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                        <span class="text-xs font-medium text-white">{{ auth()->user()->initials() }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ auth()->user()->isLecturer() ? 'Dosen' : 'Mahasiswa' }}
                        </p>
                    </div>
                </div>
            </div>

            @if(auth()->user()->isStudent())
                <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-1">
                    <p class="px-2 pt-2 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Menu</p>

                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('dashboard') ? 'bg-gray-900 text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a2 2 0 012-2h4v8H4V5zm10-2h4a2 2 0 012 2v4h-6V3zM4 15h6v6H6a2 2 0 01-2-2v-4zm10-2h6v6a2 2 0 01-2 2h-4v-8z"/>
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('vault') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('vault') ? 'bg-gray-900 text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                        </svg>
                        Materi Kuliah
                    </a>

                    <div class="pt-4">
                        <p class="px-2 pt-2 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Deadline Dekat</p>
                        @php
                            $mobileUpcoming = \App\Models\Assignment::whereHas('course.students', fn($q) => $q->where('users.id', auth()->id()))
                                ->where('due_at', '>=', now())
                                ->orderBy('due_at')
                                ->limit(4)
                                ->get();
                        @endphp

                        @forelse($mobileUpcoming as $up)
                            <a href="{{ route('assignments.show', $up) }}" wire:navigate
                               class="block px-3 py-2 rounded-lg hover:bg-gray-100 transition">
                                <p class="text-xs font-medium text-gray-800 truncate">{{ $up->title }}</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">{{ $up->course->code }} · {{ $up->due_at->diffForHumans() }}</p>
                            </a>
                        @empty
                            <p class="px-3 py-2 text-xs text-gray-400 italic">Tidak ada deadline</p>
                        @endforelse
                    </div>
                </nav>
            @endif

            @if(auth()->user()->isLecturer())
                <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-1">
                    <p class="px-2 pt-2 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Pengajar</p>

                    <a href="{{ route('lecturer.dashboard') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('lecturer.dashboard') || request()->routeIs('lecturer.courses.show') ? 'bg-gray-900 text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a2 2 0 012-2h4v8H4V5zm10-2h4a2 2 0 012 2v4h-6V3zM4 15h6v6H6a2 2 0 01-2-2v-4zm10-2h6v6a2 2 0 01-2 2h-4v-8z"/>
                        </svg>
                        Pusat Kelas
                    </a>

                    <a href="{{ route('lecturer.vault') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('lecturer.vault') ? 'bg-gray-900 text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                        </svg>
                        Bank Materi
                    </a>

                    <p class="px-2 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Kelas Saya</p>
                    @foreach(auth()->user()->teachingCourses as $c)
                        <a href="{{ route('lecturer.courses.show', $c) }}" wire:navigate
                           class="px-3 py-2 rounded-lg text-sm flex items-center gap-2.5 transition
                                  {{ request()->routeIs('lecturer.courses.show') && request()->route('course')?->id === $c->id ? 'bg-gray-900 text-white font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                            <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $c->color }}"></div>
                            <span class="truncate">{{ $c->name }}</span>
                        </a>
                    @endforeach
                </nav>
            @endif

            <div class="border-t border-gray-200 p-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-100 transition">
                        Keluar
                    </button>
                </form>
            </div>
        </aside>
    </div>

    <div class="flex-1 md:ml-60 flex flex-col min-h-screen">
        <header class="h-14 border-b border-gray-200 bg-white sticky top-0 z-20 flex items-center justify-between px-4 md:px-6">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button"
                        @click="mobileMenuOpen = true"
                        class="md:hidden w-9 h-9 flex items-center justify-center rounded-md hover:bg-gray-100"
                        aria-label="Buka menu">
                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
                <h1 class="text-sm font-medium text-gray-700 truncate">{{ $title ?? 'EduSpace' }}</h1>
            </div>

            <div class="flex items-center gap-2">
                <livewire:notification-hub />
            </div>
        </header>

        <main class="flex-1 p-4 pb-28 md:p-8 max-w-7xl mx-auto w-full">
            {{ $slot }}
        </main>
    </div>
</div>

<nav class="md:hidden fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/85 pb-[env(safe-area-inset-bottom)]">
    @if(auth()->user()->isStudent())
        <div class="grid grid-cols-2">
            <a href="{{ route('dashboard') }}" wire:navigate
               class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium {{ request()->routeIs('dashboard') ? 'text-gray-900' : 'text-gray-500' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a2 2 0 012-2h4v8H4V5zm10-2h4a2 2 0 012 2v4h-6V3zM4 15h6v6H6a2 2 0 01-2-2v-4zm10-2h6v6a2 2 0 01-2 2h-4v-8z"/>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('vault') }}" wire:navigate
               class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium {{ request()->routeIs('vault') ? 'text-gray-900' : 'text-gray-500' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                </svg>
                Materi Kuliah
            </a>
        </div>
    @endif

    @if(auth()->user()->isLecturer())
        <div class="grid grid-cols-2">
            <a href="{{ route('lecturer.dashboard') }}" wire:navigate
               class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium {{ request()->routeIs('lecturer.dashboard') || request()->routeIs('lecturer.courses.show') ? 'text-gray-900' : 'text-gray-500' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a2 2 0 012-2h4v8H4V5zm10-2h4a2 2 0 012 2v4h-6V3zM4 15h6v6H6a2 2 0 01-2-2v-4zm10-2h6v6a2 2 0 01-2 2h-4v-8z"/>
                </svg>
                Pusat Kelas
            </a>
            <a href="{{ route('lecturer.vault') }}" wire:navigate
               class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium {{ request()->routeIs('lecturer.vault') ? 'text-gray-900' : 'text-gray-500' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                </svg>
                Bank Materi
            </a>
        </div>
    @endif
</nav>

<div x-data="{ toasts: [] }"
     @toast.window="
        const toast = Object.assign({ id: Date.now() }, $event.detail);
        toasts.push(toast);
        if (!toast.persist) {
            setTimeout(() => { toasts = toasts.filter(t => t.id !== toast.id) }, toast.duration || 3000);
        }
     "
     class="fixed bottom-24 left-3 right-3 md:bottom-6 md:left-auto md:right-6 z-50 space-y-2">
    <template x-for="t in toasts" :key="t.id">
        <div x-transition class="bg-gray-900 text-white text-sm px-4 py-3 rounded-lg shadow-lg md:min-w-[280px] flex items-center justify-between gap-3">
            <span x-text="t.message"></span>
        </div>
    </template>
</div>

@livewireScripts
</body>
</html>
