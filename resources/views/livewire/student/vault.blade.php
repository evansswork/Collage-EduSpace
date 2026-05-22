<div class="space-y-6">

    {{-- HEADER --}}
    <div>
        <h2 class="text-2xl font-semibold text-gray-900">Materi Kuliah</h2>
        <p class="text-sm text-gray-500 mt-1">
            Semua materi kuliah, terorganisir otomatis berdasarkan mata kuliah dan minggu/topik.
        </p>
    </div>

    {{-- SEARCH BAR --}}
    <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Cari materi..."
               class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 focus:bg-white transition">
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        {{-- FILTER SIDEBAR --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- COURSE FILTER --}}
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Mata Kuliah</p>
                <div class="space-y-1">
                    <button wire:click="selectCourse(null)"
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                                   {{ !$selectedCourse ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        Semua Mata Kuliah
                    </button>

                    @foreach($this->courses as $course)
                        <button wire:click="selectCourse({{ $course->id }})"
                                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition text-left
                                       {{ $selectedCourse === $course->id ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $course->color }}"></div>
                            <span class="truncate">{{ $course->name }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- WEEK FILTER (kondisional) --}}
            @if($selectedCourse && $this->weeks->count() > 0)
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Minggu</p>
                    <div class="flex flex-wrap gap-1.5">
                        <button wire:click="selectWeek(null)"
                                class="px-2.5 py-1 rounded-md text-xs font-medium transition
                                       {{ !$selectedWeek ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Semua
                        </button>
                        @foreach($this->weeks as $week)
                            <button wire:click="selectWeek({{ $week }})"
                                    class="px-2.5 py-1 rounded-md text-xs font-medium transition
                                           {{ $selectedWeek === $week ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Minggu {{ $week }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- FILE GRID --}}
        <div class="lg:col-span-3">

            @if($this->files->count() > 0)
                <p class="text-xs text-gray-500 mb-3">
                    {{ $this->files->count() }} file ditemukan
                    @if($selectedCourse)
                        di <span class="font-medium text-gray-700">{{ $this->courses->find($selectedCourse)->name }}</span>
                    @endif
                </p>

                <div class="space-y-5">
                    @foreach($this->files->groupBy('course_id') as $courseFiles)
                        @php $course = $courseFiles->first()->course; @endphp
                        <section class="space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $course->color }}"></span>
                                <h3 class="text-sm font-semibold text-gray-900">{{ $course->name }}</h3>
                                <span class="text-[10px] text-gray-400">{{ $courseFiles->count() }} file</span>
                            </div>

                            @foreach($courseFiles->groupBy(fn($file) => $file->week ?: 'none') as $week => $weekFiles)
                                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                                    <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                            </svg>
                                            <p class="text-xs font-semibold text-gray-700">
                                                {{ $week === 'none' ? 'Tanpa minggu' : 'Minggu ' . $week }}
                                            </p>
                                        </div>
                                        <span class="text-[10px] text-gray-400">{{ $weekFiles->count() }} item</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 p-3">
                                        @foreach($weekFiles as $file)
                                            @php $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)); @endphp
                                            <button wire:click="openPreview({{ $file->id }})"
                                                    class="group text-left border border-gray-200 rounded-lg p-3 hover:border-gray-400 hover:shadow-sm transition">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                                                                {{ $ext === 'pdf' ? 'bg-red-50' : '' }}
                                                                {{ in_array($ext, ['py','js','java','php','md','txt','json','sql','html','css']) ? 'bg-blue-50' : '' }}
                                                                {{ $ext === 'zip' ? 'bg-amber-50' : '' }}
                                                                {{ in_array($ext, ['doc','docx','ppt','pptx']) ? 'bg-indigo-50' : '' }}
                                                                {{ !in_array($ext, ['pdf','py','js','java','php','md','txt','json','sql','html','css','zip','doc','docx','ppt','pptx']) ? 'bg-gray-100' : '' }}">
                                                        <span class="text-[10px] font-bold
                                                                     {{ $ext === 'pdf' ? 'text-red-600' : '' }}
                                                                     {{ in_array($ext, ['py','js','java','php','md','txt','json','sql','html','css']) ? 'text-blue-600' : '' }}
                                                                     {{ $ext === 'zip' ? 'text-amber-600' : '' }}
                                                                     {{ in_array($ext, ['doc','docx','ppt','pptx']) ? 'text-indigo-600' : '' }}
                                                                     {{ !in_array($ext, ['pdf','py','js','java','php','md','txt','json','sql','html','css','zip','doc','docx','ppt','pptx']) ? 'text-gray-600' : '' }}">
                                                            {{ strtoupper($ext ?: 'FILE') }}
                                                        </span>
                                                    </div>

                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-medium text-gray-900 line-clamp-2 group-hover:text-black">
                                                            {{ $file->title }}
                                                        </p>
                                                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-xs text-gray-500">
                                                            <span>{{ $file->course->code }}</span>
                                                            @if($file->topic)
                                                                <span>·</span>
                                                                <span class="truncate">{{ $file->topic }}</span>
                                                            @endif
                                                        </div>
                                                        <p class="text-[10px] text-gray-400 mt-1">{{ $file->human_size }}</p>
                                                    </div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </section>
                    @endforeach
                </div>
            @else
                <div class="border border-dashed border-gray-300 rounded-xl p-12 text-center">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm text-gray-600 font-medium">Tidak ada materi ditemukan</p>
                    <p class="text-xs text-gray-500 mt-1">Coba ubah filter atau kata kunci pencarian</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ========== UNIVERSAL FILE PREVIEW MODAL ========== --}}
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
             wire:key="preview-{{ $previewFile->id }}">

            <div @click.outside="$wire.closePreview()"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden min-h-0">

                {{-- Modal header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-semibold text-gray-900 truncate">{{ $previewFile->title }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $previewFile->course->name }}
                            @if($previewFile->week) · Minggu {{ $previewFile->week }} @endif
                            @if($previewFile->topic) · {{ $previewFile->topic }} @endif
                            · {{ $previewFile->human_size }}
                        </p>
                    </div>
                    <button wire:click="closePreview"
                            class="w-9 h-9 flex items-center justify-center rounded-md hover:bg-gray-100 transition">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Preview body --}}
                <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain bg-gray-50 p-6">
                    @if($preview['type'] === 'missing')
                        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12V16.5zm9-4.5a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-700">File belum ditemukan</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $preview['message'] }}</p>
                        </div>
                    @elseif($preview['type'] === 'pdf')
                        <iframe src="{{ $preview['inline_url'] }}"
                                class="w-full h-[65vh] bg-white rounded-lg border border-gray-200"
                                title="Preview PDF"></iframe>
                    @elseif($preview['type'] === 'image')
                        <div class="bg-white rounded-lg border border-gray-200 p-4 flex justify-center">
                            <img src="{{ $preview['inline_url'] }}" alt="{{ $previewFile->title }}" class="max-h-[65vh] object-contain">
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
                                <div class="p-6 prose prose-sm max-w-none">
                                    <pre class="whitespace-pre-wrap font-sans text-sm leading-6 text-gray-800">{{ $preview['content'] }}</pre>
                                </div>
                            @else
                                <div class="p-8 text-center">
                                    <p class="text-sm text-gray-600">Isi dokumen belum bisa diekstrak.</p>
                                    <p class="text-xs text-gray-500 mt-1">File tetap tersedia melalui tombol unduh.</p>
                                </div>
                            @endif
                        </div>
                    @elseif($preview['type'] === 'zip')
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Isi Folder ZIP</p>
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
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm text-gray-600">{{ $preview['message'] }}</p>
                        </div>
                    @endif
                </div>

                {{-- Modal footer --}}
                <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 flex justify-between items-center">
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
