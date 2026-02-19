@extends('layouts.admin')

@section('content')
<div class="p-8 min-h-screen text-white bg-gradient-to-b from-[#1a3a3a] via-[#10202e] to-[#0a141d]">
    
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Gestión de Usuarios</h1>
            <p class="text-white/60 text-sm mt-1">Administra los usuarios registrados en la plataforma.</p>
        </div>
        <button 
            class="btn-modal bg-cyan-600 hover:bg-cyan-500 text-white px-5 py-2.5 rounded-xl font-medium transition-colors shadow-lg shadow-cyan-900/20 flex items-center gap-2"
            data-url="{{ route('user.create') }}"
            data-title="Crear Nuevo Usuario"
            data-load="modal"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
            Nuevo Usuario
        </button>
    </div>

    <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden shadow-xl backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-white/80">
                <thead class="text-xs text-white uppercase bg-white/10">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Acciones</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Usuario</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">DNI/NIE</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Rol</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Reputación</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Nivel / Institución</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($users as $user)
                    <tr class="hover:bg-white/5 transition-colors duration-150">
                        <td class="px-6 py-4 text-left">
                            <div class="flex items-center justify-start gap-2">
                                <a class="btn-modal p-2 text-white/60 hover:text-blue-400 hover:bg-blue-400/10 rounded-lg transition-colors cursor-pointer" 
                                   title="Consultar"
                                   data-url="{{ route('user.show', $user->id) }}"
                                   data-title="Consultar Usuario"
                                   data-load="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                </a>
                                <a class="btn-modal p-2 text-white/60 hover:text-cyan-400 hover:bg-cyan-400/10 rounded-lg transition-colors cursor-pointer" 
                                   title="Editar"
                                   data-url="{{ route('user.edit', $user->id) }}"
                                   data-title="Editar Usuario"
                                   data-load="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                                </a>
                                <a class="btn-modal p-2 text-white/60 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-colors cursor-pointer" 
                                title="Eliminar" data-url="{{ route('user.destroy', $user->id) }}" data-title="Eliminar Usuario" data-load="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                </a>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-white/70">
                            #{{ $user->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-white">{{ $user->name }} {{ $user->last_name }}</div>
                                    <div class="text-xs text-white/50">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-white/70">
                            {{ $user->dni ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                           <div class="flex items-center gap-1 text-yellow-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-coins"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 14c0 1.657 2.686 3 6 3s6 -1.343 6 -3s-2.686 -3 -6 -3s-6 1.343 -6 3z" /><path d="M9 14v4c0 1.656 2.686 3 6 3s6 -1.344 6 -3v-4" /><path d="M3 6c0 1.072 1.144 2.062 3 2.598s4.144 .536 6 0c1.856 -.536 3 -1.526 3 -2.598c0 -1.072 -1.144 -2.062 -3 -2.598s-4.144 -.536 -6 0c-1.856 .536 -3 1.526 -3 2.598z" /><path d="M3 6v10c0 .888 .772 1.45 2 2" /><path d="M3 11c0 .888 .772 1.45 2 2" /></svg>
                                <span class="font-bold">{{ $user->reputation }}</span>
                           </div>
                        </td>
                        <td class="px-6 py-4 text-white/70">
                            <div class="flex flex-col">
                                <span class="text-sm">{{ \App\Models\EducationalCenter::$niveles_disponibles[$user->education_level] ?? $user->education_level ?? '-' }}</span>
                                <span class="text-xs text-white/40">{{ $user->institution_name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-white/70 text-sm">
                            {{ $user->created_at->format('Y-m-d') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
    </div>
</div>
@endsection



