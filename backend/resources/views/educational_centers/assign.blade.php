<div class="p-6 text-white">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 border-b border-white/10 pb-6">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center text-xl font-bold text-white shadow-lg shadow-blue-500/20">
                    {{ substr($center->name, 0, 1) }}
                </div>
                <h2 class="text-3xl font-extrabold tracking-tight">{{ $center->name }}</h2>
            </div>
            <p class="text-white/50 font-medium">Ficha detallada del centro educativo</p>
        </div>
        <div class="flex gap-2">
            <button class="btn-modal px-4 py-2 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-bold transition-colors" data-url="{{ route('educational_centers.edit', $center->id) }}" data-title="Editar Centro" data-load="modal">Editar</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda: Información General -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white/5 border border-white/10 rounded-3xl p-6">
                <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-400"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>
                    Información General
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-white/30 uppercase tracking-widest">Administrador</span>
                        <div class="flex items-center gap-3 p-3 bg-black/20 rounded-2xl border border-white/5">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold">
                                {{ $center->adminUser ? substr($center->adminUser->name, 0, 1) : '?' }}
                            </div>
                            <div>
                                <p class="text-sm font-bold">{{ $center->adminUser->name ?? 'No Asignado' }} {{ $center->adminUser->last_name ?? '' }}</p>
                                <p class="text-xs text-white/40">{{ $center->adminUser->email ?? '---' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-white/30 uppercase tracking-widest">Estado del Centro</span>
                        <div class="flex items-center gap-3 p-3 bg-black/20 rounded-2xl border border-white/5">
                            <div class="px-3 py-1 bg-green-500/10 text-green-400 rounded-lg text-xs font-bold border border-green-500/20">
                                ACTIVO
                            </div>
                            <p class="text-xs text-white/40 italic">Registrado en TelamoNet</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-white/5">
                    <span class="text-[10px] font-bold text-white/30 uppercase tracking-widest block mb-4">Ciclos Formativos</span>
                    <div class="flex flex-wrap gap-2">
                        @forelse($center->cycles ?? [] as $cycle)
                            <span class="px-4 py-2 bg-blue-500/10 text-blue-300 rounded-xl text-xs font-bold border border-blue-500/20">
                                {{ $cycle }}
                            </span>
                        @empty
                            <p class="text-sm text-white/30 italic">No hay ciclos definidos aún.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-3xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-400"><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                        Personal y Alumnado
                    </h3>
                    <button class="btn-modal text-xs font-bold text-blue-400 hover:underline" data-url="{{ route('educational_centers.add_users', $center->id) }}" data-title="Gestionar Matrícula" data-load="modal">Gestionar Lista</button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-green-500/5 border border-green-500/10 rounded-2xl flex flex-col items-center justify-center text-center">
                        <span class="text-3xl font-black text-green-400 mb-1">{{ $center->students->count() }}</span>
                        <span class="text-[10px] font-bold text-green-400/50 uppercase">Alumnos</span>
                    </div>
                    <div class="p-4 bg-purple-500/5 border border-purple-500/10 rounded-2xl flex flex-col items-center justify-center text-center">
                        <span class="text-3xl font-black text-purple-400 mb-1">{{ $center->teachers->count() }}</span>
                        <span class="text-[10px] font-bold text-purple-400/50 uppercase">Docentes</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Estadísticas Rápidas -->
        <div class="space-y-6">
             <div class="bg-gradient-to-br from-blue-600 to-cyan-600 rounded-3xl p-6 shadow-xl shadow-blue-900/40 relative overflow-hidden">
                <div class="relative z-10">
                    <h4 class="text-white/80 text-xs font-bold uppercase tracking-widest mb-4">Acción Sugerida</h4>
                    <p class="text-lg font-bold text-white mb-6">Asigna alumnos a sus respectivos tutores.</p>
                    <button class="btn-modal w-full py-3 bg-white text-blue-600 rounded-2xl font-black text-sm shadow-lg hover:scale-[1.02] transition-transform" 
                            data-url="{{ route('educational_centers.assign_view', $center->id) }}" 
                            data-title="Asignación de Tutorías" 
                            data-load="modal">
                        IR A ASIGNACIÓN
                    </button>
                </div>
                <div class="absolute -right-4 -bottom-4 opacity-20 transform rotate-12 scale-150">
                    <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21l-8 -4.5v-9l8 -4.5l8 4.5v9l-8 4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12v9" /><path d="M12 12l-8 -4.5" /><path d="M16 5.25l-8 4.5" /></svg>
                </div>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-3xl p-6">
                <h4 class="text-white/40 text-xs font-bold uppercase tracking-widest mb-4 italic">Metadatos Internos</h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-white/40">ID Privado:</span>
                        <span class="font-mono text-blue-400">#{{ $center->id }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-white/40">Creado el:</span>
                        <span class="text-white/70">{{ $center->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
