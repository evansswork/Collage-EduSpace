@props([
    'previewFile',
    'preview',
    'contextLabel' => 'Preview File',
    'wireKeyPrefix' => 'file-preview',
])

@php
    $title = $previewFile->title ?? $previewFile->file_name ?? 'File';
    $subtitleParts = [$contextLabel];

    if (isset($previewFile->course)) {
        $subtitleParts[] = $previewFile->course->name;
    }

    if (!empty($previewFile->week)) {
        $subtitleParts[] = 'Minggu ' . $previewFile->week;
    }

    if (!empty($previewFile->topic)) {
        $subtitleParts[] = $previewFile->topic;
    }

    if (!empty($previewFile->human_size)) {
        $subtitleParts[] = $previewFile->human_size;
    }
@endphp

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
     wire:key="{{ $wireKeyPrefix }}-{{ $previewFile->id }}">

    <div @click.outside="$wire.closePreview()"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden min-h-0">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold text-gray-900 truncate">{{ $title }}</h3>
                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ implode(' · ', array_filter($subtitleParts)) }}</p>
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
                        title="Preview file"></iframe>
            @elseif($preview['type'] === 'image')
                <div class="bg-white rounded-lg border border-gray-200 p-4 flex justify-center">
                    <img src="{{ $preview['inline_url'] }}" alt="{{ $title }}" class="max-h-[65vh] object-contain">
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
                @if(isset($previewFile->uploader))
                    Diunggah oleh <span class="text-gray-700 font-medium">{{ $previewFile->uploader->name }}</span>
                    ·
                @endif
                {{ $previewFile->created_at->diffForHumans() }}
            </p>
            @if(!empty($preview['download_url']))
                <a href="{{ $preview['download_url'] }}"
                   class="px-3 py-1.5 bg-white border border-gray-200 rounded-md text-xs font-medium text-gray-700 hover:bg-gray-100 transition">
                    Unduh File
                </a>
            @endif
        </div>
    </div>
</div>
