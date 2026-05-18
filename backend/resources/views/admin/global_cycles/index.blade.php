<div class="p-6 text-white" id="global-cycles-container">
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-white/10">
        <div>
            <h2 class="text-2xl font-black tracking-tight text-transparent bg-clip-text bg-linear-to-r from-blue-400 to-purple-400">Registro Global de Ciclos</h2>
            <p class="text-xs text-white/40 mt-1 uppercase tracking-widest font-bold">Base de datos de módulos compartidos</p>
        </div>
        <div class="px-4 py-1.5 bg-purple-500/10 text-purple-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-purple-500/20">
            {{ count($cycles) }} Registrados
        </div>
    </div>

    {{-- Buscador y Filtros --}}
    <div class="mb-6 px-1 flex flex-col md:flex-row gap-4 items-center">
        <form action="{{ url()->current() }}" method="GET" class="relative group flex-1" id="search-global-cycles-form" data-load="section">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-white/20 group-focus-within:text-blue-400 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
            </div>
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Buscar por nombre o nivel..."
                class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 pl-11 pr-4 text-sm text-white placeholder:text-white/20 focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all border-dashed"
            >
        </form>
        @if(!empty($levels))
            <x-admin.filter-dropdown
                label="Nivel"
                name="level"
                :options="$levels"
                :selected="request('level')"
            />
        @endif
    </div>

    <!-- Lista del Registro -->
    <div class="space-y-3 max-h-87.5 overflow-y-auto pr-2 custom-scroll">
        @forelse($cycles as $cycle)
            <div class="group flex items-center justify-between p-4 bg-white/3 border border-white/5 cursor-pointer hover:border-white/20 hover:bg-white/[0.07] rounded-2xl transition-all duration-300">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-linear-to-br from-blue-500/10 to-purple-500/10 flex items-center justify-center border border-white/10 group-hover:border-blue-500/30 transition-all">
                        <span class="text-[10px] font-black text-white/60 group-hover:text-blue-400">{{ $cycle->level ?? '?' }}</span>
                    </div>
                    <div>
                        <p class="font-bold text-sm tracking-wide text-white group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-linear-to-r group-hover:from-white group-hover:to-blue-400">{{ $cycle->name }}</p>
                        @if($cycle->area)
                            <p class="text-[10px] text-white/30 font-medium italic mt-0.5">{{ $cycle->area }}</p>
                        @endif
                    </div>
                </div>
                <button
                    onclick="removeGlobalCycle('{{ $cycle->id }}', this)"
                    class="p-2.5 text-white/10 hover:text-red-400 cursor-pointer hover:bg-red-400/10 rounded-xl transition-all opacity-0 group-hover:opacity-100"
                    title="Eliminar del Registro"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                </button>
            </div>
        @empty
            <div class="py-16 text-center">
                <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-5 border border-white/5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="text-white/10" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5v9l-8 4.5l-8 -4.5v-9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /><path d="M16 5.25l-8 4.5" /></svg>
                </div>
                <p class="text-white/20 text-sm font-medium">El registro está vacío.</p>
                <p class="text-[10px] text-white/10 italic mt-1 uppercase tracking-widest">Añade el primer ciclo oficial arriba</p>
            </div>
        @endforelse
    </div>

</div>

<script>
    window.submitGlobalCycleForm = function(button) {
        const form = document.getElementById('add-global-cycle-form');
        const container = document.getElementById('modal-body');
        if (!form || !container) return;

        const formData = new FormData(form);
        button.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            alert('Error al registrar el ciclo.');
            button.disabled = false;
        });
    }

    window.removeGlobalCycle = function(id, button) {
        const container = document.getElementById('modal-body');
        if (!confirm('¿Seguro que deseas eliminar este ciclo del registro GLOBAL? Esto NO afectará a los centros que ya lo tengan asignado pero ya no podrán seleccionarlo nuevos centros.')) return;

        button.disabled = true;
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ url("admin/global-cycles/delete") }}/' + id, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            alert('Error al eliminar del registro.');
            button.disabled = false;
        });
    }
</script>
