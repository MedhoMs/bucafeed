@props(['url', 'title', 'color' => 'blue'])

@php
    $colors = [
        'blue' => 'hover:text-blue-400 hover:bg-blue-400/10',
        'cyan' => 'hover:text-cyan-400 hover:bg-cyan-400/10',
        'red' => 'hover:text-red-400 hover:bg-red-400/10',
    ];
    $colorClass = $colors[$color] ?? $colors['blue'];
@endphp

@if($url)
<a class="btn-modal p-2 text-white/60 {{ $colorClass }} rounded-lg transition-colors cursor-pointer" 
   title="{{ $title }}"
   data-url="{{ $url }}"
   data-title="{{ $title }}"
   data-load="modal">
    {{ $slot }}
</a>
@endif
