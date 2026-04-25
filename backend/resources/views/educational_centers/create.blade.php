<x-admin.crud-form
    :oper="$oper"
    :action="route('educational_centers.' . ($oper === 'edit' || $oper === 'destroy' ? $oper . '.post' : 'create.post'), $center->id ?? '')"
    :title="$oper === 'create' ? 'Crear Nuevo Centro Educativo' : ($oper === 'edit' ? 'Editar Centro Educativo' : ($oper === 'show' ? 'Consultar Centro' : 'Eliminar Centro'))"
    :description="$oper === 'destroy' ? '¿Estás seguro de que deseas eliminar este centro educativo?' : ($oper === 'show' ? 'Detalles del centro educativo y configuración actual.' : 'Completa los datos del centro educativo y asigna un administrador.')"
    :datos="$datos"
    :disabled="$disabled"
    enctype="multipart/form-data"
>


    <x-admin.form-template :disabled="$disabled || $oper === 'destroy' ? 'disabled' : ''" :fields="$fields" />
</x-admin.crud-form>
