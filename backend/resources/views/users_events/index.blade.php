@extends('layouts.admin')

@section('content')
<div class="p-4 sm:p-8 min-h-screen text-white bg-gradient-to-b from-[#1a3a3a] via-[#10202e] to-[#0a141d]">
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 sm:gap-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white">Gestión de Eventos</h1>
            <p class="text-white/60 text-sm mt-1">Administra los eventos de los centros educativos.</p>
        </div>
        <button 
            class="btn-modal btn-primary px-5 py-2.5 rounded-xl font-medium flex items-center gap-2 w-full sm:w-auto justify-center"
            data-url="{{ route('users_events.create') }}"
            data-title="Crear Nuevo Evento"
            data-load="modal"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
            Nuevo Evento
        </button>
    </div>

    <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden shadow-xl backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-white/80">
                <thead class="text-xs text-white uppercase bg-[#1a3a3a]/90 sticky top-0 z-10 backdrop-blur-md">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Acciones</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Evento</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Centro Educativo</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Fecha / Horario</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Lugar</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider text-center">Rol Target</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($events as $event)
                    <tr class="hover:bg-white/5 transition-colors duration-150">
                        <td class="px-6 py-4 text-left">
                            <div class="flex items-center justify-start gap-2">
                                <a class="btn-modal p-2 text-white/60 hover:text-blue-400 hover:bg-blue-400/10 rounded-lg transition-colors cursor-pointer" 
                                   title="Consultar"
                                   data-url="{{ route('users_events.show', $event->id) }}"
                                   data-title="Consultar Evento"
                                   data-load="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                </a>
                                <a class="btn-modal p-2 text-white/60 hover:text-cyan-400 hover:bg-cyan-400/10 rounded-lg transition-colors cursor-pointer" 
                                   title="Editar"
                                   data-url="{{ route('users_events.edit', $event->id) }}"
                                   data-title="Editar Evento"
                                   data-load="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                                </a>
                                <a class="btn-modal p-2 text-white/60 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-colors cursor-pointer" 
                                   title="Eliminar" data-url="{{ route('users_events.destroy', $event->id) }}" data-title="Eliminar Evento" data-load="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-white/70">
                            #{{ $event->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                @if($event->image_url)
                                    <img src="{{ $event->image_url }}" class="w-10 h-10 rounded-lg object-cover shadow-md" alt="" loading="lazy">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-cyan-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                        {{ substr($event->title, 0, 1) }}
                                    </div>
                                @endif
                                <div class="w-48">
                                    <div class="font-semibold text-white truncate" title="{{ $event->title }}">{{ $event->title }}</div>
                                    <div class="text-xs text-white/50 w-full truncate" title="{{ $event->description }}">{{ $event->description ?: 'Sin descripción' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-white/80 max-w-[150px] truncate" title="{{ $event->educationalCenter->name ?? '-' }}">
                            {{ $event->educationalCenter->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-white">{{ \Carbon\Carbon::parse($event->date)->format('d M Y') }}</span>
                                <span class="text-xs text-cyan-300 mt-1">{{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-white/70 max-w-[150px] truncate" title="{{ $event->location }}">
                            {{ $event->location ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($event->target_role)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium tracking-wide uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    {{ $roles_disponibles[$event->target_role] ?? $event->target_role }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium tracking-wide uppercase bg-white/10 text-white/50 border border-white/20">
                                    Todos
                                </span>
                            @endif
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
    </div>
</div>
@endsection
