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

    {{-- ========== HEADER ========== --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2 h-2 rounded-full" style="background-color: {{ $group->assignment->course->color }}"></div>
                    <span class="text-xs font-medium text-gray-600">{{ $group->assignment->course->code }} · Tugas Kelompok</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $group->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $group->assignment->title }}</p>

                <div class="mt-3 flex items-center gap-3 text-sm text-gray-500">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Deadline {{ $group->assignment->due_at->diffForHumans() }}
                    </span>
                </div>
            </div>

            {{-- ===== OVERALL PROGRESS RING ===== --}}
            <div class="flex flex-col items-center">
                <x-progress-ring :percentage="$group->overallProgress()" :size="100" :stroke="8" label="Tim" />
            </div>
        </div>

        {{-- LEADER BADGE (cuma muncul kalo user adalah leader) --}}
        @if($this->isLeader)
            <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-xs font-medium text-amber-900">Kamu adalah Ketua Kelompok</p>
            </div>
        @endif
    </div>

    {{-- ========== MEMBER PROGRESS PANEL ========== --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-900">Progress Anggota</h3>
            <p class="text-xs text-gray-500 mt-0.5">
                Hitungan otomatis berdasarkan checklist (anti-manipulasi self-report)
            </p>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($this->memberProgress as $mp)
                @php $isMe = $mp['user']->id === auth()->id(); @endphp
                <div class="p-4 md:p-5 hover:bg-gray-50/50 transition">
                    <div class="flex flex-wrap items-center gap-4">
                        {{-- Avatar + Name --}}
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-medium text-white">{{ $mp['user']->initials() }}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center flex-wrap gap-1.5">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $mp['user']->name }}</p>
                                    @if($mp['role'] === 'leader')
                                        <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px] font-medium">KETUA</span>
                                    @endif
                                    @if($isMe)
                                        <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] font-medium">KAMU</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500">{{ $mp['tasks_done'] }} / {{ $mp['tasks_total'] }} sub-task selesai</p>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="flex-1 min-w-[180px] max-w-xs">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-500"
                                         style="width: {{ $mp['progress'] }}%;
                                                background-color: {{ $mp['progress'] >= 100 ? '#10B981' : ($mp['progress'] >= 50 ? '#F59E0B' : ($mp['progress'] > 0 ? '#F97316' : '#EF4444')) }};"></div>
                                </div>
                                <span class="text-xs font-semibold w-9 text-right
                                             {{ $mp['progress'] >= 100 ? 'text-green-600' : ($mp['progress'] >= 50 ? 'text-amber-600' : 'text-gray-700') }}">
                                    {{ $mp['progress'] }}%
                                </span>
                            </div>
                        </div>

                        {{-- ===== LEADER-ONLY ACTIONS ===== --}}
                        @if($this->isLeader && !$isMe)
                            <div class="flex items-center gap-2">
                                {{-- ONE-CLICK GENTLE NUDGE --}}
                                @if($mp['progress'] < 100)
                                    <button wire:click="nudge({{ $mp['user']->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-xs font-medium transition">
                                        <span>Nudge</span>
                                        @if($mp['nudges_this_week'] > 0)
                                            <span class="px-1 bg-amber-200 text-amber-800 rounded text-[10px]">{{ $mp['nudges_this_week'] }}</span>
                                        @endif
                                    </button>
                                @endif

                                {{-- ESCALATE TO LECTURER (kondisional: muncul setelah 3 nudge gagal) --}}
                                @if($mp['can_escalate'] && !$mp['already_escalated'])
                                    <button wire:click="escalateToLecturer({{ $mp['user']->id }})"
                                            wire:confirm="Yakin mau eskalasi ke pengajar? Ini akan mengirim laporan resmi otomatis."
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-md text-xs font-medium transition">
                                        <span>Eskalasi ke Pengajar</span>
                                    </button>
                                @elseif($mp['already_escalated'])
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-700 rounded-md text-xs font-medium">
                                        Sudah dieskalasi
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Hint message untuk leader --}}
                    @if($this->isLeader && !$isMe && $mp['nudges_this_week'] >= 2 && $mp['progress'] === 0)
                        <p class="text-xs text-amber-700 mt-2 ml-13">
                            Sudah {{ $mp['nudges_this_week'] }}x nudge tanpa progres. Pertimbangkan eskalasi ke pengajar.
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- ========== TABS NAVIGATION ========== --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="flex border-b border-gray-200 px-2 pt-2 overflow-x-auto">
            <button wire:click="setTab('tasks')"
                    class="px-4 py-3 text-sm font-medium transition relative flex-shrink-0
                           {{ $activeTab === 'tasks' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                <span class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Sub-Tasks
                </span>
                @if($activeTab === 'tasks')
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-900"></div>
                @endif
            </button>

            <button wire:click="setTab('sandbox')"
                    class="px-4 py-3 text-sm font-medium transition relative flex-shrink-0
                           {{ $activeTab === 'sandbox' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                <span class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    Shared Sandbox
                    <span class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px] font-semibold text-gray-600">{{ $this->sandboxFiles->count() }}</span>
                </span>
                @if($activeTab === 'sandbox')
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-900"></div>
                @endif
            </button>

            <button wire:click="setTab('thread')"
                    class="px-4 py-3 text-sm font-medium transition relative flex-shrink-0
                           {{ $activeTab === 'thread' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                <span class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    Group Thread
                </span>
                @if($activeTab === 'thread')
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-900"></div>
                @endif
            </button>
        </div>

        {{-- =================== TAB CONTENT =================== --}}
        <div class="p-6">

            {{-- ========== TAB 1: TASKS ========== --}}
            @if($activeTab === 'tasks')
                <div class="space-y-4">
                    <p class="text-xs text-gray-500">
                        Tandai sub-task selesai dengan upload bukti file. Progress dihitung otomatis berdasarkan checklist — tidak bisa dimanipulasi.
                    </p>

                    @php
                        $tasksByMember = $this->tasks->groupBy('assigned_to');
                    @endphp

                    @foreach($this->members as $member)
                        @php $memberTasks = $tasksByMember->get($member->user_id, collect()); @endphp
                        @if($memberTasks->count() > 0)
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-gray-900 flex items-center justify-center">
                                            <span class="text-[10px] font-medium text-white">{{ $member->user->initials() }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $member->user->name }}</span>
                                        @if($member->role === 'leader')
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-500">
                                        {{ $memberTasks->where('is_completed', true)->count() }} / {{ $memberTasks->count() }} selesai
                                    </span>
                                </div>

                                <div class="divide-y divide-gray-100">
                                    @foreach($memberTasks as $task)
                                        @php $isMyTask = $task->assigned_to === auth()->id(); @endphp
                                        <div class="px-4 py-3 flex items-center gap-3 hover:bg-gray-50/50 transition">
                                            <div class="w-5 h-5 rounded flex items-center justify-center flex-shrink-0
                                                        {{ $task->is_completed ? 'bg-green-500' : 'border-2 border-gray-300' }}">
                                                @if($task->is_completed)
                                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                @endif
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm {{ $task->is_completed ? 'text-gray-500 line-through' : 'text-gray-900' }}">
                                                    {{ $task->title }}
                                                </p>
                                                @if($task->is_completed && $task->completed_at)
                                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                                        Selesai {{ $task->completed_at->diffForHumans() }}
                                                        @if($task->proof_file) · Ada bukti @endif
                                                    </p>
                                                @endif
                                            </div>

                                            @if($isMyTask && !$task->is_completed)
                                                <button wire:click="startCompletingTask({{ $task->id }})"
                                                        class="px-2.5 py-1 bg-gray-900 hover:bg-gray-800 text-white rounded-md text-xs font-medium transition flex-shrink-0">
                                                    Selesaikan
                                                </button>
                                            @elseif($isMyTask && $task->is_completed)
                                                <button wire:click="uncompleteTask({{ $task->id }})"
                                                        class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-xs font-medium transition flex-shrink-0">
                                                    Buka lagi
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- COMPLETE TASK MODAL --}}
                @if($completingTaskId)
                    @php $taskBeingCompleted = $this->tasks->firstWhere('id', $completingTaskId); @endphp
                    @if($taskBeingCompleted)
                        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                            <div @click.outside="$wire.cancelCompleteTask()" class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h3 class="text-base font-semibold text-gray-900">Tandai Selesai</h3>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $taskBeingCompleted->title }}</p>
                                </div>

                                <form wire:submit="completeTask" class="p-6">
                                    <p class="text-xs text-gray-600 mb-3">
                                        Upload bukti hasil pekerjaan (screenshot, file, dll) — ini wajib untuk mencegah self-report yang dimanipulasi.
                                    </p>

                                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 hover:border-gray-400 transition">
                                        <input type="file" wire:model="taskProof" id="task-proof" class="hidden" accept=".pdf,.doc,.docx,.ppt,.pptx,.xlsx,.zip,.py,.java,.js,.md,.txt,.json,.sql,.html,.css">
                                        <label for="task-proof" class="cursor-pointer flex flex-col items-center text-center">
                                            <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                            <p class="text-sm text-gray-700 font-medium">
                                                @if($taskProof)
                                                    {{ $taskProof->getClientOriginalName() }}
                                                @else
                                                    Klik untuk pilih file bukti
                                                @endif
                                            </p>
                                        </label>
                                    </div>
                                    @error('taskProof') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

                                    <div class="mt-5 flex justify-end gap-2">
                                        <button type="button" wire:click="cancelCompleteTask"
                                                class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md text-xs font-medium hover:bg-gray-200 transition">
                                            Batal
                                        </button>
                                        <button type="submit"
                                                class="px-3 py-1.5 bg-gray-900 text-white rounded-md text-xs font-medium hover:bg-gray-800 transition">
                                            Konfirmasi Selesai
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @endif
            @endif

            {{-- ========== TAB 2: SANDBOX ========== --}}
            @if($activeTab === 'sandbox')
                <div class="space-y-4">
                    <p class="text-xs text-gray-500">
                        Tempat upload file kelompok terpusat. Semua anggota bisa lihat & download. Tidak perlu Google Drive terpisah.
                    </p>

                    {{-- UPLOAD FORM --}}
                    <form wire:submit="uploadFile" class="border-2 border-dashed border-gray-200 rounded-xl p-4 hover:border-gray-400 transition">
                        <div class="flex flex-col items-center text-center">
                            <input type="file" wire:model="newFile" id="sandbox-upload" class="hidden" accept=".pdf,.doc,.docx,.ppt,.pptx,.xlsx,.zip,.py,.java,.js,.md,.txt,.json,.sql,.html,.css">
                            <label for="sandbox-upload" class="cursor-pointer w-full">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-700">
                                    @if($newFile)
                                        {{ $newFile->getClientOriginalName() }}
                                    @else
                                        Klik untuk upload file ke Sandbox
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">Maks 50 MB · semua format</p>
                            </label>
                            @error('newFile') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

                            @if($newFile)
                                <div class="mt-3 w-full max-w-sm">
                                    <input type="text" wire:model="fileNote"
                                           placeholder="Catatan opsional (mis: 'API endpoint untuk login')"
                                           class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition">
                                    <button type="submit"
                                            class="mt-2 w-full px-3 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg text-xs font-medium transition">
                                        <span wire:loading.remove wire:target="uploadFile">Upload ke Sandbox</span>
                                        <span wire:loading wire:target="uploadFile">Uploading...</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </form>

                    {{-- FILE LIST --}}
                    @if($this->sandboxFiles->count() > 0)
                        <div class="space-y-2">
                            @foreach($this->sandboxFiles as $file)
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-3 bg-gray-50/50 hover:bg-gray-50 border border-gray-200 rounded-lg transition group">
                                    <div class="flex items-start gap-3 min-w-0 flex-1">
                                        <div class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center flex-shrink-0">
                                            @php $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)); @endphp
                                            @if(in_array($ext, ['pdf']))
                                                <span class="text-[10px] font-bold text-red-600">PDF</span>
                                            @elseif(in_array($ext, ['doc', 'docx']))
                                                <span class="text-[10px] font-bold text-blue-600">DOC</span>
                                            @elseif(in_array($ext, ['zip', 'rar']))
                                                <span class="text-[10px] font-bold text-amber-600">ZIP</span>
                                            @elseif(in_array($ext, ['py', 'js', 'java', 'php']))
                                                <span class="text-[10px] font-bold text-purple-600">{{ strtoupper($ext) }}</span>
                                            @elseif(in_array($ext, ['png', 'jpg', 'jpeg', 'gif']))
                                                <span class="text-[10px] font-bold text-green-600">IMG</span>
                                            @else
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            @endif
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $file->file_name }}</p>
                                            @if($file->note)
                                                <p class="text-xs text-gray-600 truncate">{{ $file->note }}</p>
                                            @endif
                                            <p class="text-[10px] text-gray-500 mt-0.5">
                                                oleh {{ $file->uploader->name }} · {{ $file->human_size }} · {{ $file->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end gap-1.5 flex-shrink-0 w-full sm:w-auto">
                                        <button type="button"
                                                wire:click="openPreview({{ $file->id }})"
                                                class="px-2.5 py-1.5 rounded-md bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 text-xs font-medium transition">
                                            Preview
                                        </button>
                                        <a href="{{ route('group.files.download', $file) }}"
                                           class="px-2.5 py-1.5 rounded-md bg-gray-900 hover:bg-gray-800 text-white text-xs font-medium transition">
                                            Unduh
                                        </a>
                                    </div>

                                    @if($file->uploaded_by === auth()->id() || $this->isLeader)
                                        <button wire:click="deleteFile({{ $file->id }})"
                                                wire:confirm="Hapus file ini?"
                                                class="opacity-100 sm:opacity-0 sm:group-hover:opacity-100 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition self-end sm:self-auto">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-sm text-gray-500">
                            Belum ada file di Sandbox
                        </div>
                    @endif
                </div>
            @endif

            {{-- ========== TAB 3: GROUP THREAD ========== --}}
            @if($activeTab === 'thread')
                <div class="space-y-4">

                    {{-- PINNED DECISIONS (kalo ada) --}}
                    @if($this->decisions->count() > 0)
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <h4 class="text-xs font-semibold text-amber-900 uppercase tracking-wider">Keputusan Tim</h4>
                            </div>
                            <div class="space-y-2">
                                @foreach($this->decisions as $decision)
                                    <div class="bg-white border border-amber-200 rounded-lg p-3">
                                        <p class="text-sm text-gray-800">{{ $decision->body }}</p>
                                        <p class="text-[10px] text-amber-700 mt-1.5">
                                            oleh {{ $decision->user->name }} · {{ $decision->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- MESSAGE LIST --}}
                    <div class="space-y-3 max-h-[500px] overflow-y-auto">
                        @forelse($this->threadMessages as $msg)
                            @php $isMine = $msg->user_id === auth()->id(); @endphp
                            <div class="flex gap-3 group {{ $isMine ? 'flex-row-reverse' : '' }}">
                                <div class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                                    <span class="text-[10px] font-medium text-white">{{ $msg->user->initials() }}</span>
                                </div>
                                <div class="max-w-[75%] {{ $isMine ? 'items-end' : '' }} flex flex-col">
                                    <div class="flex items-center gap-1.5 mb-0.5 {{ $isMine ? 'flex-row-reverse' : '' }}">
                                        <span class="text-xs font-medium text-gray-700">
                                            {{ $isMine ? 'Kamu' : $msg->user->name }}
                                        </span>
                                        @if($this->members->firstWhere('user_id', $msg->user_id)?->role === 'leader')
                                        @endif
                                        <span class="text-[10px] text-gray-400">{{ $msg->created_at->format('H:i') }}</span>
                                    </div>
                                    <div class="rounded-2xl px-3.5 py-2 inline-block
                                                {{ $isMine ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-900' }}
                                                {{ $msg->is_decision ? '!bg-amber-100 !text-amber-900 border border-amber-200' : '' }}">
                                        @if($msg->is_decision)
                                            <div class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider mb-1 opacity-70">
                                                Keputusan Tim
                                            </div>
                                        @endif
                                        <p class="text-sm whitespace-pre-line">{{ $msg->body }}</p>
                                    </div>

                                    {{-- LEADER-ONLY: pin as decision --}}
                                    @if($this->isLeader)
                                        <button wire:click="pinAsDecision({{ $msg->id }})"
                                                class="opacity-0 group-hover:opacity-100 mt-1 text-[10px] text-gray-500 hover:text-amber-600 transition">
                                            {{ $msg->is_decision ? 'Lepas pin' : 'Tandai sebagai keputusan' }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-sm text-gray-500">Belum ada percakapan</p>
                                <p class="text-xs text-gray-400 mt-1">Mulai dengan koordinasi pembagian tugas</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- COMPOSE --}}
                    <form wire:submit="sendMessage" class="border-t border-gray-200 pt-4">
                        <div class="flex gap-2">
                            <input type="text" wire:model="newMessage"
                                   placeholder="Tulis pesan untuk kelompok..."
                                   class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 focus:bg-white transition">
                            <button type="submit"
                                    class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg text-sm font-medium transition">
                                Kirim
                            </button>
                        </div>
                        @error('newMessage') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                        @if($this->isLeader)
                            <label class="inline-flex items-center gap-1.5 mt-2 cursor-pointer">
                                <input type="checkbox" wire:model="markAsDecision"
                                       class="w-3.5 h-3.5 rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                                <span class="text-xs text-gray-600">Tandai sebagai Keputusan Tim (anti-tenggelam)</span>
                            </label>
                        @endif
                    </form>
                </div>
            @endif
        </div>
    </div>

    @php
        $previewFile = $this->previewFile;
        $preview = $this->previewData;
    @endphp

    @if($previewFile && $preview)
        <div x-data="{
                previousOverflow: '',
                init() {
                    this.previousOverflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                },
                destroy() {
                    document.body.style.overflow = this.previousOverflow || '';
                }
             }"
             class="fixed inset-0 z-40 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:key="sandbox-preview-{{ $previewFile->id }}">

            <div @click.outside="$wire.closePreview()"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden min-h-0">

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-semibold text-gray-900 truncate">{{ $previewFile->file_name }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Shared Sandbox · {{ $previewFile->human_size }}
                            @if($previewFile->note) · {{ $previewFile->note }} @endif
                        </p>
                    </div>
                    <button wire:click="closePreview"
                            class="w-9 h-9 flex items-center justify-center rounded-md hover:bg-gray-100 transition">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain bg-gray-50 p-6">
                    @if($preview['type'] === 'missing')
                        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
                            <p class="text-sm font-medium text-gray-700">File belum ditemukan</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $preview['message'] }}</p>
                        </div>
                    @elseif($preview['type'] === 'pdf')
                        <iframe src="{{ $preview['inline_url'] }}"
                                class="w-full h-[65vh] bg-white rounded-lg border border-gray-200"
                                title="Preview file sandbox"></iframe>
                    @elseif($preview['type'] === 'image')
                        <div class="bg-white rounded-lg border border-gray-200 p-4 flex justify-center">
                            <img src="{{ $preview['inline_url'] }}" alt="{{ $previewFile->file_name }}" class="max-h-[65vh] object-contain">
                        </div>
                    @elseif(in_array($preview['type'], ['code', 'text']))
                        <div class="bg-gray-950 rounded-lg overflow-hidden border border-gray-800">
                            <div class="px-4 py-2 border-b border-gray-800 flex items-center justify-between">
                                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">{{ $preview['language'] }}</span>
                                <span class="text-[10px] text-gray-500">Preview file asli</span>
                            </div>
                            <pre class="p-5 text-xs leading-relaxed text-gray-200 overflow-auto max-h-[65vh]"><code>{{ $preview['content'] }}</code></pre>
                        </div>
                    @elseif($preview['type'] === 'document')
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $preview['label'] }}</p>
                                <span class="text-[10px] text-gray-400">Teks diekstrak dari file</span>
                            </div>
                            @if(trim($preview['content'] ?? '') !== '')
                                <pre class="p-6 whitespace-pre-wrap font-sans text-sm leading-6 text-gray-800 overflow-auto max-h-[65vh]">{{ $preview['content'] }}</pre>
                            @else
                                <div class="p-8 text-center text-sm text-gray-500">Isi dokumen belum bisa diekstrak.</div>
                            @endif
                        </div>
                    @elseif($preview['type'] === 'zip')
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">File yang akan diunduh</p>
                                </div>
                                <div class="divide-y divide-gray-100 max-h-[65vh] overflow-auto">
                                    @forelse($preview['entries'] as $entry)
                                        <div class="flex items-center gap-2 px-4 py-2 text-sm font-mono" style="padding-left: {{ 16 + ($entry['depth'] * 18) }}px">
                                            @if($entry['is_dir'])
                                                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                                            @else
                                                <span class="w-8 text-[10px] font-bold text-gray-500">{{ strtoupper($entry['extension'] ?: 'FILE') }}</span>
                                            @endif
                                            <span class="text-gray-800 truncate">{{ $entry['basename'] }}</span>
                                            @if(!$entry['is_dir'])
                                                <span class="ml-auto text-[10px] text-gray-400">{{ $entry['human_size'] }}</span>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="p-6 text-center text-sm text-gray-500">ZIP kosong atau tidak bisa dibaca</div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="space-y-3">
                                @forelse($preview['inline_previews'] as $item)
                                    @if($item['type'] === 'pdf')
                                        <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
                                            <div class="px-4 py-2 border-b border-gray-200">
                                                <span class="text-[10px] font-semibold text-gray-500 truncate">{{ $item['name'] }}</span>
                                            </div>
                                            <iframe src="{{ $item['data_uri'] }}" class="w-full h-72 bg-white" title="Preview PDF dalam ZIP"></iframe>
                                        </div>
                                    @elseif($item['type'] === 'image')
                                        <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
                                            <div class="px-4 py-2 border-b border-gray-200">
                                                <span class="text-[10px] font-semibold text-gray-500 truncate">{{ $item['name'] }}</span>
                                            </div>
                                            <div class="p-3 flex justify-center">
                                                <img src="{{ $item['data_uri'] }}" alt="{{ $item['name'] }}" class="max-h-72 object-contain">
                                            </div>
                                        </div>
                                    @else
                                        <div class="bg-gray-950 rounded-lg overflow-hidden border border-gray-800">
                                            <div class="px-4 py-2 border-b border-gray-800 flex items-center justify-between">
                                                <span class="text-[10px] font-semibold text-gray-400 truncate">{{ $item['name'] }}</span>
                                                <span class="text-[10px] text-gray-500">{{ $item['language'] }}</span>
                                            </div>
                                            <pre class="p-4 text-xs leading-relaxed text-gray-200 overflow-auto max-h-72"><code>{{ $item['content'] }}</code></pre>
                                        </div>
                                    @endif
                                @empty
                                    <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
                                        <p class="text-sm text-gray-600">Tidak ada file ringan yang bisa langsung dipreview di dalam ZIP.</p>
                                        <p class="text-xs text-gray-500 mt-1">Daftar isi ZIP tetap tampil di sebelah kiri.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
                            <p class="text-sm text-gray-600">{{ $preview['message'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">Gunakan tombol unduh untuk membuka file ini.</p>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 flex flex-wrap justify-between items-center gap-3">
                    <p class="text-xs text-gray-500">
                        Diunggah oleh <span class="text-gray-700 font-medium">{{ $previewFile->uploader->name }}</span>
                        · {{ $previewFile->created_at->diffForHumans() }}
                    </p>
                    <a href="{{ $preview['download_url'] }}"
                       class="px-3 py-1.5 bg-white border border-gray-200 rounded-md text-xs font-medium text-gray-700 hover:bg-gray-100 transition">
                        Unduh File
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
