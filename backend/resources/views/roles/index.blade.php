@extends('layouts.admin')

@section('content')
<div class="p-4 sm:p-8 min-h-screen text-white bg-gradient-to-b from-[#1a3a3a] via-[#10202e] to-[#0a141d]">
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 sm:gap-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white">Gestión de Roles</h1>
            <p class="text-white/60 text-sm mt-1">Administra los roles del sistema de TelamoNet.</p>
        </div>
        <button 
            class="btn-modal bg-cyan-600 hover:bg-cyan-500 text-white px-5 py-2.5 rounded-xl font-medium transition-colors shadow-lg shadow-cyan-900/20 flex items-center gap-2 w-full sm:w-auto justify-center"
            data-url="{{ route('role.create') }}"
            data-title="Crear Nuevo Rol"
            data-load="modal"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
            Nuevo Rol
        </button>
    </div>

    <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden shadow-xl backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-white/80">
                <thead class="text-xs text-white uppercase bg-[#1a3a3a]/90 sticky top-0 z-10 backdrop-blur-md">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Acciones</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Nombre del Rol</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Código</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Creado el</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($roles as $role)
                    <tr class="hover:bg-white/5 transition-colors duration-150">
                        <td class="px-6 py-4 text-left">
                            <div class="flex items-center justify-start gap-2">
                                <a class="btn-modal p-2 text-white/60 hover:text-cyan-400 hover:bg-cyan-400/10 rounded-lg transition-colors cursor-pointer" 
                                   title="Editar"
                                   data-url="{{ route('role.edit', $role->id) }}"
                                   data-title="Editar Rol"
                                   data-load="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                                </a>
                                <a class="btn-modal p-2 text-white/60 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-colors cursor-pointer" 
                                title="Eliminar" data-url="{{ route('role.destroy', $role->id) }}" data-title="Eliminar Rol" data-load="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                </a>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-white/70">
                            #{{ $role->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div>
                                    <div class="font-semibold text-white">{{ $roles_disponibles[$role->code] ?? $role->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-white/70">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                {{ $role->code ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-white/70 text-sm">
                            {{ $role->created_at ? $role->created_at->format('Y-m-d') : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
    </div>
</div>
@endsection
