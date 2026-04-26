@props([
    'title',
    'to',
    'backend' => false,
    'dbStatus' => null
])

@php
    $isExternal = str_starts_with($to, 'http://') || str_starts_with($to, 'https://') || $to === '#';
    $finalUrl = $isExternal ? $to : url($to);
@endphp

@if($backend)
    <a href="{{ $finalUrl }}" {{ $attributes }} class="relative flex items-center gap-2.5 mb-5 rounded-xl text-[17px] font-medium py-3 px-4 text-white no-underline transition-all duration-200 ease-in-out hover:bg-[#406071] w-full">
        {{ $slot }}
        <span class="flex-1">{{ $title }}</span>

        @if($dbStatus === 'connected')
            <span class="ml-auto w-3 h-3 bg-green-500 rounded-full" title="Conectado a DB"></span>
        @elseif($dbStatus === 'error')
            <span class="ml-auto w-3 h-3 bg-red-500 rounded-full" title="Error de conexión"></span>
        @else
            <span class="ml-auto w-3 h-3 bg-yellow-500 rounded-full animate-pulse" title="Cargando..."></span>
        @endif
    </a>
@else
    <a href="{{ $finalUrl }}" {{ $attributes }} class="relative flex items-center gap-2.5 mb-5 rounded-xl text-[17px] font-medium py-3 px-4 text-white no-underline transition-all duration-200 ease-in-out hover:bg-[#406071] w-full">
        {{ $slot }}
        <span class="flex-1">{{ $title }}</span>
    </a>
@endif
