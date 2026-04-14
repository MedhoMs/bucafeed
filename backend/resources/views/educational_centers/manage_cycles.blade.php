<div class="p-6 text-white" id="cycles-management-container">
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-white/10">
        <div>
            <h2 class="text-2xl font-black tracking-tight">Gestionar Ciclos</h2>
            <p class="text-sm text-white/40 italic">{{ $center->name }}</p>
        </div>
        <div class="px-4 py-1.5 bg-blue-500/10 text-blue-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-blue-500/20">
            {{ $center->cycles->count() }} Asignados
        </div>
    </div>

    <!-- Formulario para Vincular desde el Registro -->
    <div class="mb-8 p-5 bg-white/5 border border-white/10 rounded-2xl">
        <form action="{{ route('educational_centers.add_cycle', $center->id) }}" method="POST" class="flex flex-col gap-4" id="add-center-cycle-form">
            @csrf
            
            <div>
                <label class="block text-[10px] font-black text-white/40 uppercase tracking-widest mb-2 px-1 text-left">Vincular del Registro Global</label>
                <div class="flex gap-3">
                    <select name="cycle_id" class="flex-1 bg-[#0a141d] border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-blue-500/50 outline-hidden">
                        <option value="">-- Seleccionar Ciclo Existente --</option>
                        @foreach($globalCycles as $gc)
                            @if(!$center->cycles->contains($gc->id))
                                <option value="{{ $gc->id }}">{{ $gc->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <button 
                        type="button" 
                        onclick="submitCenterCycleForm(this)"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-sm font-bold transition-all active:scale-95"
                    >
                        Vincular
                    </button>
                </div>
            </div>

            <div class="relative py-2 flex items-center justify-center">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-white/5"></div></div>
                <span class="relative bg-[#0d1b26] px-2 text-[10px] font-black text-white/20 uppercase tracking-[0.2em]">O crear nuevo</span>
            </div>

            <div class="flex gap-3">
                <input 
                    type="text" 
                    name="new_cycle" 
                    placeholder="Escribe el nombre del ciclo si no está en la lista" 
                    class="flex-1 bg-[#0a141d] border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-blue-500/50 outline-hidden"
                >
                <button 
                    type="button" 
                    onclick="submitCenterCycleForm(this)"
                    class="px-6 py-2.5 border border-white/10 hover:bg-white/5 text-white rounded-xl text-sm font-bold transition-all active:scale-95"
                >
                    Registrar y Vincular
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Ciclos Asignados -->
    <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2 custom-scroll">
        @forelse($center->cycles as $cycle)
            <div class="group flex items-center justify-between p-4 bg-white/5 border border-transparent hover:border-white/10 hover:bg-white/[0.07] rounded-2xl transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center border border-white/10 text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9l-10 -4l-10 4l10 4l10 -4v6" /><path d="M6 10.6v5.4a6 6 0 0 0 12 0v-5.4" /></svg>
                    </div>
                    <span class="font-bold text-sm tracking-wide">{{ $cycle->name }}</span>
                </div>
                <button 
                    onclick="removeCenterCycle('{{ $cycle->id }}', this)"
                    class="p-2 text-white/20 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-all opacity-0 group-hover:opacity-100"
                    title="Desvincular Ciclo"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                </button>
            </div>
        @empty
            <div class="py-12 text-center">
                <p class="text-white/30 text-sm italic">Este centro no tiene ciclos asignados.</p>
            </div>
        @endforelse
    </div>
</div>

<script>
    window.submitCenterCycleForm = function(button) {
        const form = document.getElementById('add-center-cycle-form');
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
            alert('Error al añadir el ciclo.');
            button.disabled = false;
        });
    }

    window.removeCenterCycle = function(id, button) {
        const container = document.getElementById('modal-body');
        // Eliminado el confirm por petición del usuario
        
        button.disabled = true;
        const formData = new FormData();
        formData.append('cycle_id', id);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("educational_centers.remove_cycle", $center->id) }}', {
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
            alert('Error al desvincular el ciclo.');
            button.disabled = false;
        });
    }
</script>
