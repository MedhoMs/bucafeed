<div class="container mx-auto pt-4 sticky top-0">
    @if($errors->any())
        <ul class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
            @foreach ($errors->all() as $error)
                <li class="text-red-400 text-sm list-disc list-inside">{{ $error }}</li>
            @endforeach
        </ul>
    @endif
    
    @if($datos['exito'])
        <p class="mb-4 bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg"> {{ $datos['exito'] }} </p>
    @endif

    <form id="formGeneral"
    data-oper="{{ $oper }}"
    action="@if($oper == 'create'){{ route('users_events.create.post') }}@elseif($oper == 'destroy'){{ route('users_events.destroy.post', $event->id ?? 0) }}@else{{ route('users_events.edit.post', $event->id ?? 0) }}@endif"   
    method="POST"
    enctype="multipart/form-data"
    class="space-y-6"
    >
        @csrf

        <input name="id" type="hidden" value="{{ $event->id ?? '' }}" />

        {{-- First Row: Title, School --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Título -->
            <div>
                <label for="idtitle" class="block text-sm font-medium text-white/70 mb-1">Nombre del Evento</label>
                <input {{ $disabled }} value="{{ old('title', $event->title ?? '') }}" type="text" name="title" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                    id="idtitle" placeholder="Ej: Jornada de Puertas Abiertas">
                @error('title')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Centro Educativo -->
            <div>
                <label for="idschool" class="block text-sm font-medium text-white/70 mb-1">Centro Educativo Organizador</label>
                <select {{ $disabled }} name="educational_center_id" id="idschool" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200">
                    <option value="" class="bg-[#1a202c]">Seleccionar centro...</option>
                    @foreach($schools as $schoolDb)
                        <option value="{{ $schoolDb->id }}" {{ (old('educational_center_id', $event->educational_center_id ?? '') == $schoolDb->id) ? 'selected' : '' }} class="bg-[#1a202c]">
                            {{ $schoolDb->name }}
                        </option>
                    @endforeach
                </select>
                @error('educational_center_id')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Second Row: Description --}}
        <div>
            <label for="iddescription" class="block text-sm font-medium text-white/70 mb-1">Descripción</label>
            <textarea {{ $disabled }} name="description" id="iddescription" rows="3"
                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                placeholder="Detalles sobre el evento...">{{ old('description', $event->description ?? '') }}</textarea>
            @error('description')
                <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- Third Row: Date & Times --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Fecha -->
            <div>
                <label for="iddate" class="block text-sm font-medium text-white/70 mb-1">Fecha</label>
                <input {{ $disabled }} value="{{ old('date', $event->date ?? '') }}" type="date" name="date" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 [color-scheme:dark]" 
                    id="iddate">
                @error('date')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            
            <!-- Hora Inicio -->
            <div>
                <label for="idstart" class="block text-sm font-medium text-white/70 mb-1">Hora Inicio</label>
                <input {{ $disabled }} value="{{ old('start_time', $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i') : '') }}" type="time" name="start_time" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 [color-scheme:dark]" 
                    id="idstart">
                @error('start_time')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Hora Fin -->
            <div>
                <label for="idend" class="block text-sm font-medium text-white/70 mb-1">Hora Fin</label>
                <input {{ $disabled }} value="{{ old('end_time', $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('H:i') : '') }}" type="time" name="end_time" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 [color-scheme:dark]" 
                    id="idend">
                @error('end_time')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Fourth Row: Location & Image --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
             <!-- Location -->
             <div>
                <label for="idlocation" class="block text-sm font-medium text-white/70 mb-1">Lugar Exacto (Dirección o Aula)</label>
                <input {{ $disabled }} value="{{ old('location', $event->location ?? '') }}" type="text" name="location" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                    id="idlocation" placeholder="Aula 104, Edificio A...">
                @error('location')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Image -->
            <div>
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
        </div>

        {{-- Fifth Row: Target Role --}}
        <div>
            <label for="idtarget_role" class="block text-sm font-medium text-white/70 mb-1">Dirigido Especificamente A (Opcional)</label>
            <select {{ $disabled }} name="target_role" id="idtarget_role" 
                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200">
                <option value="" class="bg-[#1a202c]">Todos los roles pueden unirse</option>
                @foreach($roles as $rolDb)
                    @php $roleValue = $rolDb->code ?? $rolDb->name; @endphp
                    <option value="{{ $roleValue }}" {{ (strtolower(old('target_role', $event->target_role ?? '')) == strtolower($roleValue)) ? 'selected' : '' }} class="bg-[#1a202c]">
                        Solo {{ $roles_disponibles[$rolDb->code] ?? $rolDb->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-white/40 text-xs mt-1">Si dejas esto en "Todos", cualquier usuario de la aplicación podrá ver e inscribirse a este evento.</p>
            @error('target_role')
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
                                {{ collect($roles_disponibles)->mapWithKeys(fn($item, $key) => [strtolower($key) => $item])->get(strtolower($participant->role), ucfirst($participant->role)) }}
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

        <div class="flex items-center justify-end gap-3 mt-8">
            @if (!$disabled || $oper == 'edit')
                <button type="submit" class="px-6 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-semibold shadow-lg shadow-cyan-500/20 transition-all duration-200">
                    {{ $oper == 'edit' ? 'Actualizar Evento' : 'Crear Evento' }}
                </button>
            @endif

            @if ($oper == 'destroy' && empty($datos['exito']))
                <button type="submit" class="px-6 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 transition-all duration-200">
                    Confirmar Eliminación
                </button>
            @endif
        </div>
    </form>
</div>
