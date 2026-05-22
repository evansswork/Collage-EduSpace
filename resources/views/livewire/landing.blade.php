<div>

    {{-- ========== HERO SECTION ========== --}}
    <section class="relative pt-32 pb-20 overflow-hidden">
        {{-- Background decoration --}}
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-20 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-gradient-to-br from-blue-50 via-purple-50 to-transparent rounded-full blur-3xl opacity-50"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">

                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-full mb-6 shadow-sm">
                    <span class="text-xs font-medium text-gray-700">Versi Beta — Akses Gratis</span>
                </div>

                {{-- Headline --}}
                <h1 class="text-4xl md:text-6xl font-bold text-gray-900 leading-[1.1] tracking-tight">
                    Belajar lebih
                    <span class="relative inline-block">
                        <span class="relative z-10">tenang</span>
                    </span>,
                    <br class="hidden md:block">
                    bukan lebih sibuk.
                </h1>

                <p class="mt-6 text-base md:text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    EduSpace adalah platform pembelajaran yang dirancang dengan empati.
                    Mengganti chaos Google Classroom dengan <strong class="text-gray-900">Zen Dashboard</strong>,
                    forum kontekstual, dan tools kolaborasi yang manusiawi.
                </p>

                {{-- CTAs --}}
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('register') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-xl transition shadow-lg shadow-gray-900/20 hover:shadow-xl hover:shadow-gray-900/30 group">
                        Mulai Sekarang — Gratis
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                    <a href="{{ route('login') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-white hover:bg-gray-50 text-gray-900 text-sm font-semibold rounded-xl border border-gray-200 transition">
                        Sudah punya akun? Masuk
                    </a>
                </div>

                {{-- Trust line --}}
                <p class="mt-6 text-xs text-gray-500">
                    Cocok untuk mahasiswa, ketua kelompok, dan dosen · Tidak perlu kartu kredit
                </p>
            </div>

            {{-- Hero Visual: Dashboard Mockup --}}
            <div class="mt-16 relative max-w-5xl mx-auto">
                <div class="absolute -inset-4 bg-gradient-to-r from-blue-100 via-purple-100 to-pink-100 rounded-3xl blur-2xl opacity-40"></div>

                <div class="relative bg-white border border-gray-200 rounded-2xl shadow-2xl shadow-gray-900/10 overflow-hidden">
                    {{-- Browser chrome --}}
                    <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 flex items-center gap-2">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        </div>
                        <div class="flex-1 flex justify-center">
                            <div class="bg-white border border-gray-200 rounded-md px-3 py-1 text-xs text-gray-500 font-mono">
                                eduspace.id/dashboard
                            </div>
                        </div>
                    </div>

                    {{-- Dashboard mockup --}}
                    <div class="p-6 bg-gradient-to-br from-white to-gray-50/50">
                        {{-- Urgency banner --}}
                        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-5 text-white mb-4 shadow-lg shadow-red-500/10">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold uppercase">
                                    URGENT · H-1
                                </span>
                                <span class="text-xs opacity-90">Rekayasa Perangkat Lunak</span>
                            </div>
                            <h3 class="text-lg font-bold">Tugas UML Diagram — Deadline Besok</h3>
                            <p class="text-xs opacity-90 mt-1">Tersisa 23 jam</p>
                        </div>

                        {{-- Stats row --}}
                        <div class="grid grid-cols-3 gap-3">
                            {{-- Progress Ring --}}
                            <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col items-center">
                                <div class="relative w-20 h-20">
                                    <svg class="transform -rotate-90 w-full h-full">
                                        <circle cx="40" cy="40" r="32" fill="none" stroke="#F3F4F6" stroke-width="8"/>
                                        <circle cx="40" cy="40" r="32" fill="none" stroke="#F59E0B" stroke-width="8"
                                                stroke-dasharray="201" stroke-dashoffset="80" stroke-linecap="round"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-lg font-bold text-amber-600">60%</span>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-2">Minggu Ini</p>
                            </div>

                            {{-- Mini cards --}}
                            <div class="col-span-2 space-y-2">
                                <div class="bg-white border border-gray-200 rounded-xl p-3 flex items-center gap-2.5">
                                    <div class="w-1 h-8 bg-blue-500 rounded-full"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-gray-900 truncate">Metode Numerik — Newton Raphson</p>
                                        <p class="text-[10px] text-gray-500">Dalam 3 hari</p>
                                    </div>
                                    <span class="text-[10px] font-medium text-amber-600">H-3</span>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-xl p-3 flex items-center gap-2.5">
                                    <div class="w-1 h-8 bg-amber-500 rounded-full"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-gray-900 truncate">UTS Basis Data — ERD</p>
                                        <p class="text-[10px] text-gray-500">3 minggu lagi</p>
                                    </div>
                                    <span class="text-[10px] font-medium text-gray-600">Normal</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Floating notification card --}}
                <div class="hidden md:block absolute -right-8 top-1/3 bg-white border border-gray-200 rounded-xl shadow-xl shadow-gray-900/10 p-4 max-w-xs animate-float">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-900">Achievement Unlocked!</p>
                            <p class="text-[10px] text-gray-500 mt-0.5">The Early Bird — Submit sebelum H-1</p>
                        </div>
                    </div>
                </div>

                {{-- Floating progress card --}}
                <div class="hidden md:block absolute -left-8 bottom-1/3 bg-white border border-gray-200 rounded-xl shadow-xl shadow-gray-900/10 p-3 max-w-[200px] animate-float-delayed">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Progress Tim</p>
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-gray-700 w-16 truncate">Dimas</span>
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500" style="width: 100%"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-gray-700 w-16 truncate">Raka</span>
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-500" style="width: 75%"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-gray-700 w-16 truncate">Bayu</span>
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-red-500" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== SOCIAL PROOF / STATS ========== --}}
    <section class="py-12 border-y border-gray-200 bg-gray-50/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <p class="text-3xl md:text-4xl font-bold text-gray-900">87%</p>
                    <p class="text-xs text-gray-500 mt-1">Stres berkurang</p>
                </div>
                <div>
                    <p class="text-3xl md:text-4xl font-bold text-gray-900">3.5×</p>
                    <p class="text-xs text-gray-500 mt-1">Lebih cepat menilai</p>
                </div>
                <div>
                    <p class="text-3xl md:text-4xl font-bold text-gray-900">0</p>
                    <p class="text-xs text-gray-500 mt-1">Drama grup chat</p>
                </div>
                <div>
                    <p class="text-3xl md:text-4xl font-bold text-gray-900">10s</p>
                    <p class="text-xs text-gray-500 mt-1">Undo grace period</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== PROBLEM SECTION ========== --}}
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-12">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Masalah Klasik</p>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
                    Google Classroom udah <span class="line-through text-gray-400">cukup</span> bikin pusing.
                </h2>
                <p class="mt-4 text-base text-gray-600">
                    Banyak frustasi kecil yang menumpuk jadi besar. Kami dengarkan, lalu desain ulang.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">Linimasa berantakan</h3>
                    <p class="text-sm text-gray-600">Materi tertimbun chat. Cari tugas yang due besok aja jadi adventure tersendiri.</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">Nagih kelompok itu canggung</h3>
                    <p class="text-sm text-gray-600">Ketua kelompok jadi "orang jahat" yang harus chat personal nagih kontribusi.</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">Dosen kewalahan</h3>
                    <p class="text-sm text-gray-600">Menilai 100+ submission satu per satu, balasan chat WhatsApp menumpuk soal tugas yang sama.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== PERSONAS SECTION ========== --}}
    <section id="personas" class="py-20 bg-gradient-to-b from-gray-50/50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-12">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Untuk Siapa</p>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
                    Dirancang untuk <span class="text-blue-600">3 peran berbeda</span>
                </h2>
                <p class="mt-4 text-base text-gray-600">
                    Setiap peran punya kebutuhan unik. EduSpace memberikan pengalaman yang dipersonalisasi.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- MURID --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-lg hover:-translate-y-1 transition group">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0115 0"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Mahasiswa</h3>
                    <p class="text-xs text-blue-600 font-medium mb-3">Persona: Raka</p>
                    <p class="text-sm text-gray-600 mb-4">
                        Si pelupa deadline yang butuh ditegur sistem dengan ramah. Suka organisasi rapi tanpa effort.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Zen Dashboard yang anti-overwhelming
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            10 detik undo setelah submit
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Achievement biar makin semangat
                        </li>
                    </ul>
                </div>

                {{-- KETUA --}}
                <div class="bg-white border-2 border-amber-200 rounded-2xl p-6 hover:shadow-lg hover:-translate-y-1 transition group relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-amber-500 text-white text-[10px] font-bold uppercase rounded-full">
                        Most Loved
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h3m6-7a3 3 0 11-6 0 3 3 0 016 0zm6 2a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Ketua Kelompok</h3>
                    <p class="text-xs text-amber-600 font-medium mb-3">Persona: Dimas</p>
                    <p class="text-sm text-gray-600 mb-4">
                        Si penyelamat tim yang selalu jadi tumbal nagih anggota. Butuh tools yang lebih sopan dari WhatsApp chat.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            One-Click Gentle Nudge dari sistem
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Progress tracker anti-manipulasi
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Eskalasi ke dosen sebagai last resort
                        </li>
                    </ul>
                </div>

                {{-- DOSEN --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-lg hover:-translate-y-1 transition group">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m-5-3l5 3 5-3"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Dosen</h3>
                    <p class="text-xs text-purple-600 font-medium mb-3">Persona: Bu Clara</p>
                    <p class="text-sm text-gray-600 mb-4">
                        Sang pengajar yang harus mengelola ratusan murid lintas mata kuliah. Butuh efisiensi tanpa kehilangan personal touch.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Pusat Kelas — status sekilas
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            One-Screen Grading split-view
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            AI sebagai saran, bukan vonis
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== FEATURES SHOWCASE ========== --}}
    <section id="features" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Fitur Andalan</p>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
                    Dirancang dengan <span class="text-purple-600">empati</span>, bukan asal jadi.
                </h2>
            </div>

            <div class="space-y-20">

                {{-- Feature 1: Zen Dashboard --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold mb-3">
                            Zen Experience
                        </span>
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">
                            Lihat yang penting <em>duluan</em>.
                        </h3>
                        <p class="text-base text-gray-600 mb-6">
                            Urgency-First Banner di paling atas, Progress Ring yang glanceable, dan Notification Hub yang memisahkan "wajib dikerjakan" dari "informasi lainnya".
                        </p>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500">•</span>
                                Banner H-1 dengan animasi pulsing untuk tugas mendesak
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500">•</span>
                                Progress Ring berubah warna sesuai pencapaian
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500">•</span>
                                Achievement micro-rewards untuk dopamine hit
                            </li>
                        </ul>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-6 border border-gray-200">
                        {{-- Mock visual --}}
                        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-4 text-white shadow-lg mb-3">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[10px] font-bold uppercase">URGENT · H-1</span>
                            </div>
                            <p class="font-bold text-sm">Tugas UML Diagram</p>
                            <p class="text-[10px] opacity-90">23 jam tersisa</p>
                        </div>
                        <div class="bg-white rounded-xl p-4 flex items-center gap-4 shadow-sm">
                            <div class="relative w-16 h-16 flex-shrink-0">
                                <svg class="transform -rotate-90 w-full h-full">
                                    <circle cx="32" cy="32" r="26" fill="none" stroke="#F3F4F6" stroke-width="6"/>
                                    <circle cx="32" cy="32" r="26" fill="none" stroke="#22C55E" stroke-width="6"
                                            stroke-dasharray="163" stroke-dashoffset="40" stroke-linecap="round"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-sm font-bold text-green-600">75%</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-900">Progress Minggu Ini</p>
                                <p class="text-[10px] text-gray-500">3 dari 4 tugas selesai</p>
                                <p class="text-[10px] text-green-600 mt-1">Sudah lebih dari setengah jalan</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Feature 2: Group Hub --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div class="md:order-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-semibold mb-3">
                            Collaborative Group Hub
                        </span>
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">
                            Nagih anggota tanpa drama chat.
                        </h3>
                        <p class="text-base text-gray-600 mb-6">
                            Ketua kelompok bisa kirim "Gentle Nudge" yang dikirim atas nama sistem, bukan personal. Hubungan pertemanan tetap aman, tugas tetap selesai.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="text-amber-500">•</span>
                                Task-Based Progress (anti-manipulasi self-report)
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-amber-500">•</span>
                                Wajib upload bukti file untuk mark as done
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-amber-500">•</span>
                                Eskalasi ke dosen setelah 3× nudge tanpa respons
                            </li>
                        </ul>
                    </div>
                    <div class="md:order-1 bg-gradient-to-br from-amber-50 to-yellow-50 rounded-2xl p-6 border border-gray-200">
                        <div class="bg-white rounded-xl p-4 shadow-sm">
                            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Progress Anggota</p>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                                        <span class="text-[9px] font-medium text-white">DA</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-1 mb-1">
                                            <span class="text-xs font-medium">Dimas</span>
                                        </div>
                                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-green-500" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-green-600 w-8 text-right">100%</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                                        <span class="text-[9px] font-medium text-white">BS</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-medium mb-1">Bayu</p>
                                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-red-500" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <button class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 rounded text-[10px] font-medium hover:bg-gray-200">
                                        Nudge
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Feature 3: Pusat Kelas --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-50 text-purple-700 rounded-full text-xs font-semibold mb-3">
                            Pusat Kelas
                        </span>
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">
                            Pantau 100+ murid dalam sekali lirik.
                        </h3>
                        <p class="text-base text-gray-600 mb-6">
                            Color-coded Status Matrix membuat statusnya glanceable. Bulk action untuk reminder massal dalam 2 klik. AI Assist sebagai saran (bukan vonis).
                        </p>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="text-purple-500">•</span>
                                Tabel warna hijau/kuning/merah untuk status
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-purple-500">•</span>
                                One-Screen Grading split-view (no tab switching)
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-purple-500">•</span>
                                AI Similarity dengan tombol "Abaikan"
                            </li>
                        </ul>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-6 border border-gray-200">
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="grid grid-cols-5 gap-1 p-3 text-[10px]">
                                @for($i = 0; $i < 25; $i++)
                                    @php $rand = ['bg-green-500', 'bg-green-500', 'bg-green-500', 'bg-amber-500', 'bg-red-500'][rand(0,4)]; @endphp
                                    <div class="aspect-square rounded {{ $rand }}"></div>
                                @endfor
                            </div>
                            <div class="border-t border-gray-100 p-3 flex items-center gap-3 text-[10px]">
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Hijau: Selesai</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span> Telat</span>
                                <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> Belum</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== WORKFLOW SECTION ========== --}}
    <section id="workflow" class="py-20 bg-gray-50/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-12">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Cara Kerja</p>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
                    Mulai dalam <span class="text-green-600">3 langkah</span> sederhana
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <div class="relative">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 h-full">
                        <div class="w-10 h-10 bg-gray-900 text-white rounded-full flex items-center justify-center font-bold text-sm mb-4">1</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Daftar Akun</h3>
                        <p class="text-sm text-gray-600">Pilih role kamu: Murid atau Dosen. Verifikasi email, dan kamu siap masuk dashboard.</p>
                    </div>
                    <div class="hidden md:block absolute top-1/2 -right-3 w-6 h-px bg-gray-300 z-10"></div>
                </div>

                <div class="relative">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 h-full">
                        <div class="w-10 h-10 bg-gray-900 text-white rounded-full flex items-center justify-center font-bold text-sm mb-4">2</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Join Kelas / Buat Kelas</h3>
                        <p class="text-sm text-gray-600">Murid: enroll ke kelas dosenmu. Dosen: bikin kelas baru dan undang murid via kode kelas.</p>
                    </div>
                    <div class="hidden md:block absolute top-1/2 -right-3 w-6 h-px bg-gray-300 z-10"></div>
                </div>

                <div>
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 h-full">
                        <div class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center font-bold text-sm mb-4">3</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Mulai Belajar</h3>
                        <p class="text-sm text-gray-600">Nikmati Zen Dashboard, kumpulkan tugas dengan Undo Grace Period, dan kolaborasi tanpa drama.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== TESTIMONIAL ========== --}}
    <section class="py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="currentColor" viewBox="0 0 32 32">
                <path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z"/>
            </svg>
            <p class="text-2xl md:text-3xl text-gray-900 font-medium leading-relaxed">
                "EduSpace bukan cuma ganti Google Classroom. Ini ganti cara saya
                <span class="text-blue-600">merasakan</span> kuliah."
            </p>
            <div class="mt-6 inline-flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gray-900 flex items-center justify-center">
                    <span class="text-xs font-medium text-white">RP</span>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Raka Pratama</p>
                    <p class="text-xs text-gray-500">Mahasiswa Teknik Informatika</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== FINAL CTA ========== --}}
    <section class="py-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative bg-gray-900 rounded-3xl overflow-hidden">
                {{-- Decoration --}}
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-blue-500 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-500 rounded-full blur-3xl"></div>
                </div>

                <div class="relative px-8 py-16 md:py-20 text-center">
                    <h2 class="text-3xl md:text-5xl font-bold text-white leading-tight">
                        Siap untuk belajar lebih tenang?
                    </h2>
                    <p class="mt-4 text-base md:text-lg text-gray-300 max-w-xl mx-auto">
                        Gratis selamanya untuk versi beta. Tidak ada kartu kredit, tidak ada commitment.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a href="{{ route('register') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-white hover:bg-gray-100 text-gray-900 text-sm font-semibold rounded-xl transition group">
                            Daftar Gratis Sekarang
                            <svg class="w-4 h-4 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                        <a href="{{ route('login') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-transparent hover:bg-white/10 text-white text-sm font-semibold rounded-xl border border-white/30 transition">
                            Masuk ke Akun
                        </a>
                    </div>

                    {{-- Demo accounts hint --}}
                    <div class="mt-8 inline-flex items-center gap-2 px-3 py-2 bg-white/10 rounded-lg border border-white/20">
                        <span class="text-xs text-gray-300">Atau coba demo dengan</span>
                        <code class="text-xs text-white font-mono">raka@eduspace.id</code>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
