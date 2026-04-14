@props([
    'createUrl',
    'createTitle' => 'Crear Nuevo',
    'createText' => 'Nuevo',
    'emptyText' => 'No hay registros en el sistema.',
    'hasItems' => false,
    'models' => null,
    'searchPlaceholder' => 'Buscar...',
])

<div class="container mx-auto pt-4 relative">
    {{-- Buscador --}}
    <div class="mb-6">
        <form action="{{ url()->current() }}" method="GET" class="relative w-full group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-white/20 group-focus-within:text-cyan-400 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
            </div>
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="{{ $searchPlaceholder }}" 
                class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 pl-11 pr-4 text-sm text-white placeholder:text-white/20 focus:outline-hidden focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all"
            >
            @if(request('search'))
                <a href="{{ url()->current() }}" class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/20 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                </a>
            @endif
        </form>
    </div>

    <div class="flex flex-col gap-3">
        @if($hasItems)
            {{ $slot }}
        @else
            <p class="text-white/50 text-center py-8 text-sm border border-dashed border-white/10 rounded-xl">{{ $emptyText }}</p>
        @endif
    </div>

    {{-- Paginación personalizada --}}
    @if($models && method_exists($models, 'links'))
        <div class="mt-6 flex justify-center">
            {{ $models->appends(request()->query())->links('components.admin.pagination') }}
        </div>
    @endif
    
    <div class="mt-8 flex justify-end gap-3 flex-wrap">
        <button type="button" class="px-5 py-2 rounded-xl bg-slate-700/50 hover:bg-slate-700 text-white transition-all duration-200" data-bs-dismiss="modal" onclick="document.getElementById('default-modal').classList.add('hidden')">
            Cerrar
        </button>

        <a href="#" data-url="{{ $createUrl }}" data-load="modal" data-title="{{ $createTitle }}" class="btn-modal btn-primary px-6 py-2 rounded-xl font-semibold transition-all duration-200">
            {{ $createText }}
        </a>
    </div>
</div>
