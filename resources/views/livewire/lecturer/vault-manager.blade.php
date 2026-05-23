<div class="space-y-6">

    {{-- HEADER --}}
    <div>
        <h2 class="text-2xl font-semibold text-gray-900">Bank Materi</h2>
        <p class="text-sm text-gray-500 mt-1">
            Unggah materi sekali, sistem langsung membaca dan menaruhnya ke folder mata kuliah serta minggu/topik.
        </p>
    </div>

    {{-- ========== STATS ========== --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total File</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $this->vaultStats['total'] }}</p>
            <p class="text-xs text-gray-500">Di seluruh kelas</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Auto-Kategori</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                {{ $this->vaultStats['total'] > 0 ? round(($this->vaultStats['ai_correct'] / $this->vaultStats['total']) * 100) : 0 }}%
            </p>
            <p class="text-xs text-gray-500">{{ $this->vaultStats['ai_correct'] }} file diproses otomatis</p>
        </div>
    </div>

    {{-- ========== UPLOAD AREA ========== --}}
    <div class="bg-white border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center hover:border-gray-400 transition">
        <input type="file" wire:model="uploadedFile" id="vault-upload"
               class="hidden" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.py,.java,.js,.md,.txt,.json,.sql,.html,.css">

        <label for="vault-upload" class="cursor-pointer flex flex-col items-center">
            <div wire:loading.remove wire:target="uploadedFile" class="flex flex-col items-center">
                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900">Klik untuk unggah materi</p>
                <p class="text-xs text-gray-500 mt-1">PDF, Word, PPT, ZIP, kode ringan (maks 50 MB)</p>
                <p class="text-[10px] text-gray-400 mt-3 max-w-md">
                    Sistem membaca nama dan isi file, lalu langsung menyimpan ke folder visual otomatis.
                </p>
            </div>

            <div wire:loading wire:target="uploadedFile" class="flex flex-col items-center">
                <div class="w-14 h-14 border-4 border-gray-200 border-t-gray-900 rounded-full animate-spin mb-3"></div>
                <p class="text-sm font-semibold text-gray-900">Mengunggah dan menganalisis...</p>
                <p class="text-xs text-gray-500 mt-1">Sedang mencari mata kuliah, minggu, dan topik</p>
            </div>
        </label>
        @error('uploadedFile') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
    </div>

    @if($pendingAnalysis)
        <div class="bg-white border border-amber-300 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-amber-200 bg-amber-50">
                <h3 class="text-sm font-semibold text-amber-900">Konfirmasi Hasil Auto-Vault</h3>
                <p class="text-xs text-amber-800 mt-0.5">
                    Sistem mendeteksi file ini untuk
                    <span class="font-semibold">{{ $pendingAnalysis['course_name'] ?? 'Mata kuliah belum terdeteksi' }}</span>
                    ·
                    <span class="font-semibold">{{ !empty($pendingAnalysis['week']) ? 'Minggu ' . $pendingAnalysis['week'] : 'Tanpa minggu' }}</span>
                    ·
                    <span class="font-semibold">{{ $pendingAnalysis['topic'] ?: 'Tanpa topik' }}</span>
                </p>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                <div class="md:col-span-2 rounded-lg bg-gray-50 border border-gray-200 p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Mata Kuliah</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $pendingAnalysis['course_name'] ?? 'Belum terdeteksi' }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Minggu</p>
                    <p class="mt-1 font-medium text-gray-900">{{ !empty($pendingAnalysis['week']) ? 'Minggu ' . $pendingAnalysis['week'] : 'Tanpa minggu' }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Topik</p>
                    <p class="mt-1 font-medium text-gray-900 truncate">{{ $pendingAnalysis['topic'] ?: 'Tanpa topik' }}</p>
                </div>
                <div class="md:col-span-2 rounded-lg bg-gray-50 border border-gray-200 p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Confidence</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $pendingAnalysis['confidence'] ?? 0 }}%</p>
                </div>
                <div class="md:col-span-2 rounded-lg bg-gray-50 border border-gray-200 p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Sumber Deteksi</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $pendingAnalysis['source'] ?? 'AI' }}</p>
                </div>
            </div>

            @if($showManualOverride)
                <div class="px-5 pb-5">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                        <h4 class="text-sm font-semibold text-gray-900">Edit Manual</h4>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Mata Kuliah</label>
                            <select wire:model="manualCourseId"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition">
                                <option value="">Pilih mata kuliah</option>
                                @foreach($this->courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->code }} · {{ $course->name }}</option>
                                @endforeach
                            </select>
                            @error('manualCourseId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Minggu</label>
                                <input type="number" wire:model="manualWeek" min="1" max="30" placeholder="Contoh: 5"
                                       class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition">
                                @error('manualWeek') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Topik</label>
                                <input type="text" wire:model="manualTopic" placeholder="Contoh: API Authentication"
                                       class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition">
                                @error('manualTopic') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex flex-wrap justify-end gap-2">
                            <button type="button" wire:click="cancelManualEdit"
                                    class="px-3 py-1.5 bg-white border border-gray-200 text-gray-700 rounded-md text-xs font-medium hover:bg-gray-100 transition">
                                Kembali
                            </button>
                            <button type="button" wire:click="saveManualOverride"
                                    class="px-3 py-1.5 bg-gray-900 text-white rounded-md text-xs font-medium hover:bg-gray-800 transition">
                                Simpan Manual
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="px-5 pb-5 flex flex-wrap justify-end gap-2">
                    <button type="button" wire:click="cancelPendingAnalysis"
                            class="px-3 py-1.5 bg-white border border-gray-200 text-gray-700 rounded-md text-xs font-medium hover:bg-gray-100 transition">
                        Batal
                    </button>
                    <button type="button" wire:click="editManual"
                            class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md text-xs font-medium hover:bg-gray-200 transition">
                        Edit Manual
                    </button>
                    <button type="button" wire:click="confirmAndSave"
                            class="px-3 py-1.5 bg-gray-900 text-white rounded-md text-xs font-medium hover:bg-gray-800 transition">
                        Konfirmasi & Simpan
                    </button>
                </div>
            @endif
        </div>
    @endif

    @if($lastAnalysis)
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Terakhir Diproses</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Keyakinan {{ $lastAnalysis['confidence'] }}% dari {{ $lastAnalysis['source'] }}
                    </p>
                </div>
                <span class="px-2 py-1 rounded-md bg-gray-100 text-[10px] font-semibold text-gray-600">
                    {{ strtoupper(pathinfo($lastAnalysis['stored_path'], PATHINFO_EXTENSION)) }}
                </span>
            </div>
            <div class="p-5 grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                <div class="md:col-span-2 rounded-lg bg-gray-50 border border-gray-200 p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Mata Kuliah</p>
                    <div class="mt-1 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $lastAnalysis['course_color'] }}"></span>
                        <p class="font-medium text-gray-900">{{ $lastAnalysis['course_name'] }}</p>
                    </div>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Minggu</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $lastAnalysis['week'] ? 'Minggu ' . $lastAnalysis['week'] : 'Tanpa minggu' }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Topik</p>
                    <p class="mt-1 font-medium text-gray-900 truncate">{{ $lastAnalysis['topic'] ?: 'Tanpa topik' }}</p>
                </div>
                <div class="md:col-span-4 rounded-lg bg-gray-50 border border-gray-200 p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Folder</p>
                    <p class="mt-1 font-mono text-xs text-gray-700 break-all">{{ $lastAnalysis['folder'] }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ========== RECENT FILES ========== --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-base font-semibold text-gray-900">File Terbaru</h3>
            <p class="text-xs text-gray-500 mt-0.5">20 file paling baru di vault</p>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($this->recentFiles as $file)
                <div class="p-4 hover:bg-gray-50/50 transition group flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                        @php $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)); @endphp
                        <span class="text-[10px] font-bold text-gray-600">{{ strtoupper($ext) }}</span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $file->title }}</p>
                        <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                            <span class="inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $file->course->color }}"></span>
                                {{ $file->course->code }}
                            </span>
                            @if($file->week) <span>· Minggu {{ $file->week }}</span> @endif
                            @if($file->topic) <span>· {{ $file->topic }}</span> @endif
                            @if($file->ai_categorized)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-purple-50 text-purple-700 rounded text-[9px] font-medium">
                                     AI
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-amber-50 text-amber-700 rounded text-[9px] font-medium">
                                     Manual
                                </span>
                            @endif
                        </div>
                    </div>

                    <span class="text-[10px] text-gray-400 self-start sm:self-auto">{{ $file->created_at->diffForHumans() }}</span>

                    <div class="flex items-center justify-end gap-1.5 flex-shrink-0 w-full sm:w-auto">
                        <button type="button"
                                wire:click="openPreview({{ $file->id }})"
                                class="flex-1 sm:flex-none text-center px-2.5 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium transition">
                            Preview
                        </button>
                        <a href="{{ route('vault.files.download', $file) }}"
                           class="flex-1 sm:flex-none text-center px-2.5 py-1.5 rounded-md bg-gray-900 hover:bg-gray-800 text-white text-xs font-medium transition">
                            Unduh
                        </a>
                        <button wire:click="deleteFile({{ $file->id }})"
                                wire:confirm="Hapus file ini?"
                                class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-sm text-gray-500">
                    Belum ada file di vault
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
            context-label="Bank Materi"
            wire-key-prefix="lecturer-vault-preview" />
    @endif
</div>
