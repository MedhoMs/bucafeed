@props([
    'label',
    'name',
    'options' => [],
    'selected' => null,
    'align' => 'left',
])

<div class="relative inline-block text-left filter-dropdown">
    <button 
        type="button" 
        class="dropdown-toggle inline-flex justify-between items-center w-full px-5 py-2.5 bg-(--admin-bg-input) border border-(--admin-primary)/40 rounded-xl text-xs font-bold text-white hover:bg-(--admin-primary)/10 transition-all active:scale-95 border-dashed"
    >
        <span>{{ $label }}</span>
        <x-admin.constants.icons name="filter" />
    </button>

    <div class="dropdown-menu hidden absolute {{ $align === 'right' ? 'right-0' : 'left-0' }} mt-2 w-56 rounded-2xl shadow-2xl bg-[#0f1922] border border-white/10 ring-1 ring-black ring-opacity-5 focus:outline-hidden z-100 overflow-hidden">
        <div class="py-1 max-h-64 overflow-y-auto custom-scroll">
            {{-- Opción para limpiar este filtro --}}
            @if($selected)
                <a href="{{ url()->current() }}?{{ http_build_query(request()->except($name)) }}" 
                   data-load="section"
                   class="block px-4 py-3 text-[10px] uppercase tracking-widest font-black text-red-400/70 hover:bg-red-500/10 hover:text-red-400 transition-colors border-b border-white/5 mb-1">
                    Limpiar Filtro
                </a>
            @endif

            @foreach($options as $value => $text)
                <a 
                    href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), [$name => $value])) }}" 
                    data-load="section"
                    class="block px-4 py-3 text-sm text-white/50 hover:bg-(--admin-primary-soft) hover:text-white transition-colors {{ $selected == $value ? 'bg-(--admin-primary)/40 text-white font-bold' : '' }}"
                >
                    {{ $text }}
                </a>
            @endforeach
        </div>
    </div>
</div>
