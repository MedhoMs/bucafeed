@props([
    'user' => null,
    'size' => 'w-9 h-9',
    'showName' => true,
    'showReputation' => false,
    'class' => '',
    'nameClass' => 'text-white font-medium',
    'subtitleClass' => 'text-white/40 text-[10px] sm:text-xs'
])

@php
    $name = $user ? ($user->name . ' ' . ($user->last_name ?? '')) : 'Usuario';
    $subtitle = $user ? $user->email : '';
    $profileUrl = $user ? route('user.profile_modal', $user->id) : '#';
    $profilePicture = $user?->profile_picture ?? null;
    
    // Fallback logo style from users section
    $fallbackUrl = app()->environment('local') 
        ? asset('logoTelamon.png') 
        : asset('logoTelamon.png');
@endphp

<div class="flex items-center gap-3 {{ $class }}">
    {{-- Avatar Circle --}}
    <div 
        class="{{ $size }} rounded-full bg-linear-to-br from-indigo-500 to-cyan-600 flex items-center justify-center text-white font-bold shadow-md overflow-hidden btn-modal cursor-pointer hover:scale-110 active:scale-95 transition-transform border-2 border-white/20 shrink-0"
        data-url="{{ $profileUrl }}"
        data-title="Perfil de {{ $name }}"
        data-load="modal"
        title="Ver perfil de {{ $name }}"
    >
        @if($profilePicture)
            <img src="{{ $profilePicture }}" alt="{{ $name }}" class="w-full h-full object-cover">
        @else
            <img src="{{ $fallbackUrl }}" alt="Avatar" class="w-full h-full object-cover opacity-60">
        @endif
    </div>
    
    {{-- Info --}}
    @if($showName)
        <div class="min-w-0 flex-1">
            <div class="{{ $nameClass }} truncate" title="{{ $name }}">{{ $name }}</div>
            @if($showReputation && $user)
                <div class="{{ $subtitleClass }}">Reputación: {{ $user->reputation }}</div>
            @elseif($subtitle)
                <div class="{{ $subtitleClass }} truncate" title="{{ $subtitle }}">{{ $subtitle }}</div>
            @endif
        </div>
    @endif
</div>
