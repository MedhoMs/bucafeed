@props([
    'image' => null,
    'fallback' => '?',
    'title' => '',
    'subtitle' => '',
    'shape' => 'rounded-xl',
    'modalUrl' => null,
    'modalTitle' => null,
    'gradient' => 'from-indigo-500 to-cyan-600',
    'imageSize' => 'w-10 h-10',
])

<x-admin.table.td {{ $attributes }}>
    <div class="flex items-center gap-3">
        @php
            $modalClasses = $modalUrl ? 'btn-modal cursor-pointer hover:scale-110 active:scale-95 transition-transform border-2 border-white/20' : '';
        @endphp
        
        <div 
            class="{{ $imageSize }} {{ $shape }} bg-gradient-to-br {{ $gradient }} flex items-center justify-center text-white font-bold text-sm shadow-md overflow-hidden shrink-0 {{ $modalClasses }}"
            @if($modalUrl)
                data-url="{{ $modalUrl }}"
                data-title="{{ $modalTitle }}"
                data-load="modal"
            @endif
        >
            @if($image)
                <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-cover" loading="lazy">
            @else
                {!! $fallback !!}
            @endif
        </div>
        
        <div class="min-w-0 flex-1">
            <div class="font-semibold text-white truncate" title="{{ $title }}">{{ $title }}</div>
            @if($subtitle)
                <div class="text-[10px] sm:text-xs text-white/50 w-full truncate" title="{{ $subtitle }}">{{ $subtitle }}</div>
            @endif
        </div>
    </div>
</x-admin.table.td>
