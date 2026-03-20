<x-admin.crud-form 
    :action="$oper == 'create' ? route('user.create') : ($oper == 'destroy' ? route('user.destroy', $user->id ?? 0) : route('user.edit.post', $user->id ?? 0))"
    :oper="$oper"
    :modelId="$user->id ?? ''"
    :datos="$datos ?? []"
    :disabled="$disabled"
    enctype="multipart/form-data"
    deleteText="Eliminar usuario"
>

        <!-- Campos de imagen y banner -->
        @if($oper !== 'create')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-white/5 rounded-xl border border-white/10">
            <div>
                <label for="idprofile_picture" class="block text-sm font-medium text-white/70 mb-1">Foto de Perfil</label>
                <input {{ $disabled }} type="file" name="profile_picture" id="idprofile_picture" 
                    class="w-full text-sm text-white/50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-500/10 file:text-blue-400 hover:file:bg-blue-500/20 transition-all cursor-pointer">
                @if($user->profile_picture)
                    <p class="text-xs text-blue-400 mt-1">Tiene una imagen configurada</p>
                @endif
            </div>
            <div>
                <label for="idbanner" class="block text-sm font-medium text-white/70 mb-1">Banner</label>
                <input {{ $disabled }} type="file" name="banner" id="idbanner" 
                    class="w-full text-sm text-white/50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-500/10 file:text-purple-400 hover:file:bg-purple-500/20 transition-all cursor-pointer">
                @if($user->banner)
                    <p class="text-xs text-purple-400 mt-1">Tiene un banner configurado</p>
                @endif
            </div>
        </div>
        @endif

        @php
            $roleOptions = [];
            foreach($roles as $rolDb) {
                $roleOptions[$rolDb->code ?? $rolDb->name] = $roles_disponibles[$rolDb->code] ?? $rolDb->name;
            }
            
            $educationOptions = [];
            foreach($education_levels as $key => $label) {
                $educationOptions[$key] = $label;
            }
        @endphp

        <x-admin.form.builder :disabled="$disabled" :rows="[
            [
                ['name' => 'name',      'label' => 'Nombre',    'placeholder' => 'Ej: Juan',         'value' => old('name', $user->name ?? '')],
                ['name' => 'last_name', 'label' => 'Apellidos', 'placeholder' => 'Ej: Pérez García', 'value' => old('last_name', $user->last_name ?? '')]
            ],
            [
                ['name' => 'email',    'type' => 'email',    'label' => 'Email',      'placeholder' => 'juan@ejemplo.com', 'value' => old('email', $user->email ?? '')],
                ['name' => 'password', 'type' => 'password', 'label' => 'Contraseña', 'placeholder' => '********']
            ],
            [
                ['name' => 'dni', 'label' => 'DNI/NIE', 'placeholder' => '12345678A', 'value' => old('dni', $user->dni ?? '')],
                ['name' => 'role', 'component' => 'select', 'label' => 'Rol', 'options' => $roleOptions, 'selectedValue' => old('role', $user->role ?? ''), 'placeholder' => 'Seleccionar rol...']
            ],
            [
                ['name' => 'education_level', 'component' => 'select', 'label' => 'Nivel Educativo', 'options' => $educationOptions, 'selectedValue' => old('education_level', $user->education_level ?? ''), 'placeholder' => 'Seleccionar...'],
                ['name' => 'institution_name', 'component' => 'select', 'label' => 'Institución (Si no pertenece a un centro)', 'options' => ['' => '-- Ninguna --'] + array_combine($instituciones_existentes, $instituciones_existentes), 'selectedValue' => old('institution_name', $user->institution_name ?? ''), 'placeholder' => 'Seleccionar institución existente...']
            ],
            [
                ['name' => 'educational_center_id', 'component' => 'select', 'label' => 'Centro Educativo (Vinculación oficial)', 'options' => ['' => '-- Ninguno --'] + ($educational_centers ?? []), 'selectedValue' => old('educational_center_id', $user->educational_center_id ?? request('center') ?? ''), 'placeholder' => 'Selecciona un centro...']
            ],
            [
                ['name' => 'description', 'component' => 'textarea', 'label' => 'Descripción / Biografía', 'placeholder' => 'Cuenta algo sobre el usuario...', 'value' => old('description', $user->description ?? '')]
            ]
        ]" />


</x-admin.crud-form>
