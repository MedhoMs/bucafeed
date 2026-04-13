<div class="p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white mb-1">Añadir al {{ $center->name }}</h2>
        <p class="text-white/60">Selecciona alumnos y profesores existentes para matricularlos en este centro.</p>
    </div>

    @if(session('success'))
        <div class="p-4 mb-6 bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('educational_centers.store_users', $center->id) }}" method="POST" id="addUsersForm" class="space-y-6">
        @csrf
        
        {{-- Contenedor de errores --}}
        <div id="add-users-errors" class="hidden mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
            <ul class="text-red-400 text-sm list-disc list-inside"></ul>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Selector de Alumnos -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-green-500/20 text-green-400 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">Alumnos</h3>
                </div>
                
                <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2 custom-scroll">
                    @forelse($availableStudents as $student)
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-white/5 bg-black/20 hover:bg-white/5 cursor-pointer transition-colors">
                            <input type="checkbox" name="students[]" value="{{ $student->id }}" class="w-4 h-4 rounded border-white/20 bg-black/50 text-green-500 focus:ring-green-500/50">
                            <div>
                                <p class="text-sm font-medium text-white">{{ $student->name }} {{ $student->last_name }}</p>
                                <p class="text-xs text-white/50">{{ $student->email }} {{ $student->education_level ? '('. $student->education_level .')' : '' }}</p>
                            </div>
                        </label>
                    @empty
                        <p class="text-center text-sm text-white/40 py-4 italic">No hay alumnos libres disponibles.</p>
                    @endforelse
                </div>
            </div>

            <!-- Selector de Profesores -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v3" /><path d="M12 15v.01" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">Docentes</h3>
                </div>
                
                <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2 custom-scroll">
                    @forelse($availableTeachers as $teacher)
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-white/5 bg-black/20 hover:bg-white/5 cursor-pointer transition-colors">
                            <input type="checkbox" name="teachers[]" value="{{ $teacher->id }}" class="w-4 h-4 rounded border-white/20 bg-black/50 text-purple-500 focus:ring-purple-500/50">
                            <div>
                                <p class="text-sm font-medium text-white">{{ $teacher->name }} {{ $teacher->last_name }}</p>
                                <p class="text-xs text-white/50">{{ $teacher->email }} {{ $teacher->education_level ? '('. $teacher->education_level .')' : '' }}</p>
                            </div>
                        </label>
                    @empty
                        <p class="text-center text-sm text-white/40 py-4 italic">No hay docentes libres disponibles.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <button type="button" onclick="submitAddUsers(this)" class="w-full mt-6 flex justify-center items-center py-3.5 px-4 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-500 transition-colors shadow-lg shadow-blue-500/25">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
            Vincular Usuarios Seleccionados
        </button>
    </form>
</div>

<script>
    window.submitAddUsers = function(button) {
        const form = document.getElementById('addUsersForm');
        if (!form) return;
        
        const formData = new FormData(form);
        const modalContent = document.getElementById('modal-content');
        
        button.disabled = true;
        const originalText = button.innerHTML;
        button.innerHTML = 'Guardando...';

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            if (modalContent) { 
                modalContent.innerHTML = html; 
            } else { 
                window.location.reload(); 
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al vincular los usuarios.');
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }
</script>
