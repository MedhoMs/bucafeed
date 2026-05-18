<div class="space-y-8" id="manage-groups-container">
    {{-- Formulario de Creación / Edición --}}
    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 relative overflow-hidden">
        <div id="form-edit-indicator" class="hidden absolute top-0 left-0 w-full h-1 bg-indigo-500 animate-pulse"></div>

        <div class="flex items-center justify-between mb-4">
            <h3 id="form-title" class="text-white font-bold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-400"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 2v2" /></svg>
                Crear Nuevo Grupo / Nivel
            </h3>
            <button id="btn-reset-form" type="button" onclick="resetGroupForm()" class="hidden text-[10px] font-black uppercase tracking-widest text-white/30 hover:text-white transition-colors cursor-pointer">
                Limpiar y Crear Nuevo
            </button>
        </div>

        <form id="add-group-form" action="{{ route('educational_centers.store_group', $center->id) }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" id="editing-group-id" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nombre y Ciclo --}}
                <div class="space-y-4">
                    <x-admin.form.input name="name" id="group-name-input" label="Nombre del Grupo/Nivel" placeholder="Ej: 1º ESO A, 2º DAW..." required />

                    <x-admin.form.select name="cycle_id" id="group-cycle-id" label="Ciclo Asociado (Opcional)" :options="$center->cycles->pluck('name', 'id')->toArray()" placeholder="Seleccionar ciclo si aplica..." />

                    <x-admin.form.select name="tutor_id" id="group-tutor-id" label="Tutor del Grupo" :options="$center->teachers->pluck('name', 'id')->toArray()" placeholder="Seleccionar tutor..." />
                </div>

                {{-- Selección de Alumnos --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-white/70 mb-1">Alumnos del Centro</label>
                    <div id="students-checkbox-list" class="bg-[#1a1c23]/50 border border-white/5 rounded-xl p-3 max-h-45 overflow-y-auto custom-scroll space-y-2">
                        @foreach($center->students as $student)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-white/5 p-1 rounded-sm transition-colors student-checkbox-label">
                                <input type="checkbox" name="students[]" value="{{ $student->id }}" class="rounded-sm bg-white/10 border-white/20 text-indigo-500 student-checkbox">
                                <span class="text-xs text-white/80">{{ $student->name }} {{ $student->last_name }}</span>
                            </label>
                        @endforeach
                        @if($center->students->isEmpty())
                            <p class="text-white/20 text-xs italic p-4 text-center">No hay alumnos matriculados en este centro.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Asignación de Materias y Profesores --}}
            <div class="space-y-3">
                <label class="block text-sm font-medium text-white/70">Asignación de Materias y Profesores</label>
                <div class="border border-white/10 rounded-2xl overflow-hidden bg-white/5">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-white/5 text-white/40 uppercase tracking-wider font-black">
                            <tr>
                                <th class="px-4 py-3">Materia</th>
                                <th class="px-4 py-3">Profesor Asignado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5" id="teachers-assignment-list">
                            {{-- Mostrar sugeridas o todas si no hay --}}
                            @php $displayTags = $suggestedTags->isNotEmpty() ? $suggestedTags : $allTags->take(15); @endphp
                            @foreach($displayTags as $tag)
                                <tr class="hover:bg-white/5 transition-colors tag-row" data-tag-id="{{ $tag->id }}">
                                    <td class="px-4 py-2 text-white font-medium">{{ $tag->name }}</td>
                                    <td class="px-4 py-2">
                                        <select name="teachers[{{ $tag->id }}]" data-tag-id="{{ $tag->id }}" class="teacher-select w-full bg-white/5 border border-white/10 rounded-lg py-1 px-2 text-white/80 focus:outline-hidden focus:ring-1 focus:ring-indigo-500/50 scheme-dark">
                                            <option value="">-- No impartida --</option>
                                            @foreach($center->teachers as $teacher)
                                                <option value="{{ $teacher->id }}">{{ $teacher->name }} {{ $teacher->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <script>
                        (function(){
                            const cycleMap = @json($cycleTagsMap ?? []);
                            const select = document.getElementById('group-cycle-id');

                            function updateSubjects() {
                                if(!select) return;
                                const cid = select.value;
                                const rows = document.querySelectorAll('.tag-row');
                                rows.forEach(r => {
                                    if(!cid) {
                                        r.style.display = 'none'; // Ocultar hasta elegir ciclo
                                        const sel = r.querySelector('.teacher-select');
                                        if (sel) sel.value = '';
                                    } else {
                                        const tid = parseInt(r.getAttribute('data-tag-id'));
                                        if(cycleMap[cid] && cycleMap[cid].includes(tid)) {
                                            r.style.display = '';
                                        } else {
                                            r.style.display = 'none';
                                            const sel = r.querySelector('.teacher-select');
                                            if (sel) sel.value = '';
                                        }
                                    }
                                });
                            }

                            if(select) {
                                select.addEventListener('change', updateSubjects);
                                updateSubjects(); // Ejecutar al inicio para ocultar si no hay ciclo seleccionado por defecto
                            }
                        })();
                    </script>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="button" id="btn-submit-group" onclick="submitGroupForm(this)" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/20 cursor-pointer">
                    Crear Grupo e Iniciar Nivel
                </button>
            </div>
        </form>
    </div>

    {{-- Listado de Grupos Actuales --}}
    <div class="space-y-4">
        <h3 class="text-white font-bold flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /><path d="M12 7v5l2 2" /></svg>
            Grupos Configurados ({{ $center->groups->count() }})
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($center->groups as $group)
                <div class="p-4 bg-white/5 border border-white/10 rounded-2xl relative group hover:border-indigo-500/30 transition-all">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="text-white font-black text-lg">{{ $group->name }}</h4>
                            <p class="text-[10px] text-indigo-300 font-bold uppercase tracking-widest mt-1">
                                {{ $group->cycle ? $group->cycle->name : (\App\Models\EducationalCenter::$niveles_disponibles[$center->type] ?? $center->type) }}
                            </p>
                        </div>
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="ajaxLoad('{{ route('educational_centers.group_details', [$center->id, $group->id]) }}', document.getElementById('modal-body'), true)" class="p-2 text-white/20 hover:text-emerald-400 hover:bg-emerald-400/10 rounded-xl transition-all cursor-pointer" title="Consultar Detalles">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                            </button>
                            <button onclick="editGroup('{{ $group->id }}', this)" class="p-2 text-white/20 hover:text-blue-400 hover:bg-blue-400/10 rounded-xl transition-all cursor-pointer" title="Editar Grupo">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                            </button>
                            <button onclick="deleteGroup('{{ $group->id }}', this)" class="p-2 text-white/20 hover:text-red-400 hover:bg-red-400/10 rounded-xl transition-all cursor-pointer" title="Eliminar Grupo">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-[10px] mt-4 pt-4 border-t border-white/5">
                        <div class="space-y-1">
                            <span class="text-white/40 font-bold uppercase block">Tutor</span>
                            <span class="text-white font-medium">{{ $group->tutor ? $group->tutor->name : 'N/A' }}</span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-white/40 font-bold uppercase block">Alumnos</span>
                            <span class="text-white font-medium">{{ $group->students->count() }} matriculados</span>
                        </div>
                        <div class="space-y-1 col-span-2">
                            <span class="text-white/40 font-bold uppercase block">Asignaturas</span>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($group->subjectsWithTeachers as $subject)
                                    <span class="px-1.5 py-0.5 bg-white/5 rounded-sm text-white/60 border border-white/5">{{ $subject->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        window.resetGroupForm = function() {
            const form = document.getElementById('add-group-form');
            const editingId = document.getElementById('editing-group-id');
            const formTitle = document.getElementById('form-title');
            const submitBtn = document.getElementById('btn-submit-group');
            const resetBtn = document.getElementById('btn-reset-form');
            const indicator = document.getElementById('form-edit-indicator');

            form.reset();
            form.action = "{{ route('educational_centers.store_group', $center->id) }}";
            editingId.value = '';
            formTitle.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-400"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 2v2" /></svg> Crear Nuevo Grupo / Nivel`;
            submitBtn.innerText = 'Crear Grupo e Iniciar Nivel';
            resetBtn.classList.add('hidden');
            indicator.classList.add('hidden');

            // Limpiar selección de alumnos
            document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
            // Limpiar selección de profesores
            document.querySelectorAll('.teacher-select').forEach(sel => sel.value = '');
        }

        window.submitGroupForm = function(button) {
            const form = document.getElementById('add-group-form');
            const container = document.getElementById('manage-groups-container').parentNode;
            if (!form || !container) return;

            const formData = new FormData(form);
            button.disabled = true;
            button.innerHTML = '<span class="animate-pulse">Procesando...</span>';

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                if (!response.ok) {
                    const data = await response.json();
                    throw data;
                }
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch(e) {
                    return text; // It's the HTML partial
                }
            })
            .then(result => {
                if (typeof result === 'string') {
                    container.innerHTML = result;
                } else {
                    // Si el servidor devolvió JSON (exito), recargamos la vista de gestión
                    ajaxLoad(`{{ url("admin/educational-centers") }}/{{ $center->id }}/manage-groups`, container, false);
                }
            })
            .catch(error => {
                console.error('Error:', error);

                const errorContainer = document.getElementById('group-form-errors');
                const errorList = errorContainer.querySelector('ul');
                errorList.innerHTML = '';

                let errors = [];
                if (error.errors) {
                    errors = Object.values(error.errors).flat();
                } else if (error.message) {
                    errors = [error.message];
                } else {
                    errors = ['Error desconocido al procesar la solicitud.'];
                }

                errors.forEach(msg => {
                    const li = document.createElement('li');
                    li.innerText = msg;
                    errorList.appendChild(li);
                });

                errorContainer.classList.remove('hidden');
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });

                button.disabled = false;
                button.innerText = 'Crear Grupo e Iniciar Nivel';
            });
        }

        window.editGroup = function(groupId, button) {
            const form = document.getElementById('add-group-form');
            const editingId = document.getElementById('editing-group-id');
            const formTitle = document.getElementById('form-title');
            const submitBtn = document.getElementById('btn-submit-group');
            const resetBtn = document.getElementById('btn-reset-form');
            const indicator = document.getElementById('form-edit-indicator');

            button.disabled = true;

            fetch(`{{ url("admin/educational-centers") }}/{{ $center->id }}/edit-group/${groupId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(group => {
                resetGroupForm();

                form.action = `{{ url("admin/educational-centers") }}/{{ $center->id }}/update-group/${groupId}`;
                editingId.value = group.id;
                formTitle.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-400"><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg> Editando Grupo: ${group.name}`;
                submitBtn.innerText = 'Actualizar Grupo';
                resetBtn.classList.remove('hidden');
                indicator.classList.remove('hidden');

                // Llenar campos
                document.getElementById('group-name-input').value = group.name;
                document.getElementById('group-cycle-id').value = group.cycle_id || '';
                document.getElementById('group-tutor-id').value = group.tutor_id || '';

                // Marcar alumnos
                if (group.students) {
                    group.students.forEach(student => {
                        const cb = document.querySelector(`.student-checkbox[value="${student.id}"]`);
                        if (cb) cb.checked = true;
                    });
                }

                // Asignar profesores a materias
                if (group.subjects_with_teachers) {
                    group.subjects_with_teachers.forEach(subject => {
                        const sel = document.querySelector(`.teacher-select[data-tag-id="${subject.id}"]`);
                        if (sel) sel.value = subject.pivot.user_id;
                    });
                }

                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                button.disabled = false;
            })
            .catch(err => {
                console.error(err);
                alert('Error al cargar datos del grupo.');
                button.disabled = false;
            });
        }

        window.deleteGroup = function(groupId, button) {
            // Sin confirmación por petición del usuario
            const container = document.getElementById('modal-body');
            button.disabled = true;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');

            fetch(`{{ url("admin/educational-centers") }}/{{ $center->id }}/delete-group/${groupId}`, {
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
                alert('Error al eliminar el grupo.');
                button.disabled = false;
            });
        }
    </script>
</div>
