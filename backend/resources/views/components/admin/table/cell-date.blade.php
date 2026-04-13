@props([
    'date',
    'startTime' => null,
    'endTime' => null,
    'formatDate' => 'd M Y',
    'formatTime' => 'H:i',
])

<x-admin.table.td {{ $attributes }}>
    <div class="flex flex-col">
        @if($date)
            <span class="text-sm font-medium text-white">{{ \Carbon\Carbon::parse($date)->format($formatDate) }}</span>
        @else
            <span class="text-sm font-medium text-white/50">-</span>
        @endif
        
        @if($startTime || $endTime)
            <span class="text-xs text-cyan-300 mt-1">
                {{ $startTime ? \Carbon\Carbon::parse($startTime)->format($formatTime) : '' }} 
                @if($startTime && $endTime) - @endif
                {{ $endTime ? \Carbon\Carbon::parse($endTime)->format($formatTime) : '' }}
            </span>
        @endif
    </div>
</x-admin.table.td>
