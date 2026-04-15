@props([
    'items' => [],
    'label' => 'Ver lista',
    'count' => 0,
    'color' => 'blue', // blue, green, purple, yellow
    'icon' => null
])

@php
    $colors = [
        'blue'   => 'text-blue-400 bg-blue-500/10 border-blue-500/20 hover:bg-blue-500/20',
        'green'  => 'text-green-400 bg-green-500/10 border-green-500/20 hover:bg-green-500/20',
        'purple' => 'text-purple-400 bg-purple-500/10 border-purple-500/20 hover:bg-purple-500/20',
        'yellow' => 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20 hover:bg-yellow-500/20',
        'red'    => 'text-red-400 bg-red-500/10 border-red-500/20 hover:bg-red-500/20',
    ];
    $activeColor = $colors[$color] ?? $colors['blue'];
    $dropdownId = 'dropdown-' . uniqid();
@endphp

<div class="relative inline-block text-left dropdown-container">
    <button
        onclick="toggleDropdownList('{{ $dropdownId }}', event)"
        type="button"
        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-bold transition-all shadow-xs hover:scale-105 active:scale-95 {{ $activeColor }}"
    >
        @if($icon)
            <span class="opacity-80 shrink-0">{!! $icon !!}</span>
        @endif
        <span class="min-w-[1ch]">{{ $count }}</span>
    </button>

    <div
        id="{{ $dropdownId }}"
        class="hidden absolute z-100 mt-2 w-56 origin-top-right rounded-xl bg-[#132a2a] border border-white/10 shadow-2xl shadow-black/80 ring-1 ring-black ring-opacity-5 focus:outline-hidden right-0"
    >
        <div class="p-2 space-y-1">
            <div class="px-3 py-2 text-[10px] font-bold text-white/40 uppercase tracking-widest border-b border-white/5 mb-1">
                {{ $label }}
            </div>
            <div class="max-h-50 overflow-y-auto custom-scroll px-1">
                @forelse($items as $item)
                    <div class="flex items-center gap-2 p-2 rounded-lg hover:bg-white/5 transition-colors group text-left">
                        <div class="w-1.5 h-1.5 rounded-full {{ str_replace('text-', 'bg-', explode(' ', $activeColor)[0]) }} shrink-0"></div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-medium text-white/90 truncate">{{ $item['name'] ?? 'N/A' }}</span>
                            @if(isset($item['subtitle']))
                                <span class="text-[10px] text-white/40 truncate">{{ $item['subtitle'] }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-3 text-center text-xs text-white/30 italic">
                        No hay registros
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@once
<script>
    window.toggleDropdownList = function(id, event) {
        event.stopPropagation();
        const dropdown = document.getElementById(id);
        const arrow = document.querySelector('.dropdown-arrow-' + id);

        // Close all other dropdowns
        document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
            if (el.id !== id) el.classList.add('hidden');
        });

        const isHidden = dropdown.classList.contains('hidden');

        if (isHidden) {
            dropdown.classList.remove('hidden');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            dropdown.classList.add('hidden');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    };

    // Close on click outside
    document.addEventListener('click', function() {
        document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('[class*="dropdown-arrow-"]').forEach(el => el.style.transform = 'rotate(0deg)');
    });
</script>
<style>
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
    .custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    .custom-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
</style>
@endonce
