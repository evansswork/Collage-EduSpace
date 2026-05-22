@php
    $nowWib = now('Asia/Jakarta')->locale('id');
    $greeting = $nowWib->hour < 12 ? 'Selamat pagi' : ($nowWib->hour < 18 ? 'Selamat siang' : 'Selamat malam');
@endphp

<div class="space-y-6" wire:poll.60s>

    {{-- ========== ACHIEVEMENT TOAST (kalo baru abis submit) ========== --}}
    @if($achievementMessage)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-amber-900">{{ $achievementMessage }}</p>
                <p class="text-xs text-amber-700 mt-0.5">Kerja bagus! Pertahankan ya.</p>
            </div>
            <button @click="show = false" class="text-amber-600 hover:text-amber-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- ========== GREETING ========== --}}
    <div>
        <h2 class="text-2xl font-semibold text-gray-900">
            {{ $greeting }},
            {{ explode(' ', auth()->user()->name)[0] }}
        </h2>
        <p class="text-sm text-gray-500 mt-1">{{ $nowWib->translatedFormat('l, d F Y') }} · {{ $nowWib->format('H:i') }} WIB</p>
    </div>

    {{-- ========== URGENCY-FIRST BANNER ========== --}}
    @if($this->urgentAssignment)
        @php
            $hoursLeft = now()->diffInHours($this->urgentAssignment->due_at, false);
            $isVeryUrgent = $hoursLeft <= 24;
        @endphp

        <a href="{{ route('assignments.show', $this->urgentAssignment) }}" wire:navigate class="block group">
            <div class="relative overflow-hidden rounded-2xl p-6 md:p-8 transition-all
                        {{ $isVeryUrgent
                            ? 'bg-gradient-to-br from-red-500 to-red-600 text-white shadow-lg shadow-red-500/20'
                            : 'bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200' }}">
                <div class="absolute top-0 right-0 w-64 h-64 opacity-10 transform translate-x-16 -translate-y-16">
                    <svg viewBox="0 0 200 200" fill="currentColor"><circle cx="100" cy="100" r="100"/></svg>
                </div>

                <div class="relative flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                         {{ $isVeryUrgent ? 'bg-white/20 text-white' : 'bg-amber-200 text-amber-900' }}">
                                {{ $isVeryUrgent ? 'URGENT · H-' . max(1, ceil($hoursLeft/24)) : 'Mendekati Deadline' }}
                            </span>
                            <span class="text-xs {{ $isVeryUrgent ? 'text-white/80' : 'text-amber-800' }}">
                                {{ $this->urgentAssignment->course->name }}
                            </span>
                        </div>

                        <h3 class="text-xl md:text-2xl font-bold leading-tight {{ $isVeryUrgent ? 'text-white' : 'text-amber-950' }}">
                            {{ $this->urgentAssignment->title }}
                        </h3>

                        <div class="mt-4 flex items-center gap-4 text-sm {{ $isVeryUrgent ? 'text-white/90' : 'text-amber-800' }}">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $this->urgentAssignment->due_at->diffForHumans() }}
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $this->urgentAssignment->due_at->translatedFormat('d M, H:i') }}
                            </div>
                        </div>
                    </div>

                    <div class="hidden md:flex items-center gap-2 px-4 py-2 rounded-lg
                                {{ $isVeryUrgent ? 'bg-white/15 backdrop-blur' : 'bg-amber-200/60' }}
                                group-hover:translate-x-1 transition">
                        <span class="text-sm font-medium {{ $isVeryUrgent ? 'text-white' : 'text-amber-900' }}">Kerjakan Sekarang</span>
                        <svg class="w-4 h-4 {{ $isVeryUrgent ? 'text-white' : 'text-amber-900' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </div>
                </div>
            </div>
        </a>
    @else
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-9 h-9 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-green-900">Tidak ada tugas mendesak</p>
                <p class="text-xs text-green-700">Santai dulu — kamu sudah on track</p>
            </div>
        </div>
    @endif

    {{-- ========== MAIN GRID ========== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Progress Ring + Group Card --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Progress Ring --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-1">Progress Minggu Ini</h3>
                <p class="text-xs text-gray-500 mb-6">
                    {{ $this->weeklyProgress['done'] }} dari {{ $this->weeklyProgress['total'] }} tugas selesai
                </p>

                <div class="flex justify-center my-4">
                    <x-progress-ring :percentage="$this->weeklyProgress['percentage']" :size="180" :stroke="14" label="Selesai" />
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    @php
                        $progressMessage = match (true) {
                            $this->weeklyProgress['percentage'] >= 100 => 'Luar biasa! Semua tugas selesai',
                            $this->weeklyProgress['percentage'] >= 50 => 'Sudah lebih dari setengah jalan',
                            $this->weeklyProgress['percentage'] > 0 => 'Ayo selesaikan sisanya',
                            default => 'Yuk mulai kerjakan tugasmu',
                        };
                    @endphp
                    <p class="text-xs text-gray-500 text-center">
                        {{ $progressMessage }}
                    </p>
                </div>
            </div>

            {{-- ===== GROUP HUB CARD (kondisional, kalo user punya kelompok) ===== --}}
            @php
                $myGroups = \App\Models\Group::whereHas('members', fn($q) => $q->where('user_id', auth()->id()))
                    ->with(['assignment', 'members.user'])
                    ->get();
            @endphp

            @if($myGroups->count() > 0)
                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Kelompok Saya</h3>

                    <div class="space-y-3">
                        @foreach($myGroups as $g)
                            @php $isLeader = $g->hasLeader(auth()->user()); @endphp
                            <a href="{{ route('groups.show', $g) }}" wire:navigate
                               class="block group p-3 bg-gray-50/50 hover:bg-gray-50 border border-gray-200 rounded-lg transition">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <p class="text-sm font-medium text-gray-900 truncate group-hover:text-black">{{ $g->name }}</p>
                                    @if($isLeader)
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 truncate">{{ $g->assignment->title }}</p>

                                <div class="mt-2 flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-gray-900" style="width: {{ $g->overallProgress() }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-semibold text-gray-600">{{ $g->overallProgress() }}%</span>
                                </div>

                                <div class="mt-2 flex -space-x-1.5">
                                    @foreach($g->members->take(4) as $member)
                                        <div class="w-5 h-5 rounded-full bg-gray-900 border-2 border-white flex items-center justify-center" title="{{ $member->user->name }}">
                                            <span class="text-[8px] font-medium text-white">{{ $member->user->initials() }}</span>
                                        </div>
                                    @endforeach
                                    @if($g->members->count() > 4)
                                        <div class="w-5 h-5 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center">
                                            <span class="text-[8px] font-medium text-gray-600">+{{ $g->members->count() - 4 }}</span>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- RIGHT: Courses + Upcoming --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- COURSES GRID --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">Mata Kuliah</h3>
                    <span class="text-xs text-gray-500">{{ $this->courses->count() }} kelas</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($this->courses as $course)
                        <div class="bg-white border border-gray-200 rounded-xl p-4 hover:border-gray-300 hover:shadow-sm transition cursor-pointer">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                     style="background-color: {{ $course->color }}20;">
                                    <span class="text-base font-bold" style="color: {{ $course->color }}">
                                        {{ strtoupper(substr($course->code, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $course->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $course->code }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $course->assignments_count }} tugas</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- UPCOMING ASSIGNMENTS LIST --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Tugas Mendatang</h3>

                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    @forelse($this->upcomingAssignments as $assignment)
                        @php
                            $urgency = $assignment->urgencyLevel();
                        @endphp

                        <a href="{{ route('assignments.show', $assignment) }}" wire:navigate
                           class="flex items-center gap-4 p-4 hover:bg-gray-50 border-b border-gray-100 last:border-0 transition">
                            <div class="w-1 self-stretch rounded-full" style="background-color: {{ $assignment->course->color }}"></div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $assignment->title }}
                                    @if($assignment->type === 'group')
                                        <span class="ml-1 px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded text-[9px] font-medium align-middle">KELOMPOK</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $assignment->course->name }}</p>
                            </div>

                            <div class="text-right">
                                <p class="text-xs font-medium
                                          {{ $urgency === 'urgent' ? 'text-red-600' : ($urgency === 'soon' ? 'text-amber-600' : 'text-gray-600') }}">
                                    {{ $assignment->due_at->diffForHumans() }}
                                </p>
                                <p class="text-[10px] text-gray-400 mt-0.5">
                                    {{ $assignment->due_at->translatedFormat('d M, H:i') }}
                                </p>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-sm text-gray-500">
                            Tidak ada tugas mendatang
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
