<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RoleController extends TemplateController
{
    protected $model = Rol::class;
    protected $viewPath = 'roles';

    protected function getFormFields($role = null)
    {
        return [
            ['name' => 'name', 'label' => 'Nombre del Rol', 'placeholder' => 'Ej: Estudiante', 'value' => old('name', $role->name ?? ''), 'required' => true],
            ['name' => 'code', 'label' => 'Código Único', 'placeholder' => 'Ej: Student', 'value' => old('code', $role->code ?? ''), 'required' => true]
        ];
    }

    protected function rules($role = null)
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . ($role->id ?? 'NULL'),
            'code' => 'required|string|max:50|unique:roles,code,' . ($role->id ?? 'NULL'),
        ];
    }
}
