@props([
    'title',
    'description',
    'createUrl' => null,
    'createTitle' => 'Crear Nuevo',
    'createText' => 'Nuevo',
    'headers' => [],
    'models' => null, {{-- Varias para paginación --}}
    'searchPlaceholder' => 'Buscar...',
    'filterLabels' => [], {{-- Mapeo de valores a nombres legibles --}}
])

@extends('layouts.admin')

@section('content')
<div class="p-4 sm:p-8 min-h-screen text-white bg-linear-to-b from-(--admin-bg-gradient-start) via-(--admin-bg-gradient-via) to-(--admin-bg-main)">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-5">
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $title }}</h1>
            <p class="text-cyan-200/50 text-sm font-medium">{{ $description }}</p>
        </div>
        @if($createUrl)
        <button 
            class="btn-modal btn-primary px-6 py-3 rounded-2xl font-bold flex items-center gap-2.5 w-full md:w-auto justify-center shadow-lg shadow-(--admin-primary-glow) hover:shadow-(--admin-primary)/40 transition-all active:scale-95 border border-white/10"
            style="background-color: var(--admin-primary);"
            data-url="{{ $createUrl }}"
            data-title="{{ $createTitle }}"
            data-load="modal"
        >
            <div class="bg-white/20 p-1 rounded-lg">
                <x-admin.constants.icons name="plus" />
            </div>
            <span>{{ $createText }}</span>
        </button>
        @endif
    </div>
    
    {{-- Buscador y Filtros Avanzados --}}
    <div class="mb-8 space-y-6">
        <div class="flex flex-col lg:flex-row gap-5 items-stretch lg:items-center justify-start">
            <form action="{{ url()->current() }}" method="GET" class="relative w-full lg:max-w-md group" data-load="section">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-white/20 group-focus-within:text-(--admin-primary) transition-colors">
                    <x-admin.constants.icons name="search" />
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="{{ $searchPlaceholder }}" 
                    class="w-full bg-(--admin-bg-input) border border-white/10 rounded-2xl py-3 pl-12 pr-6 text-sm text-white placeholder:text-white/20 focus:outline-hidden focus:ring-2 focus:ring-(--admin-primary)/40 focus:border-(--admin-primary)/40 transition-all shadow-inner"
                >
                @if(request('search'))
                    <a href="{{ url()->current() }}?{{ http_build_query(request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-5 flex items-center text-white/20 hover:text-white transition-colors" data-load="section">
                        <x-admin.constants.icons name="close" />
                    </a>
                @endif
            </form>

            <div class="flex flex-wrap gap-3 items-center">
                {{ $filters ?? '' }}
            </div>
        </div>

        {{-- Filtros Activos --}}
        @php
            $activeFilters = request()->except(['search', 'page']);
        @endphp
        @if(!empty($activeFilters) || request('search'))
            <div class="flex items-center gap-3 text-xs animate-in fade-in slide-in-from-left-2 duration-300">
                <span class="text-white/30 font-bold uppercase tracking-widest">Filtros activos:</span>
                <div class="flex flex-wrap gap-2">
                    @if(request('search'))
                        <div class="px-3 py-1.5 bg-(--admin-primary-soft) border border-(--admin-primary)/30 text-(--admin-primary) rounded-full flex items-center gap-2">
                             <span class="font-medium">Busca: "{{ request('search') }}"</span>
                             <a href="{{ url()->current() }}?{{ http_build_query(request()->except('search')) }}" data-load="section" class="hover:text-white"><x-admin.constants.icons name="delete" /></a>
                        </div>
                    @endif

                    @foreach($activeFilters as $key => $value)
                        @if($value)
                            @php
                                $friendlyKeys = [
                                    'type' => 'Tipo',
                                    'role' => 'Rol',
                                    'level' => 'Nivel',
                                    'institution' => 'Centro',
                                    'cycle' => 'Ciclo'
                                ];
                                $displayKey = $friendlyKeys[$key] ?? ucfirst($key);
                                $displayValue = $filterLabels[$key][$value] ?? ($filterLabels[$value] ?? ($filterLabels[$key] ?? $value));
                            @endphp
                            <div class="px-3 py-1.5 bg-white/10 border border-white/20 text-white/90 rounded-full flex items-center gap-2">
                                <span class="text-white/40 uppercase font-bold text-[9px]">{{ $displayKey }}:</span>
                                <span class="font-medium">{{ $displayValue }}</span>
                                <a href="{{ url()->current() }}?{{ http_build_query(request()->except($key)) }}" data-load="section" class="hover:text-white"><x-admin.constants.icons name="delete" /></a>
                            </div>
                        @endif
                    @endforeach

                    <a href="{{ url()->current() }}" data-load="section" class="px-3 py-1.5 bg-white/5 hover:bg-white/10 border border-white/10 text-white/60 hover:text-white rounded-full transition-all flex items-center gap-2">
                        <x-admin.constants.icons name="close" class="w-3! h-3!" />
                        <span class="font-bold">Limpiar todo</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <div class="bg-(--admin-bg-card) border border-white/10 rounded-3xl overflow-hidden shadow-2xl backdrop-blur-subtle">
        <div class="overflow-x-auto custom-scroll">
            <table class="w-full text-sm text-left text-white/70">
                <thead class="text-[10px] text-white/40 uppercase bg-[#0f1922]/80 sticky top-0 z-10 border-b border-white/5">
                    <tr>
                        <th scope="col" class="px-6 py-5 font-black tracking-widest">Acciones</th>
                        @if(!empty($headers))
                            @foreach($headers as $key => $value)
                                @php
                                    $label = is_string($key) ? $key : $value;
                                    $classes = is_string($key) ? $value : '';
                                @endphp
                                <th scope="col" class="px-6 py-5 font-black tracking-widest {{ $classes }}">{{ $label }}</th>
                            @endforeach
                        @else
                            {{ $thead ?? '' }}
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    {{ $tbody ?? '' }}
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación personalizada --}}
    @if(isset($models) && method_exists($models, 'links'))
        <div class="mt-8 px-2 flex justify-center">
            {{ $models->appends(request()->query())->links('components.admin.pagination') }}
        </div>
    @endif
        
    {{ $slot }}
</div>
@endsection
