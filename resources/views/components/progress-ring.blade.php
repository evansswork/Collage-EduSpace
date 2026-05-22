@props([
    'percentage' => 0,
    'size' => 160,
    'stroke' => 12,
    'label' => null,
])

@php
    $radius = ($size - $stroke) / 2;
    $circumference = 2 * M_PI * $radius;
    $offset = $circumference - ($percentage / 100) * $circumference;
    $center = $size / 2;

    // Dopamine hit: warna berubah berdasarkan progress
    $color = match(true) {
        $percentage >= 100 => '#10B981', // hijau cerah (selesai!)
        $percentage >= 75  => '#22C55E', // hijau
        $percentage >= 50  => '#F59E0B', // kuning
        $percentage >= 25  => '#F97316', // oranye
        default            => '#EF4444', // merah
    };
@endphp

<div class="relative inline-flex items-center justify-center" style="width: {{ $size }}px; height: {{ $size }}px;">
    <svg width="{{ $size }}" height="{{ $size }}" class="transform -rotate-90">
        {{-- Track --}}
        <circle
            cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}"
            fill="none" stroke="#F3F4F6" stroke-width="{{ $stroke }}"
        />
        {{-- Progress --}}
        <circle
            cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}"
            fill="none"
            stroke="{{ $color }}"
            stroke-width="{{ $stroke }}"
            stroke-linecap="round"
            stroke-dasharray="{{ $circumference }}"
            stroke-dashoffset="{{ $offset }}"
            style="transition: stroke-dashoffset 0.8s cubic-bezier(0.4, 0, 0.2, 1), stroke 0.5s ease;"
        />
    </svg>

    {{-- Center label --}}
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        <span class="text-3xl font-bold" style="color: {{ $color }}">{{ $percentage }}%</span>
        @if($label)
            <span class="text-xs text-gray-500 mt-1">{{ $label }}</span>
        @endif
    </div>
</div>
