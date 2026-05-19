<x-admin.crud-form
    :oper="$oper"
    :action="$oper == 'destroy' ? route('question.destroy.post', $question->id ?? 0) : route('question.store')"
    :modelId="$question->id ?? ''"
    :title="'Crear Nueva Pregunta académica'"
    :description="'Completa los datos para publicar una nueva duda en el foro académico.'"
    :datos="$datos"
    :disabled="$disabled"
    enctype="multipart/form-data"
>
    <x-admin.form-template :disabled="$disabled" :fields="$fields" />

    @if($oper === 'show' && isset($question) && $question->answers->count() > 0)
        <div class="mt-8 border-t border-white/10 pt-6">
            <h3 class="text-white text-lg font-bold mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M21 14l-3 -3h-7a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1h9a1 1 0 0 1 1 1v10" /><path d="M14 15v2a1 1 0 0 1 -1 1h-7l-3 3v-10a1 1 0 0 1 1 -1h2" /></svg>
                Respuestas a esta pregunta ({{ $question->answers->count() }})
            </h3>
            <div class="flex flex-col gap-4 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                @foreach($question->answers as $ans)
                    <div class="bg-black/20 border border-white/5 rounded-xl p-4 flex gap-4 items-start hover:bg-black/30 transition-colors">
                        <x-admin.user-avatar :user="$ans->user" size="w-10 h-10" :showName="false" class="shrink-0" />
                        <div class="min-w-0 flex-1">
                            <div class="flex justify-between items-center mb-1">
                                <p class="text-white text-sm font-bold">{{ $ans->user->name }} {{ $ans->user->last_name }}</p>
                                <span class="text-xs text-white/40">{{ $ans->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-white/80 text-sm mt-1 whitespace-pre-wrap leading-relaxed">{{ $ans->content }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif($oper === 'show')
        <div class="mt-8 border-t border-white/10 pt-6">
             <p class="text-white/40 italic flex items-center gap-2 text-sm justify-center py-4 bg-white/5 rounded-xl border border-white/5">
                 <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><line x1="9" y1="12" x2="15" y2="12" /></svg>
                 Esta pregunta todavía no tiene respuestas
             </p>
        </div>
    @endif

    {{-- Script para filtrar materias dinámicamente --}}
    <script>
        (function() {
            const userSelect = document.querySelector('select[name="user_id"]');
            const tagsContainer = document.getElementById('checkbox-container-tags');

            if (userSelect && tagsContainer) {
                userSelect.addEventListener('change', function() {
                    const userId = this.value;
                    if (!userId) return;

                    // Mostrar estado de carga suave
                    tagsContainer.style.opacity = '0.5';
                    tagsContainer.style.pointerEvents = 'none';

                    fetch(`/admin/questions/tags-by-user/${userId}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(tags => {
                        tagsContainer.innerHTML = '';
                        tagsContainer.style.opacity = '1';
                        tagsContainer.style.pointerEvents = 'auto';

                        const tagIds = Object.keys(tags);
                        
                        if (tagIds.length === 0) {
                            tagsContainer.innerHTML = '<p class="text-white/20 text-xs italic col-span-full py-4 text-center">Este alumno no tiene materias asociadas a su curso.</p>';
                            return;
                        }

                        tagIds.forEach(id => {
                            const name = tags[id];
                            const label = document.createElement('label');
                            label.className = 'flex items-center gap-3 cursor-pointer group p-1 select-none';
                            label.innerHTML = `
                                <div class="relative flex items-center shrink-0">
                                    <input type="checkbox" name="tags[]" value="${id}" class="peer w-5 h-5 opacity-0 absolute cursor-pointer">
                                    <div class="w-5 h-5 border-2 border-white/10 rounded-lg bg-white/5 peer-checked:bg-indigo-500 peer-checked:border-indigo-500 transition-all flex items-center justify-center shadow-inner">
                                        <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <span class="text-sm text-white/60 group-hover:text-white truncate transition-colors">${name}</span>
                            `;
                            tagsContainer.appendChild(label);
                        });
                    })
                    .catch(err => {
                        console.error('Error fetching tags:', err);
                        tagsContainer.style.opacity = '1';
                        tagsContainer.style.pointerEvents = 'auto';
                    });
                });
            }
        })();
    </script>
</x-admin.crud-form>
