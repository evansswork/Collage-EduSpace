<div class="space-y-6">

    {{-- BACK NAV --}}
    <div>
        <a href="{{ route('lecturer.dashboard') }}" wire:navigate
           class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Pusat Kelas
        </a>
    </div>

    {{-- HEADER --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2 h-2 rounded-full" style="background-color: {{ $assignment->course->color }}"></div>
                    <span class="text-xs font-medium text-gray-600">{{ $assignment->course->code }} · {{ $assignment->course->name }}</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $assignment->title }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Deadline: {{ $assignment->due_at->translatedFormat('l, d F Y · H:i') }}
                    ({{ $assignment->due_at->diffForHumans() }})
                </p>
            </div>

            <a href="{{ route('lecturer.grading', $assignment) }}" wire:navigate
               class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg text-sm font-medium transition">
                Mode Penilaian
            </a>
        </div>
    </div>

    {{-- ========== FILTER + SEARCH ========== --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Status filters --}}
            <div class="flex items-center gap-1.5 flex-wrap">
                <button wire:click="setFilter('all')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition
                               {{ $filterStatus === 'all' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Semua <span class="opacity-60">({{ $this->statusCounts['all'] }})</span>
                </button>

                <button wire:click="setFilter('submitted')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition
                               {{ $filterStatus === 'submitted' ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                    Sudah Kumpul <span class="opacity-60">({{ $this->statusCounts['submitted'] }})</span>
                </button>

                <button wire:click="setFilter('late')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition
                               {{ $filterStatus === 'late' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    Telat <span class="opacity-60">({{ $this->statusCounts['late'] }})</span>
                </button>

                <button wire:click="setFilter('missing')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition
                               {{ $filterStatus === 'missing' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">
                    Belum Kumpul <span class="opacity-60">({{ $this->statusCounts['missing'] }})</span>
                </button>

                @if($this->statusCounts['graded'] > 0)
                    <button wire:click="setFilter('graded')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition
                                   {{ $filterStatus === 'graded' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                        Dinilai <span class="opacity-60">({{ $this->statusCounts['graded'] }})</span>
                    </button>
                @endif
            </div>

            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari nama / NIM..."
                       class="w-full pl-9 pr-3 py-1.5 bg-gray-50 border border-gray-200 rounded-md text-xs focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 focus:bg-white transition">
            </div>
        </div>
    </div>

    {{-- ========== STATUS MATRIX TABLE ========== --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
            <div class="flex items-center gap-3">
                <h3 class="text-sm font-semibold text-gray-700">Status Matrix</h3>
                <span class="text-xs text-gray-500">{{ count($this->students) }} mahasiswa ditampilkan</span>
            </div>

            {{-- BULK ACTION: SELECT ALL --}}
            @if(in_array($filterStatus, ['missing', 'late']) && count($this->students) > 0)
                <button wire:click="selectAllVisible"
                        class="text-xs text-gray-600 hover:text-gray-900 font-medium">
                    Pilih semua ({{ count($this->students) }})
                </button>
            @endif
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2.5 text-left w-10">
                            @if(in_array($filterStatus, ['missing', 'late']))
                                <input type="checkbox" disabled class="rounded border-gray-300">
                            @endif
                        </th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">NIM</th>
                        <th class="px-4 py-2.5 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Submitted At</th>
                        <th class="px-4 py-2.5 text-right text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($this->students as $row)
                        @php
                            $statusConfig = match($row['status']) {
                                'submitted' => ['bg' => 'bg-green-50', 'rowBg' => '', 'dot' => 'bg-green-500', 'label' => 'Sudah Kumpul', 'text' => 'text-green-700'],
                                'late'      => ['bg' => 'bg-amber-50', 'rowBg' => '', 'dot' => 'bg-amber-500', 'label' => 'Telat', 'text' => 'text-amber-700'],
                                'graded'    => ['bg' => 'bg-blue-50',  'rowBg' => '', 'dot' => 'bg-blue-500',  'label' => 'Dinilai', 'text' => 'text-blue-700'],
                                'missing'   => ['bg' => 'bg-red-50',   'rowBg' => '', 'dot' => 'bg-red-500',   'label' => 'Belum Kumpul', 'text' => 'text-red-700'],
                            };
                            $canSelect = in_array($row['status'], ['missing', 'late']);
                        @endphp

                        <tr class="hover:bg-gray-50/50 transition {{ $statusConfig['rowBg'] }}">
                            <td class="px-4 py-3 w-10">
                                @if($canSelect)
                                    <input type="checkbox" wire:model.live="selectedStudents" value="{{ $row['student']->id }}"
                                           class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                                        <span class="text-[10px] font-medium text-white">{{ $row['student']->initials() }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $row['student']->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600 font-mono">{{ $row['student']->nim_nip }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $row['submitted_at']?->translatedFormat('d M, H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($row['score'] !== null)
                                    <span class="text-sm font-bold text-gray-900">{{ $row['score'] }}</span>
                                    <span class="text-xs text-gray-400">/{{ $assignment->max_score }}</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-sm text-gray-500">
                                Tidak ada mahasiswa di kategori ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========== BULK ACTION STICKY BAR ========== --}}
    @if(count($selectedStudents) > 0)
        <div x-data x-transition.opacity
             class="fixed bottom-6 left-1/2 -translate-x-1/2 z-30 bg-gray-900 text-white rounded-2xl shadow-2xl shadow-gray-900/30 px-5 py-3 flex items-center gap-4 max-w-2xl">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-white/15 flex items-center justify-center">
                    <span class="text-xs font-bold">{{ count($selectedStudents) }}</span>
                </div>
                <span class="text-sm font-medium">mahasiswa dipilih</span>
            </div>

            <div class="h-6 w-px bg-white/20"></div>

            <button wire:click="bulkRemind"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-900 rounded-lg text-sm font-semibold hover:bg-gray-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5"/>
                </svg>
                Kirim Pengingat
            </button>

            <button wire:click="clearSelection"
                    class="text-xs text-white/60 hover:text-white">
                Batal
            </button>
        </div>
    @endif
</div>
