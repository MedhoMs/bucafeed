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
        <x-admin.form-template :disabled="$disabled" :fields="$fields" />
    @else
        <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-xl mb-6 flex flex-col items-center text-center">
            <h3 class="text-red-400 font-bold mb-2 text-lg">¡Advertencia Destructiva!</h3>
            <p class="text-sm text-red-400/80 max-w-md">Esta acción eliminará el centro educativo <strong class="text-white">{{ $center->name }}</strong>. Los usuarios (EI, Profesores, Alumnos) vinculados a este centro quedarán desasignados, pero <strong>no</strong> serán eliminados del sistema.</p>
        </div>
    @endif
</x-admin.crud-form>
