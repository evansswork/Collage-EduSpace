<div class="space-y-6">

    {{-- BACK NAV --}}
    <div>
        <a href="{{ route('dashboard') }}" wire:navigate
           class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    {{-- ASSIGNMENT HEADER --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-start justify-between gap-4 mb-3">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full" style="background-color: {{ $assignment->course->color }}"></div>
                <span class="text-xs font-medium text-gray-600">{{ $assignment->course->code }} · {{ $assignment->course->name }}</span>
            </div>

            @php $urgency = $assignment->urgencyLevel(); @endphp
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                {{ $urgency === 'urgent'  ? 'bg-red-100 text-red-700' : '' }}
                {{ $urgency === 'soon'    ? 'bg-amber-100 text-amber-700' : '' }}
                {{ $urgency === 'normal'  ? 'bg-gray-100 text-gray-700' : '' }}
                {{ $urgency === 'overdue' ? 'bg-gray-200 text-gray-600' : '' }}">
                @switch($urgency)
                    @case('urgent')  Mendesak — {{ $assignment->due_at->diffForHumans() }} @break
                    @case('soon')    Segera — {{ $assignment->due_at->diffForHumans() }} @break
                    @case('overdue') Lewat deadline @break
                    @default         {{ $assignment->due_at->diffForHumans() }}
                @endswitch
            </span>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $assignment->title }}</h1>

        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Deadline: {{ $assignment->due_at->translatedFormat('l, d F Y · H:i') }}
            </div>
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Nilai maks: {{ $assignment->max_score }}
            </div>
        </div>

        {{-- INSTRUCTIONS --}}
        <div class="mt-6 pt-6 border-t border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Instruksi</h3>
            <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-line">{{ $assignment->instructions }}</div>
        </div>
    </div>

    {{-- ========== SUBMISSION AREA ========== --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        @if($pendingSubmit)
            {{-- ===== UNDO GRACE PERIOD (10 detik) ===== --}}
            <div x-data="{
                    countdown: 10,
                    timer: null,
                    init() {
                        this.timer = setInterval(() => {
                            this.countdown--;
                            if (this.countdown <= 0) {
                                clearInterval(this.timer);
                                $wire.finalizeSubmission();
                            }
                        }, 1000);
                    },
                    destroy() {
                        if (this.timer) clearInterval(this.timer);
                    }
                 }"
                 x-init="init()"
                 @undo-clicked.window="destroy()"
                 class="text-center py-4">

                <div class="inline-flex items-center justify-center w-14 h-14 bg-green-100 rounded-full mb-4">
                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold text-gray-900 mb-1">Tugas berhasil dikumpulkan!</h3>
                <p class="text-sm text-gray-500 mb-5">Berubah pikiran? Kamu masih bisa membatalkan dalam:</p>

                {{-- Countdown bar --}}
                <div class="max-w-sm mx-auto mb-4">
                    <div class="relative h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="absolute inset-y-0 left-0 bg-gray-900 transition-all duration-1000 ease-linear"
                             :style="`width: ${(countdown / 10) * 100}%`"></div>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 mt-3" x-text="countdown + ' detik'"></p>
                </div>

                <button @click="$dispatch('undo-clicked'); $wire.undoSubmit()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                    Batalkan (Undo)
                </button>
            </div>

        @elseif($this->submission && in_array($this->submission->status, ['submitted', 'graded', 'late']))
            {{-- ===== SUDAH SUBMIT ===== --}}
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-gray-900">Tugas Sudah Dikumpulkan</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $this->submission->submitted_at->translatedFormat('l, d F Y · H:i') }}
                        @if($this->submission->status === 'late')
                            <span class="ml-2 px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px] font-medium">TELAT</span>
                        @endif
                    </p>

                    @if($this->submission->file_name)
                        <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            {{ $this->submission->file_name }}
                        </div>
                    @endif

                    @if($this->submission->status === 'graded')
                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs text-blue-700 font-semibold uppercase tracking-wider mb-1">Nilai</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $this->submission->score }} / {{ $assignment->max_score }}</p>
                            @if($this->submission->feedback)
                                <p class="text-sm text-blue-800 mt-2">{{ $this->submission->feedback }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        @else
            {{-- ===== BELUM SUBMIT: FORM ===== --}}
            <h3 class="text-base font-semibold text-gray-900 mb-1">Pengumpulan Tugas</h3>
            <p class="text-xs text-gray-500 mb-5">
                @if($this->assignment->due_at->isPast())
                     Deadline sudah lewat — pengumpulan akan dicatat sebagai TELAT.
                @else
                    Pastikan kamu sudah review semua jawaban sebelum submit.
                @endif
            </p>

            <form wire:submit="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">
                        Deskripsi / Catatan <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="submissionContent" rows="4"
                              placeholder="Tuliskan ringkasan jawaban atau catatan untuk dosen..."
                              class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 focus:bg-white transition resize-none"></textarea>
                    @error('submissionContent') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">
                        File Tugas
                    </label>
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 hover:border-gray-400 transition">
                        <input type="file" wire:model="submissionFile" id="file-upload"
                               class="hidden" accept=".pdf,.doc,.docx,.zip,.py,.java,.txt">
                        <label for="file-upload" class="cursor-pointer flex flex-col items-center text-center">
                            <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm text-gray-700 font-medium">
                                @if($submissionFile)
                                    {{ $submissionFile->getClientOriginalName() }}
                                @else
                                    Klik untuk pilih file
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">PDF, DOC, ZIP, atau kode (maks 10 MB)</p>
                        </label>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="submit"
                            class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition inline-flex items-center gap-2">
                        <span wire:loading.remove wire:target="submit">Submit Tugas</span>
                        <span wire:loading wire:target="submit">Mengirim...</span>
                        <svg wire:loading.remove wire:target="submit" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>
        @endif
    </div>

    {{-- ========== CONTEXTUAL MICRO-FORUM ========== --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Diskusi Tugas</h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Tanya & diskusi langsung di sini. Diurutkan otomatis berdasarkan upvote.
                </p>
            </div>
            <span class="text-xs text-gray-500">{{ $this->forumPosts->count() }} pertanyaan</span>
        </div>

        {{-- New Post Form --}}
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <form wire:submit="postQuestion" class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-medium text-white">{{ auth()->user()->initials() }}</span>
                </div>
                <div class="flex-1 space-y-2">
                    <textarea wire:model="newPostBody" rows="2"
                              placeholder="Punya pertanyaan tentang tugas ini?"
                              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition resize-none"></textarea>
                    @error('newPostBody') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-3 py-1.5 bg-gray-900 text-white rounded-md text-xs font-medium hover:bg-gray-800 transition">
                            Kirim Pertanyaan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Post List --}}
        <div class="divide-y divide-gray-100">
            @forelse($this->forumPosts as $post)
                <div class="p-6">
                    <div class="flex gap-3">
                        {{-- VOTE COLUMN --}}
                        <div class="flex flex-col items-center flex-shrink-0">
                            <button wire:click="toggleVote({{ $post->id }})"
                                    class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-gray-100 transition
                                           {{ $post->votedBy(auth()->user()) ? 'text-amber-500' : 'text-gray-400 hover:text-gray-600' }}">
                                <svg class="w-5 h-5" fill="{{ $post->votedBy(auth()->user()) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                </svg>
                            </button>
                            <span class="text-xs font-semibold {{ $post->votedBy(auth()->user()) ? 'text-amber-600' : 'text-gray-500' }}">
                                {{ $post->votes_count }}
                            </span>
                        </div>

                        {{-- POST BODY --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center flex-wrap gap-2 mb-1">
                                <span class="text-sm font-medium text-gray-900">{{ $post->user->name }}</span>
                                @if($post->user->isLecturer())
                                    <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] font-medium">DOSEN</span>
                                @endif
                                @if($post->is_pinned)
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px] font-medium">
                                         PINNED
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">·</span>
                                <span class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</span>
                            </div>

                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $post->body }}</p>

                            {{-- ACTIONS --}}
                            <div class="mt-2 flex items-center gap-3 text-xs">
                                <button wire:click="startReply({{ $post->id }})"
                                        class="text-gray-500 hover:text-gray-900 font-medium">
                                    Balas
                                </button>
                                @if($post->replies->count() > 0)
                                    <span class="text-gray-400">·</span>
                                    <span class="text-gray-500">{{ $post->replies->count() }} balasan</span>
                                @endif
                            </div>

                            {{-- REPLY FORM --}}
                            @if($replyingTo === $post->id)
                                <form wire:submit="postReply({{ $post->id }})" class="mt-3 flex gap-2">
                                    <textarea wire:model="replyBody" rows="2"
                                              placeholder="Tulis balasanmu..."
                                              class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition resize-none"></textarea>
                                    <div class="flex flex-col gap-1">
                                        <button type="submit"
                                                class="px-3 py-1.5 bg-gray-900 text-white rounded-md text-xs font-medium hover:bg-gray-800 transition">
                                            Kirim
                                        </button>
                                        <button type="button" wire:click="cancelReply"
                                                class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md text-xs font-medium hover:bg-gray-200 transition">
                                            Batal
                                        </button>
                                    </div>
                                </form>
                                @error('replyBody') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            @endif

                            {{-- REPLIES (nested) --}}
                            @if($post->replies->count() > 0)
                                <div class="mt-4 space-y-3 pl-4 border-l-2 border-gray-100">
                                    @foreach($post->replies as $reply)
                                        <div class="flex gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                                                <span class="text-[10px] font-medium text-white">{{ $reply->user->initials() }}</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center flex-wrap gap-1.5">
                                                    <span class="text-xs font-medium text-gray-900">{{ $reply->user->name }}</span>
                                                    @if($reply->user->isLecturer())
                                                        <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded text-[9px] font-medium">DOSEN</span>
                                                    @endif
                                                    <span class="text-[10px] text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-sm text-gray-700 mt-0.5 whitespace-pre-line">{{ $reply->body }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <p class="text-sm text-gray-600 font-medium">Belum ada pertanyaan</p>
                    <p class="text-xs text-gray-500 mt-1">Jadilah yang pertama bertanya — orang lain mungkin punya kebingungan yang sama!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
