@props([
    'title' => null,
    'badge' => null,
    'badgeClass' => 'bg-purple-500/20 text-purple-300 border border-purple-500/30',
    'titleClass' => 'text-white font-medium',
    'editUrl' => null,
    'editTitle' => 'Editar',
    'deleteUrl' => null,
    'deleteTitle' => 'Eliminar',
    'showUrl' => null,
    'showTitle' => 'Consultar',
])
<div class="flex items-center justify-between p-4 bg-white/5 border border-white/10 rounded-xl group transition-all duration-200 hover:bg-white/10">
    <div class="flex items-center gap-3">
        @if($title)
        <span class="{{ $titleClass }}">{{ $title }}</span>
        @endif
        
        @if($badge)
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
            {{ $badge }}
        </span>
        @endif
        
        {{ $slot }}
    </div>
    
    <x-admin.crud-actions 
        :showUrl="$showUrl"
        :showTitle="$showTitle"
        :editUrl="$editUrl"
        :editTitle="$editTitle"
        :deleteUrl="$deleteUrl"
        :deleteTitle="$deleteTitle"
    />
</div>
