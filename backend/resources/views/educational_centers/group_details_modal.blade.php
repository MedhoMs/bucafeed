<div class="space-y-6">
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <h2 class="text-2xl font-black text-white">{{ $group->name }}</h2>
            <p class="text-indigo-400 font-bold uppercase tracking-widest text-xs">
                {{ $group->cycle ? $group->cycle->name : $center->type }}
            </p>
        </div>
        <div class="text-right">
            <span class="text-white/40 text-[10px] uppercase font-black block">Tutor</span>
            <span class="text-white font-bold">{{ $group->tutor ? $group->tutor->name . ' ' . $group->tutor->last_name : 'No asignado' }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Lista de Alumnos --}}
        <div class="space-y-3">
            <h3 class="text-white font-bold flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-400"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 2v2" /></svg>
                Alumnos Matriculados ({{ $group->students->count() }})
            </h3>
            <div class="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-white/5 text-white/40 font-black uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2">Nombre</th>
                            <th class="px-4 py-2">DNI/NIE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($group->students as $student)
                            <tr>
                                <td class="px-4 py-3 text-white font-medium">{{ $student->name }} {{ $student->last_name }}</td>
                                <td class="px-4 py-3 text-white/60">{{ $student->dni ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                        @if($group->students->isEmpty())
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-white/20 italic">No hay alumnos en este grupo</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Lista de Materias y Profesores --}}
        <div class="space-y-3">
            <h3 class="text-white font-bold flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400"><path d="M3 19l18 0" /><path d="M3 11l18 0" /><path d="M3 15l18 0" /><path d="M3 7l18 0" /></svg>
                Asignaturas e Instrucción
            </h3>
            <div class="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-white/5 text-white/40 font-black uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2">Materia</th>
                            <th class="px-4 py-2">Profesor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($group->subjectsWithTeachers as $subject)
                            <tr>
                                <td class="px-4 py-3 text-white font-medium">{{ $subject->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-emerald-400 font-bold">{{ $subject->pivot->user->name }}</span>
                                </td>
                            </tr>
                        @endforeach
                        @if($group->subjectsWithTeachers->isEmpty())
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-white/20 italic">No se han asignado materias</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="flex justify-end pt-4">
        <button onclick="toggleModal(false)" class="px-6 py-2 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl transition-all cursor-pointer">
            Cerrar Detalles
        </button>
    </div>
</div>
