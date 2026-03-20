<x-admin.crud-form 
    :action="$oper == 'create' ? route('users_events.create.post') : ($oper == 'destroy' ? route('users_events.destroy.post', $event->id ?? 0) : route('users_events.edit.post', $event->id ?? 0))"
    :oper="$oper"
    :modelId="$event->id ?? ''"
    :datos="$datos ?? []"
    :disabled="$disabled"
    enctype="multipart/form-data"
    deleteText="Eliminar evento"
>

        @php
            $schoolOptions = [];
            foreach($schools as $schoolDb) {
                $schoolOptions[$schoolDb->id] = $schoolDb->name;
            }

            $roleOptions = [];
            foreach($roles as $rolDb) {
                $roleValue = $rolDb->code ?? $rolDb->name;
                $roleOptions[$roleValue] = 'Solo ' . ($roles_disponibles[$rolDb->code] ?? $rolDb->name);
            }
        @endphp

        <x-admin.form.builder :disabled="$disabled" :rows="[
            [
                ['name' => 'title', 'label' => 'Nombre del Evento', 'value' => old('title', $event->title ?? ''), 'placeholder' => 'Ej: Jornada...'],
                ['component' => 'select', 'name' => 'educational_center_id', 'label' => 'Centro Educativo Organizador', 'options' => $schoolOptions, 'selectedValue' => old('educational_center_id', $event->educational_center_id ?? ''), 'placeholder' => 'Seleccionar centro...']
            ],
            [
                ['component' => 'textarea', 'name' => 'description', 'label' => 'Descripción', 'value' => old('description', $event->description ?? ''), 'placeholder' => 'Detalles sobre el evento...', 'rows' => 3]
            ],
            [
                ['type' => 'date', 'name' => 'date', 'label' => 'Fecha', 'value' => old('date', $event->date ?? '')],
                ['type' => 'time', 'name' => 'start_time', 'label' => 'Hora Inicio', 'value' => old('start_time', $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i') : '')],
                ['type' => 'time', 'name' => 'end_time', 'label' => 'Hora Fin', 'value' => old('end_time', $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('H:i') : '')]
            ],
            [
                ['name' => 'location', 'label' => 'Lugar Exacto (Dirección o Aula)', 'value' => old('location', $event->location ?? ''), 'placeholder' => 'Aula 104, Edificio A...'],
                ['component' => 'select', 'name' => 'target_role', 'label' => 'Dirigido Especificamente A (Opcional)', 'options' => $roleOptions, 'selectedValue' => old('target_role', $event->target_role ?? ''), 'placeholder' => 'Todos los roles pueden unirse']
            ]
        ]" />

        <p class="text-white/40 text-xs mt-1 mb-6">Si dejas el target_role en "Todos", cualquier usuario podrá inscribirse.</p>

        {{-- Custom Field: Image --}}
        <div class="mt-6">
            <label for="idimage" class="block text-sm font-medium text-white/70 mb-1">Imagen de Portada</label>
            <div class="flex items-center gap-4">
                @if($event->image_url)
                    <img src="{{ $event->image_url }}" class="w-12 h-12 rounded bg-black/50 object-cover border border-white/10">
                @endif
                <input {{ $disabled }} type="file" name="image" accept="image/*"
                    class="w-full text-sm text-white/70 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-500/20 file:text-blue-400 hover:file:bg-blue-500/30 transition-all duration-200" 
                    id="idimage">
            </div>
            @error('image')
                <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- List of Participants View (Only Shows if Editing/Showing) --}}
        @if(isset($event->id) && $oper != 'create' && $oper != 'destroy')
        <div class="pt-4 mt-6 border-t border-white/10">
            <h3 class="text-white font-semibold text-lg mb-3">Participantes Inscritos ({{ $event->participants->count() }})</h3>
            
            @if($event->participants->count() > 0)
                <div class="max-h-60 overflow-y-auto pr-2 space-y-2 custom-scrollbar">
                    @foreach($event->participants as $participant)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white font-bold text-xs shadow-md">
                                    {{ substr($participant->name, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-white text-sm font-medium">{{ $participant->name }} {{ $participant->last_name }}</span>
                                    <span class="text-white/40 text-xs">{{ $participant->email }}</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium tracking-wide bg-purple-500/10 text-purple-300 border border-purple-500/20">
                                {{ $roles_disponibles[$participant->role] ?? $participant->role }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 rounded-xl bg-white/5 border border-white/5 text-center flex flex-col items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                    <p class="text-white/40 text-sm">Todavía no hay participantes inscritos a este evento.</p>
                </div>
            @endif
        </div>
        @endif

</x-admin.crud-form>
