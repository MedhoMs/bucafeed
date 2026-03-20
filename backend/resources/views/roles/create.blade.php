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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach([
                ['name' => 'name', 'label' => 'Nombre del Rol',         'placeholder' => 'Ej: Editor', 'value' => $role->name ?? ''],
                ['name' => 'code', 'label' => 'Código del Rol (Unico)', 'placeholder' => 'Ej: EDTR',   'value' => $role->code ?? '']
            ] as $field)
                <x-admin.form.input 
                    :name="$field['name']" 
                    :label="$field['label']" 
                    :value="old($field['name'], $field['value'])" 
                    :placeholder="$field['placeholder']" 
                    :disabled="$disabled" 
                />
            @endforeach
        </div>

</x-admin.crud-form>
