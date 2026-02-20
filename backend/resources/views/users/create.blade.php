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
    action="@if($oper == 'create'){{ route('user.create') }}@elseif($oper == 'destroy'){{ route('user.destroy', $user->id ?? 0) }}@else{{ route('user.edit.post', $user->id ?? 0) }}@endif"   
    method="POST"
    class="space-y-6"
    >
        @csrf

        <input name="id" type="hidden" value="{{ $user->id ?? '' }}" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nombre -->
            <div>
                <label for="idname" class="block text-sm font-medium text-white/70 mb-1">Nombre</label>
                <input {{ $disabled }} value="{{ old('name', $user->name ?? '') }}" type="text" name="name" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                    id="idname" placeholder="Ej: Juan">
                @error('name')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Apellidos -->
            <div>
                <label for="idlast_name" class="block text-sm font-medium text-white/70 mb-1">Apellidos</label>
                <input {{ $disabled }} value="{{ old('last_name', $user->last_name ?? '') }}" type="text" name="last_name" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                    id="idlast_name" placeholder="Ej: Pérez García">
                @error('last_name')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Email -->
            <div>
                <label for="idemail" class="block text-sm font-medium text-white/70 mb-1">Email</label>
                <input {{ $disabled }} value="{{ old('email', $user->email ?? '') }}" type="email" name="email" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                    id="idemail" placeholder="juan@ejemplo.com">
                @error('email')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            
            <!-- Contraseña -->
            <div>
                <label for="idpassword" class="block text-sm font-medium text-white/70 mb-1">Contraseña</label>
                <input {{ $disabled }} type="password" name="password" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                    id="idpassword" placeholder="********">
                @error('password')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- DNI/NIE -->
            <div>
                <label for="iddni" class="block text-sm font-medium text-white/70 mb-1">DNI/NIE</label>
                <input {{ $disabled }} value="{{ old('dni', $user->dni ?? '') }}" type="text" name="dni" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                    id="iddni" placeholder="12345678A">
                @error('dni')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Rol -->
            <div>
                <label for="idrole" class="block text-sm font-medium text-white/70 mb-1">Rol</label>
                <select {{ $disabled }} name="role" id="idrole" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200">
                    <option value="" class="bg-[#1a202c]">Seleccionar rol...</option>
                    @foreach($roles as $rolDb)
                        @php $roleValue = $rolDb->code ?? $rolDb->name; @endphp
                        <option value="{{ $roleValue }}" {{ (strtolower(old('role', $user->role ?? '')) == strtolower($roleValue)) ? 'selected' : '' }} class="bg-[#1a202c]">
                            {{ $roles_disponibles[$rolDb->code] ?? $rolDb->name }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
             <!-- Nivel Educativo -->
             <div>
                <label for="ideducation_level" class="block text-sm font-medium text-white/70 mb-1">Nivel Educativo</label>
                <select {{ $disabled }} name="education_level" id="ideducation_level" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200">
                    <option value="" class="bg-[#1a202c]">Seleccionar...</option>
                    @foreach ($education_levels as $key => $label)
                        <option value="{{ $key }}" {{ (old('education_level', $user->education_level ?? '') == $key) ? 'selected' : '' }} class="bg-[#1a202c]">
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('education_level')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Nombre Institución -->
            <div>
                <label for="idinstitution_name" class="block text-sm font-medium text-white/70 mb-1">Institución</label>
                <input {{ $disabled }} value="{{ old('institution_name', $user->institution_name ?? '') }}" type="text" name="institution_name" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                    id="idinstitution_name" placeholder="Nombre del centro">
                @error('institution_name')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>


        <div class="flex items-center justify-end gap-3 mt-8">
            @if (!$disabled || $oper == 'edit')
                <button type="submit" class="px-6 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-semibold shadow-lg shadow-cyan-500/20 transition-all duration-200">
                    {{ $oper == 'edit' ? 'Actualizar Usuario' : 'Guardar Usuario' }}
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

