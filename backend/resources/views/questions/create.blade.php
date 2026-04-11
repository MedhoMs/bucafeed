<x-admin.crud-form
    :oper="$oper"
    :action="route('question.store')"
    :title="'Crear Nueva Pregunta académica'"
    :description="'Completa los datos para publicar una nueva duda en el foro académico.'"
    :datos="$datos"
    :disabled="$disabled"
    enctype="multipart/form-data"
>
    <x-admin.form-template :disabled="$disabled" :fields="$fields" />

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
