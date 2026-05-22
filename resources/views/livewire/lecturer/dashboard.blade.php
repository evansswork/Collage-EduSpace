@php
    $nowWib = now('Asia/Jakarta')->locale('id');
    $greeting = $nowWib->hour < 12 ? 'Selamat pagi' : ($nowWib->hour < 18 ? 'Selamat siang' : 'Selamat malam');
@endphp

<div class="space-y-6" wire:poll.60s>

    {{-- ========== GREETING ========== --}}
    <div>
        <h2 class="text-2xl font-semibold text-gray-900">
            {{ $greeting }},
            {{ auth()->user()->name }}
        </h2>
        <p class="text-sm text-gray-500 mt-1">{{ $nowWib->translatedFormat('l, d F Y') }} · {{ $nowWib->format('H:i') }} WIB · Pantau seluruh kelas dalam satu pandangan.</p>
    </div>

    {{-- ========== STATS CARDS ========== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tugas Aktif</span>
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $this->stats['active_assignments'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Berlangsung minggu ini</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Perlu Dinilai</span>
                <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $this->stats['pending_grading'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Submission belum dinilai</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Murid</span>
                <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $this->stats['total_students'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Lintas semua kelas</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pertanyaan Belum Dijawab</span>
                <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $this->stats['unanswered'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Di forum tugas</p>
        </div>
    </div>

    {{-- ========== COURSE FILTER ========== --}}
    <div class="flex items-center gap-2 flex-wrap">
        <button wire:click="selectCourse(null)"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition
                       {{ !$selectedCourseId ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Semua Kelas
        </button>
        @foreach($this->courses as $course)
            <button wire:click="selectCourse({{ $course->id }})"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition
                           {{ $selectedCourseId === $course->id ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $course->color }}"></span>
                {{ $course->name }}
                <span class="text-[10px] opacity-60">({{ $course->students_count }})</span>
            </button>
        @endforeach
    </div>

    {{-- ========== CLASS MATERIALS ========== --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-1 bg-white border border-gray-200 rounded-2xl p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Materi Kelas</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        @if($this->selectedCourse)
                            Upload materi untuk {{ $this->selectedCourse->code }}.
                        @else
                            Pilih satu kelas dulu untuk upload materi.
                        @endif
                    </p>
                </div>
                @if($this->selectedCourse)
                    <span class="w-2.5 h-2.5 rounded-full mt-1.5" style="background-color: {{ $this->selectedCourse->color }}"></span>
                @endif
            </div>

            @if($this->selectedCourse)
                <div class="mt-4 border-2 border-dashed border-gray-300 rounded-xl p-5 text-center hover:border-gray-400 transition">
                    <input type="file"
                           wire:model="materialUpload"
                           id="class-material-upload-{{ $selectedCourseId }}"
                           class="hidden"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.py,.java,.js,.md,.txt,.json,.sql,.html,.css">

                    <label for="class-material-upload-{{ $selectedCourseId }}" class="cursor-pointer flex flex-col items-center">
                        <div wire:loading.remove wire:target="materialUpload" class="flex flex-col items-center">
                            <div class="w-11 h-11 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">Upload materi ke kelas ini</p>
                            <p class="text-xs text-gray-500 mt-1">PDF, Word, PPT, ZIP, kode ringan</p>
                        </div>

                        <div wire:loading wire:target="materialUpload" class="flex flex-col items-center">
                            <div class="w-11 h-11 border-4 border-gray-200 border-t-gray-900 rounded-full animate-spin mb-3"></div>
                            <p class="text-sm font-semibold text-gray-900">Mengunggah dan membaca file...</p>
                            <p class="text-xs text-gray-500 mt-1">Minggu/topik tetap dikategorikan otomatis</p>
                        </div>
                    </label>
                    @error('materialUpload') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
                </div>
            @else
                <div class="mt-4 rounded-xl bg-gray-50 border border-gray-200 p-4">
                    <p class="text-sm font-medium text-gray-900">Upload aktif saat kelas dipilih</p>
                    <p class="text-xs text-gray-500 mt-1">Klik salah satu chip kelas di atas atau dari sidebar Kelas Saya.</p>
                </div>
            @endif

            @if($lastMaterialAnalysis)
                <div class="mt-4 rounded-xl bg-gray-50 border border-gray-200 p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Baru Diproses</p>
                    <p class="text-sm font-medium text-gray-900 mt-1 truncate">{{ $lastMaterialAnalysis['title'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $lastMaterialAnalysis['week'] ? 'Minggu ' . $lastMaterialAnalysis['week'] : 'Tanpa minggu' }}
                        @if($lastMaterialAnalysis['topic'])
                            · {{ $lastMaterialAnalysis['topic'] }}
                        @endif
                    </p>
                </div>
            @endif
        </div>

        <div class="xl:col-span-2 bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">
                        {{ $this->selectedCourse ? 'Materi ' . $this->selectedCourse->code : 'Materi Terbaru' }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $this->materials->count() }} file {{ $this->selectedCourse ? 'di kelas ini' : 'dari seluruh kelas' }}
                    </p>
                </div>
                <a href="{{ route('lecturer.vault') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium transition">
                    Bank Materi
                </a>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($this->materials as $file)
                    <div class="p-4 hover:bg-gray-50/50 transition flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                            @php $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)); @endphp
                            <span class="text-[10px] font-bold text-gray-600">{{ strtoupper($ext ?: 'FILE') }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $file->title }}</p>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mt-0.5">
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $file->course->color }}"></span>
                                    {{ $file->course->code }}
                                </span>
                                @if($file->week) <span>· Minggu {{ $file->week }}</span> @endif
                                @if($file->topic) <span>· {{ $file->topic }}</span> @endif
                                <span>· {{ $file->human_size }}</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 flex-shrink-0 w-full sm:w-auto">
                            <button type="button"
                                    wire:click="openPreview({{ $file->id }})"
                                    class="flex-1 sm:flex-none text-center px-2.5 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium transition">
                                Preview
                            </button>
                            <a href="{{ route('vault.files.download', $file) }}"
                               class="flex-1 sm:flex-none text-center px-2.5 py-1.5 rounded-md bg-gray-900 hover:bg-gray-800 text-white text-xs font-medium transition">
                                Unduh
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-sm text-gray-500">
                        Belum ada materi di {{ $this->selectedCourse ? 'kelas ini' : 'vault' }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ========== ASSIGNMENTS LIST ========== --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">Daftar Tugas</h3>
            <span class="text-xs text-gray-500">{{ $this->assignments->count() }} tugas</span>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($this->assignments as $assignment)
                @php
                    $s = $this->assignmentStats($assignment);
                    $isOverdue = $assignment->due_at->isPast();
                @endphp

                <div class="p-5 hover:bg-gray-50/50 transition">
                    <div class="flex items-start gap-4">
                        {{-- Course color indicator --}}
                        <div class="w-1 self-stretch rounded-full flex-shrink-0" style="background-color: {{ $assignment->course->color }}"></div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="text-sm font-semibold text-gray-900">{{ $assignment->title }}</h4>
                                        @if($assignment->type === 'group')
                                            <span class="px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded text-[10px] font-medium">KELOMPOK</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $assignment->course->code }} · {{ $assignment->course->name }}
                                    </p>
                                </div>

                                <div class="text-right flex-shrink-0">
                                    <p class="text-xs font-medium {{ $isOverdue ? 'text-gray-500' : 'text-gray-700' }}">
                                        {{ $isOverdue ? 'Berakhir ' : 'Berakhir ' }}{{ $assignment->due_at->diffForHumans() }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        {{ $assignment->due_at->translatedFormat('d M, H:i') }}
                                    </p>
                                </div>
                            </div>

                            {{-- STATS BAR (visual hint of submission status) --}}
                            <div class="mt-3">
                                <div class="flex items-center gap-1.5 mb-1.5">
                                    <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden flex">
                                        @if($s['total'] > 0)
                                            <div class="bg-green-500" style="width: {{ ($s['submitted'] / $s['total']) * 100 }}%" title="Sudah kumpul"></div>
                                            <div class="bg-amber-400" style="width: {{ ($s['late'] / $s['total']) * 100 }}%" title="Telat"></div>
                                            <div class="bg-red-300" style="width: {{ ($s['missing'] / $s['total']) * 100 }}%" title="Belum kumpul"></div>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-semibold text-gray-600 w-10 text-right">{{ $s['pct_submitted'] }}%</span>
                                </div>

                                <div class="flex items-center gap-3 text-[10px]">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-gray-600">{{ $s['submitted'] }} kumpul</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-gray-600">{{ $s['late'] }} telat</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-gray-600">{{ $s['missing'] }} belum</span>
                                    </span>
                                    @if($s['graded'] > 0)
                                        <span class="inline-flex items-center gap-1">
                                            <span class="text-gray-600">{{ $s['graded'] }} dinilai</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- ACTIONS --}}
                            <div class="mt-3 flex items-center gap-2">
                                <a href="{{ route('lecturer.assignments.matrix', $assignment) }}" wire:navigate
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-xs font-medium transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                    Status Matrix
                                </a>
                                @if($s['submitted'] > 0 || $s['late'] > 0)
                                    <a href="{{ route('lecturer.grading', $assignment) }}" wire:navigate
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-900 hover:bg-gray-800 text-white rounded-md text-xs font-medium transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Mulai Penilaian
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-sm text-gray-500">
                    Belum ada tugas di kelas ini
                </div>
            @endforelse
        </div>
    </div>

    @php
        $previewFile = $this->previewFile;
        $preview = $this->previewData;
    @endphp

    @if($previewFile && $preview)
        <x-file-preview-modal
            :preview-file="$previewFile"
            :preview="$preview"
            context-label="Materi Dosen"
            wire-key-prefix="lecturer-dashboard-preview" />
    @endif
</div>
