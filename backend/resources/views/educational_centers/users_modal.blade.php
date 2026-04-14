<div class="p-6 text-white">
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-white/10">
        <div>
            <h2 class="text-2xl font-black tracking-tight">{{ $title }}</h2>
            <p class="text-xs text-white/40 mt-1 uppercase tracking-widest font-bold">{{ $role === 'Student' ? 'Listado de Estudiantes Matriculados' : 'Cuerpo Docente del Centro' }}</p>
        </div>
        <div class="px-4 py-1.5 {{ $role === 'Student' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-purple-500/10 text-purple-400 border-purple-500/20' }} rounded-full text-[10px] font-black uppercase tracking-widest border">
            {{ $users->count() }} Registros
        </div>
    </div>

    <div class="space-y-3 max-h-[450px] overflow-y-auto pr-2 custom-scroll">
        @forelse($users as $user)
            <div class="group flex items-center justify-between p-4 bg-white/3 border border-white/5 hover:border-white/20 hover:bg-white/[0.07] rounded-2xl transition-all duration-300">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/10 group-hover:border-white/30 transition-all overflow-hidden">
                        @if($user->profile_picture)
                            <img src="{{ $user->profile_picture }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-white/40 font-black text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div>
                        <p class="font-bold text-sm tracking-wide text-white">{{ $user->name }} {{ $user->last_name }}</p>
                        <p class="text-[10px] text-white/30 font-medium mt-0.5">{{ $user->email }}</p>
                    </div>
                </div>
                <a 
                    href="#" 
                    data-url="{{ route('user.show', $user->id) }}"
                    data-load="modal"
                    data-title="Perfil de Usuario"
                    class="btn-modal p-2 text-white/10 hover:text-blue-400 hover:bg-blue-400/10 rounded-xl transition-all opacity-0 group-hover:opacity-100"
                    title="Ver Perfil"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
                </a>
            </div>
        @empty
            <div class="py-16 text-center">
                <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-5 border border-white/5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="text-white/10" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                </div>
                <p class="text-white/20 text-sm font-medium">No se encontraron usuarios.</p>
            </div>
        @endforelse
    </div>
</div>
