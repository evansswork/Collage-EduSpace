<div x-data="{ open: false }" class="relative">
    <button @click="open = !open"
            class="relative w-9 h-9 flex items-center justify-center rounded-md hover:bg-gray-100 transition"
            aria-label="Notifikasi">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($this->unreadMandatoryCount + $this->unreadInfoCount > 0)
        @endif
    </button>

    <div x-show="open"
         @click.outside="open = false"
         x-transition
         x-cloak
         class="absolute right-0 mt-2 w-[calc(100vw-2rem)] max-w-sm sm:w-96 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-30">
        <div class="flex border-b border-gray-200">
            <button wire:click="setTab('mandatory')"
                    class="flex-1 px-4 py-3 text-sm font-medium transition relative {{ $activeTab === 'mandatory' ? 'text-gray-900 bg-white' : 'text-gray-500 hover:text-gray-700 bg-gray-50' }}">
                Wajib Dikerjakan
                @if($this->unreadMandatoryCount > 0)
                    <span class="ml-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-semibold text-white bg-red-500 rounded-full">
                        {{ $this->unreadMandatoryCount }}
                    </span>
                @endif
            </button>

            <button wire:click="setTab('info')"
                    class="flex-1 px-4 py-3 text-sm font-medium transition relative {{ $activeTab === 'info' ? 'text-gray-900 bg-white' : 'text-gray-500 hover:text-gray-700 bg-gray-50' }}">
                Informasi Lainnya
                @if($this->unreadInfoCount > 0)
                    <span class="ml-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-semibold text-gray-700 bg-gray-200 rounded-full">
                        {{ $this->unreadInfoCount }}
                    </span>
                @endif
            </button>
        </div>

        <div class="max-h-[70vh] sm:max-h-96 overflow-y-auto">
            @forelse($this->notifications as $n)
                <button wire:click="markRead({{ $n->id }})"
                        @if($n->link) onclick="window.location='{{ $n->link }}'" @endif
                        class="w-full text-left px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0 transition flex items-start gap-3 {{ !$n->read ? 'bg-blue-50/30' : '' }}">
                    <div class="w-2 h-2 mt-1.5 rounded-full flex-shrink-0 {{ $n->category === 'mandatory' ? ($n->read ? 'bg-gray-300' : 'bg-red-500') : ($n->read ? 'bg-gray-300' : 'bg-blue-500') }}"></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 leading-tight">{{ $n->title }}</p>
                        @if($n->body)
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $n->body }}</p>
                        @endif
                        <p class="text-[10px] text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                </button>
            @empty
                <div class="px-4 py-12 text-center">
                    <p class="text-sm text-gray-500">Tidak ada notifikasi</p>
                </div>
            @endforelse
        </div>

        @if($this->notifications->where('read', false)->count() > 0)
            <div class="border-t border-gray-200 px-4 py-2">
                <button wire:click="markAllRead" class="text-xs text-gray-600 hover:text-gray-900 font-medium">
                    Tandai semua sudah dibaca
                </button>
            </div>
        @endif
    </div>
</div>
