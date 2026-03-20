<x-admin.crud-form
    :oper="$oper"
    :action="route('educational_centers.' . ($oper === 'edit' || $oper === 'destroy' ? $oper . '.post' : 'create.post'), $center->id ?? '')"
    :title="$oper === 'create' ? 'Crear Nuevo Centro Educativo' : ($oper === 'edit' ? 'Editar Centro Educativo' : ($oper === 'show' ? 'Consultar Centro' : 'Eliminar Centro'))"
    :description="$oper === 'destroy' ? '¿Estás seguro de que deseas eliminar este centro educativo?' : ($oper === 'show' ? 'Detalles del centro educativo y configuración actual.' : 'Completa los datos del centro educativo y asigna un administrador.')"
    :datos="$datos"
    :disabled="$disabled"
    enctype="multipart/form-data"
>
    @if($oper !== 'destroy')
        <!-- Sección de Media (Reutilizado de Usuario) -->
        @if($oper !== 'create')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-white/5 rounded-[2rem] border border-white/10 mb-8 mt-2">
            <div>
                <label for="idicon" class="block text-sm font-medium text-white/70 mb-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[var(--admin-accent-1)]"><path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1" /></svg>
                    Logo / Icono del Centro
                </label>
                <div class="flex items-center gap-4">
                    @if($center->icon)
                        <div class="w-12 h-12 rounded-xl border border-white/10 overflow-hidden bg-slate-800">
                            <img src="{{ $center->icon }}" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <input {{ $disabled }} type="file" name="icon" id="idicon" 
                        class="flex-1 text-sm text-white/50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:tracking-widest file:uppercase file:bg-[var(--admin-accent-1-bg)] file:text-[var(--admin-accent-1)] hover:file:bg-[var(--admin-accent-1-border)] transition-all cursor-pointer">
                </div>
            </div>
            <div>
                <label for="idbanner" class="block text-sm font-medium text-white/70 mb-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[var(--admin-accent-1)]"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                    Imagen de Banner
                </label>
                <div class="flex items-center gap-4">
                    @if($center->banner)
                        <div class="w-12 h-12 rounded-xl border border-white/10 overflow-hidden bg-slate-800">
                            <img src="{{ $center->banner }}" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <input {{ $disabled }} type="file" name="banner" id="idbanner" 
                        class="flex-1 text-sm text-white/50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:tracking-widest file:uppercase file:bg-[var(--admin-accent-1-bg)] file:text-[var(--admin-accent-1)] hover:file:bg-[var(--admin-accent-1-border)] transition-all cursor-pointer">
                </div>
            </div>
        </div>
        @endif

        <x-admin.form.builder :disabled="$disabled" :rows="[
            [
                ['name' => 'name', 'label' => 'Nombre del Centro', 'placeholder' => 'Ej: IES Zonzamas', 'value' => old('name', $center->name ?? '')],
                ['name' => 'admin_user_id', 'component' => 'select', 'label' => 'Administrador Principal (Rol EI)', 'options' => ['' => '-- Sin Administrador --'] + $adminUsers, 'selectedValue' => old('admin_user_id', $center->adminUser ? $center->adminUser->id : ''), 'placeholder' => 'Selecciona al responsable']
            ],
            [
                ['name' => 'location', 'label' => 'Ubicación / Municipio', 'placeholder' => 'Ej: Arrecife', 'value' => old('location', $center->location ?? '')],
                ['name' => 'type', 'component' => 'select', 'label' => 'Tipo de Educación Predominante', 'options' => \App\Models\EducationalCenter::$niveles_disponibles, 'selectedValue' => old('type', $center->type ?? ''), 'placeholder' => 'Selecciona el nivel...']
            ]
        ]" />
    @else
        <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-xl mb-6 flex flex-col items-center text-center">
            <h3 class="text-red-400 font-bold mb-2 text-lg">¡Advertencia Destructiva!</h3>
            <p class="text-sm text-red-400/80 max-w-md">Esta acción eliminará el centro educativo <strong class="text-white">{{ $center->name }}</strong>. Los usuarios (EI, Profesores, Alumnos) vinculados a este centro quedarán desasignados, pero <strong>no</strong> serán eliminados del sistema.</p>
        </div>
    @endif
</x-admin.crud-form>
