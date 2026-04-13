<x-admin.crud-form 
    :action="$oper == 'create' ? route('role.store') : ($oper == 'destroy' ? route('role.destroy.post', $role->id ?? 0) : route('role.update', $role->id ?? 0))"
    :oper="$oper"
    :modelId="$role->id ?? ''"
    :datos="$datos ?? []"
    :disabled="$disabled"
    :showCancel="false"
    saveText="Guardar Rol"
    editText="Actualizar Rol"
>

        <x-admin.form-template :disabled="$disabled" :fields="$fields" />

</x-admin.crud-form>
