<div>

    {{-- BACK NAV --}}
    <div class="mb-4">
        <a href="{{ route('lecturer.assignments.matrix', $assignment) }}" wire:navigate
           class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Status Matrix
        </a>
    </div>

    {{-- ========== HEADER WITH NAVIGATION ========== --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 mb-4 flex items-center justify-between gap-4 flex-wrap">
        <div class="min-w-0 flex-1">
            <h1 class="text-base font-semibold text-gray-900 truncate">{{ $assignment->title }}</h1>
            <p class="text-xs text-gray-500 mt-0.5">Mode Penilaian · {{ $assignment->course->name }}</p>
        </div>

        @if($this->currentSubmission)
            <div class="flex items-center gap-2">
                <button wire:click="goPrev"
                        @disabled(!$this->position['prevId'])
                        class="w-9 h-9 flex items-center justify-center rounded-md border border-gray-200 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <span class="text-xs text-gray-500 px-2 min-w-[60px] text-center">
                    {{ $this->position['current'] }} / {{ $this->position['total'] }}
                </span>

                <button wire:click="goNext"
                        @disabled(!$this->position['nextId'])
                        class="w-9 h-9 flex items-center justify-center rounded-md border border-gray-200 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        @endif
    </div>

    @if(!$this->currentSubmission)
        {{-- EMPTY STATE --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-gray-100 rounded-full mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900">Belum ada submission</h3>
            <p class="text-sm text-gray-500 mt-1">Tunggu mahasiswa mengumpulkan dulu untuk mulai menilai.</p>
        </div>
    @else

        {{-- ========== AI NON-BLOCKING SUGGESTION ========== --}}
        @if($aiResult && !$aiDismissed && $aiResult['confidence_level'] !== 'normal')
            @php
                $aiLevel = $aiResult['confidence_level'];
                $aiConfig = match($aiLevel) {
                    'high' => [
                        'bg' => 'bg-amber-50',
                        'border' => 'border-amber-300',
                        'icon_bg' => 'bg-amber-100',
                        'icon_color' => 'text-amber-700',
                        'title_color' => 'text-amber-900',
                        'text_color' => 'text-amber-800',
                        'label' => 'Kemiripan Tinggi',
                    ],
                    'suspicious' => [
                        'bg' => 'bg-yellow-50',
                        'border' => 'border-yellow-200',
                        'icon_bg' => 'bg-yellow-100',
                        'icon_color' => 'text-yellow-700',
                        'title_color' => 'text-yellow-900',
                        'text_color' => 'text-yellow-800',
                        'label' => 'Kemiripan Sedang',
                    ],
                };
            @endphp

            <div class="{{ $aiConfig['bg'] }} border {{ $aiConfig['border'] }} rounded-xl p-4 mb-4 flex items-start gap-3">
                <div class="w-9 h-9 {{ $aiConfig['icon_bg'] }} rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 {{ $aiConfig['icon_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <h4 class="text-sm font-semibold {{ $aiConfig['title_color'] }}">
                             Saran AI: {{ $aiConfig['label'] }} Terdeteksi
                        </h4>
                        <span class="px-1.5 py-0.5 bg-white/50 border border-current/20 rounded text-[10px] font-mono {{ $aiConfig['text_color'] }}">
                            {{ $aiResult['similarity_score'] }}% similarity
                        </span>
                    </div>
                    <p class="text-xs {{ $aiConfig['text_color'] }}">
                        Kemiripan dengan sumber eksternal. <strong>Ini hanya saran</strong>, bukan vonis — bisa jadi cuma boilerplate kode standar atau definisi umum.
                    </p>

                    @if(!empty($aiResult['sources']))
                        <details class="mt-2">
                            <summary class="text-xs font-medium {{ $aiConfig['text_color'] }} cursor-pointer hover:underline">
                                Lihat sumber yang terdeteksi ({{ count($aiResult['sources']) }})
                            </summary>
                            <ul class="mt-2 space-y-1 ml-2">
                                @foreach($aiResult['sources'] as $source)
                                    <li class="text-xs {{ $aiConfig['text_color'] }}">
                                        • {{ $source['title'] }}
                                        <span class="font-mono opacity-60">({{ $source['match'] }}% match)</span>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                </div>

                {{-- DISMISS BUTTON --}}
                <button wire:click="dismissAi"
                        class="text-xs font-medium {{ $aiConfig['text_color'] }} hover:underline whitespace-nowrap flex-shrink-0">
                    Abaikan
                </button>
            </div>
        @endif

        {{-- ========== SPLIT-SCREEN GRID ========== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- ===== LEFT: SUBMISSION DOC ===== --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                            <span class="text-[10px] font-medium text-white">{{ $this->currentSubmission->user->initials() }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $this->currentSubmission->user->name }}</p>
                            <p class="text-[10px] text-gray-500 font-mono">{{ $this->currentSubmission->user->nim_nip }}</p>
                        </div>
                    </div>

                    @if($this->currentSubmission->status === 'late')
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px] font-semibold uppercase">TELAT</span>
                    @endif
                </div>

                <div class="p-5 max-h-[700px] overflow-y-auto">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Deskripsi/Catatan</p>
                    <div class="text-sm text-gray-700 whitespace-pre-line bg-gray-50/50 p-3 rounded-lg border border-gray-100">
                        {{ $this->currentSubmission->content ?: '— (tidak ada catatan)' }}
                    </div>

                    @if($this->currentSubmission->file_name)
                        @php $submissionPreview = $this->submissionPreview; @endphp
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">File Tugas</p>
                        <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between gap-3">
                                <div class="min-w-0 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <p class="text-xs font-medium text-gray-700 truncate">{{ $this->currentSubmission->file_name }}</p>
                                </div>
                                @if($submissionPreview && $submissionPreview['download_url'])
                                    <a href="{{ $submissionPreview['download_url'] }}"
                                       class="px-2.5 py-1 bg-white border border-gray-200 rounded-md text-[10px] font-medium text-gray-700 hover:bg-gray-100 transition flex-shrink-0">
                                        Unduh
                                    </a>
                                @endif
                            </div>

                            <div class="p-3 bg-gray-50/50">
                                @if(!$submissionPreview)
                                    <div class="p-6 text-center text-sm text-gray-500">Tidak ada file yang bisa dipreview.</div>
                                @elseif($submissionPreview['type'] === 'missing')
                                    <div class="p-6 text-center">
                                        <p class="text-sm font-medium text-gray-700">File belum ditemukan</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $submissionPreview['message'] }}</p>
                                    </div>
                                @elseif($submissionPreview['type'] === 'pdf')
                                    <iframe src="{{ $submissionPreview['inline_url'] }}"
                                            class="w-full h-[420px] bg-white rounded-lg border border-gray-200"
                                            title="Preview file tugas"></iframe>
                                @elseif($submissionPreview['type'] === 'image')
                                    <div class="bg-white rounded-lg border border-gray-200 p-3 flex justify-center">
                                        <img src="{{ $submissionPreview['inline_url'] }}" alt="File tugas" class="max-h-[420px] object-contain">
                                    </div>
                                @elseif(in_array($submissionPreview['type'], ['code', 'text']))
                                    <div class="bg-gray-950 rounded-lg overflow-hidden border border-gray-800">
                                        <div class="px-3 py-2 border-b border-gray-800 flex items-center justify-between">
                                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">{{ $submissionPreview['language'] }}</span>
                                            <span class="text-[10px] text-gray-500">Preview file asli</span>
                                        </div>
                                        <pre class="p-4 text-xs leading-relaxed text-gray-200 overflow-auto max-h-[420px]"><code>{{ $submissionPreview['content'] }}</code></pre>
                                    </div>
                                @elseif($submissionPreview['type'] === 'document')
                                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                        <div class="px-3 py-2 border-b border-gray-200 flex items-center justify-between">
                                            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">{{ $submissionPreview['label'] }}</p>
                                            <span class="text-[10px] text-gray-400">Teks diekstrak</span>
                                        </div>
                                        @if(trim($submissionPreview['content'] ?? '') !== '')
                                            <pre class="p-4 whitespace-pre-wrap font-sans text-sm leading-6 text-gray-800 overflow-auto max-h-[420px]">{{ $submissionPreview['content'] }}</pre>
                                        @else
                                            <div class="p-6 text-center text-sm text-gray-500">Isi dokumen belum bisa diekstrak.</div>
                                        @endif
                                    </div>
                                @elseif($submissionPreview['type'] === 'zip')
                                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
                                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                            <div class="px-3 py-2 border-b border-gray-200">
                                                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Isi ZIP</p>
                                            </div>
                                            <div class="divide-y divide-gray-100 max-h-[420px] overflow-auto">
                                                @forelse($submissionPreview['entries'] as $entry)
                                                    <div class="flex items-center gap-2 px-3 py-2 text-xs font-mono" style="padding-left: {{ 12 + ($entry['depth'] * 16) }}px">
                                                        @if($entry['is_dir'])
                                                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                                                        @else
                                                            <span class="w-8 text-[10px] font-bold text-gray-500">{{ strtoupper($entry['extension'] ?: 'FILE') }}</span>
                                                        @endif
                                                        <span class="text-gray-800 truncate">{{ $entry['basename'] }}</span>
                                                    </div>
                                                @empty
                                                    <div class="p-6 text-center text-sm text-gray-500">ZIP kosong atau tidak bisa dibaca.</div>
                                                @endforelse
                                            </div>
                                        </div>

                                        <div class="space-y-3">
                                            @forelse($submissionPreview['inline_previews'] as $item)
                                                @if($item['type'] === 'pdf')
                                                    <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
                                                        <div class="px-3 py-2 border-b border-gray-200">
                                                            <span class="text-[10px] font-semibold text-gray-500 truncate">{{ $item['name'] }}</span>
                                                        </div>
                                                        <iframe src="{{ $item['data_uri'] }}" class="w-full h-56 bg-white" title="Preview PDF dalam ZIP"></iframe>
                                                    </div>
                                                @elseif($item['type'] === 'image')
                                                    <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
                                                        <div class="px-3 py-2 border-b border-gray-200">
                                                            <span class="text-[10px] font-semibold text-gray-500 truncate">{{ $item['name'] }}</span>
                                                        </div>
                                                        <div class="p-3 flex justify-center">
                                                            <img src="{{ $item['data_uri'] }}" alt="{{ $item['name'] }}" class="max-h-56 object-contain">
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="bg-gray-950 rounded-lg overflow-hidden border border-gray-800">
                                                        <div class="px-3 py-2 border-b border-gray-800 flex items-center justify-between">
                                                            <span class="text-[10px] font-semibold text-gray-400 truncate">{{ $item['name'] }}</span>
                                                            <span class="text-[10px] text-gray-500">{{ $item['language'] }}</span>
                                                        </div>
                                                        <pre class="p-3 text-xs leading-relaxed text-gray-200 overflow-auto max-h-56"><code>{{ $item['content'] }}</code></pre>
                                                    </div>
                                                @endif
                                            @empty
                                                <div class="bg-white rounded-lg border border-gray-200 p-6 text-center text-sm text-gray-500">
                                                    Tidak ada file ringan yang bisa langsung dipreview di dalam ZIP.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @else
                                    <div class="p-6 text-center">
                                        <p class="text-sm text-gray-600">{{ $submissionPreview['message'] }}</p>
                                        <p class="text-xs text-gray-500 mt-1">Gunakan tombol unduh untuk membuka file ini.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <p class="text-[10px] text-gray-400 mt-4">
                        Dikumpulkan: {{ $this->currentSubmission->submitted_at?->translatedFormat('d M Y, H:i') }}
                    </p>
                </div>
            </div>

            {{-- ===== RIGHT: GRADING PANEL ===== --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden lg:sticky lg:top-20 lg:self-start">
                <div class="px-5 py-4 border-b border-gray-200 bg-gray-50/50">
                    <h3 class="text-sm font-semibold text-gray-900">Penilaian</h3>
                    <p class="text-[10px] text-gray-500 mt-0.5">Nilai maks: {{ $assignment->max_score }} poin</p>
                </div>

                <form wire:submit="saveAndNext" class="p-5 space-y-4">

                    {{-- SCORE INPUT --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">
                            Nilai <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="scoreInput"
                                   min="0" max="{{ $assignment->max_score }}"
                                   placeholder="0"
                                   class="w-24 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-2xl font-bold text-center focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 focus:bg-white transition">
                            <span class="text-sm text-gray-500">/ {{ $assignment->max_score }}</span>

                            {{-- Quick presets --}}
                            <div class="flex items-center gap-1 ml-3">
                                @foreach([60, 70, 80, 90, 100] as $preset)
                                    <button type="button" wire:click="$set('scoreInput', {{ $preset }})"
                                            class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 rounded text-[10px] font-medium text-gray-700 transition">
                                        {{ $preset }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @error('scoreInput') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- FEEDBACK --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">
                            Feedback / Komentar
                        </label>
                        <textarea wire:model="feedbackInput" rows="5"
                                  placeholder="Berikan masukan konstruktif untuk mahasiswa..."
                                  class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 focus:bg-white transition resize-none"></textarea>
                        @error('feedbackInput') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                        {{-- Quick feedback templates --}}
                        <div class="mt-2 flex items-center gap-1.5 flex-wrap">
                            <button type="button"
                                    wire:click="$set('feedbackInput', 'Pekerjaan bagus, semua poin terpenuhi. Pertahankan!')"
                                    class="px-2 py-1 bg-green-50 text-green-700 hover:bg-green-100 rounded text-[10px] font-medium transition">
                                 Bagus
                            </button>
                            <button type="button"
                                    wire:click="$set('feedbackInput', 'Konten sudah benar, tapi perlu lebih rapi dalam penyajian.')"
                                    class="px-2 py-1 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 rounded text-[10px] font-medium transition">
                                 Perlu perbaikan
                            </button>
                            <button type="button"
                                    wire:click="$set('feedbackInput', 'Konsep dasar belum terpenuhi. Silakan diskusi dengan saya di office hour.')"
                                    class="px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded text-[10px] font-medium transition">
                                 Kurang
                            </button>
                        </div>
                    </div>

                    {{-- SUBMIT --}}
                    <div class="pt-2 flex items-center justify-end gap-2">
                        @if($this->position['nextId'])
                            <button type="submit"
                                    class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg text-sm font-medium transition inline-flex items-center gap-2">
                                <span wire:loading.remove wire:target="saveAndNext">Simpan & Lanjut</span>
                                <span wire:loading wire:target="saveAndNext">Menyimpan...</span>
                                <svg wire:loading.remove wire:target="saveAndNext" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        @else
                            <button type="submit"
                                    class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg text-sm font-medium transition">
                                Simpan & Selesai
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- ========== QUEUE OVERVIEW (collapsible) ========== --}}
        <details class="mt-4 bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <summary class="px-5 py-3 cursor-pointer text-sm font-medium text-gray-700 hover:bg-gray-50 transition flex items-center justify-between">
                <span>Antrian Penilaian ({{ $this->queue->count() }} submission)</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </summary>

            <div class="border-t border-gray-200 divide-y divide-gray-100 max-h-80 overflow-y-auto">
                @foreach($this->queue as $idx => $s)
                    <button wire:click="loadSubmission({{ $s->id }})"
                            class="w-full text-left px-5 py-2.5 hover:bg-gray-50 flex items-center gap-3 transition
                                   {{ $s->id === $currentSubmissionId ? 'bg-blue-50' : '' }}">
                        <span class="text-[10px] font-mono text-gray-400 w-6 text-right">{{ $idx + 1 }}.</span>
                        <div class="w-6 h-6 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                            <span class="text-[9px] font-medium text-white">{{ $s->user->initials() }}</span>
                        </div>
                        <span class="text-sm text-gray-900 flex-1 truncate">{{ $s->user->name }}</span>
                        @if($s->score !== null)
                            <span class="text-xs font-semibold text-blue-600">{{ $s->score }}/{{ $assignment->max_score }}</span>
                        @else
                            <span class="text-[10px] text-gray-400">— belum dinilai</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </details>
    @endif
</div>
