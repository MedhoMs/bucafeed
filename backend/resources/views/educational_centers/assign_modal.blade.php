<div class="p-6">
    <div class="mb-8">
        <h3 class="text-xl font-bold text-white mb-2">Matricular Alumnos en {{ $center->name }}</h3>
        <p class="text-white/50 text-sm italic">Selecciona los alumnos que deseas vincular a este centro educativo.</p>
    </div>

    <form id="assign-students-form" action="{{ route('educational_centers.assign', $center->id) }}" method="POST" class="space-y-6">
        @csrf

        {{-- Contenedor de errores --}}
        <div id="assign-form-errors" class="hidden mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
            <ul class="text-red-400 text-sm list-disc list-inside"></ul>
        </div>

        <div class="bg-black/20 border border-white/5 rounded-2xl p-4 max-h-[400px] overflow-y-auto custom-scroll">
            @if($students->isEmpty())
                <div class="py-12 text-center">
                    <p class="text-white/20 italic">No hay alumnos disponibles sin centro asignado.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($students as $student)
                        <label class="flex items-center gap-3 p-3 bg-white/5 hover:bg-white/10 rounded-xl border border-white/5 cursor-pointer transition-all group">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="rounded-sm bg-white/10 border-white/20 text-indigo-500 focus:ring-0">
                            <div class="flex flex-col">
                                <span class="text-white font-bold text-sm">{{ $student->name }} {{ $student->last_name }}</span>
                                <span class="text-white/30 text-[10px]">{{ $student->email }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
            <button type="button" class="px-6 py-2.5 bg-white/5 hover:bg-white/10 text-white font-bold rounded-xl transition-all" data-bs-dismiss="modal">
                Cancelar
            </button>
            <button type="button" onclick="submitAssignForm(this)" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition-all">
                Finalizar Matrícula
            </button>
        </div>
    </form>
</div>

<script>
window.submitAssignForm = function(button) {
    const form = document.getElementById('assign-students-form');
    if (!form) return;

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
        const data = await response.json();
        if (!response.ok) throw data;
        return data;
    })
    .then(result => {
        // Recargar el modal de lista de alumnos si existe
        ajaxLoad(`{{ url("admin/educational-centers") }}/{{ $center->id }}`, document.getElementById('modal-body'), true);
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Error al asignar alumnos.');
        button.disabled = false;
        button.innerText = 'Finalizar Matrícula';
    });
}
</script>
