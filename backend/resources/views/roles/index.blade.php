<div class="container mx-auto pt-4 relative">
    <div class="flex flex-col gap-3">
        @forelse($roles as $role)
            <div class="flex items-center justify-between p-4 bg-white/5 border border-white/10 rounded-xl group transition-all duration-200 hover:bg-white/10">
                <div class="flex items-center gap-3">
                    <span class="text-white font-medium">{{ $role->name }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 text-purple-300 border border-purple-500/30">
                        {{ $role->code ?? '-' }}
                    </span>
                </div>
                
                <div class="flex items-center gap-2">
                    <a href="#" data-url="{{ route('role.edit', $role->id) }}" data-load="modal" data-title="Editar Rol"
                       class="btn-modal p-2 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white opacity-0 group-hover:opacity-100 transition-all duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </a>
                    <a href="#" data-url="{{ route('role.destroy', $role->id) }}" data-load="modal" data-title="Eliminar Rol"
                       class="btn-modal p-2 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white opacity-0 group-hover:opacity-100 transition-all duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <p class="text-white/50 text-center py-8 text-sm border border-dashed border-white/10 rounded-xl">No hay roles registrados en el sistema.</p>
        @endforelse
    </div>
    
    <div class="mt-8 flex justify-end gap-3 flex-wrap">
        <button type="button" class="px-5 py-2 rounded-xl bg-slate-700/50 hover:bg-slate-700 text-white transition-all duration-200" data-bs-dismiss="modal" onclick="document.getElementById('default-modal').classList.add('hidden')">
            Cerrar
        </button>

        <a href="#" data-url="{{ route('role.create') }}" data-load="modal" data-title="Crear Nuevo Rol" class="btn-modal btn-primary px-6 py-2 rounded-xl font-semibold transition-all duration-200">
            Nuevo Rol
        </a>
    </div>
</div>
