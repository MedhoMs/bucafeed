<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rol;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Rol::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('code', 'like', "%$search%");
            });
        }

        $roles = $query->paginate(10);
        
        return view('roles.index', [
            'roles' => $roles,
            'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray()
        ]);
    }

    public function create()
    {
        $role = new Rol();
        return view('roles.create', [
            'role' => $role,
            'fields' => $this->getRoleFields($role),
            'oper' => 'create',
            'disabled' => '',
            'datos' => ['exito' => '']
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'code' => 'required|string|max:50|unique:roles,code',
        ]);

        $role = new Rol();
        $role->name = $request->name;
        $role->code = $request->code;
        $role->save();

        return view('roles.create', [
            'role' => $role,
            'fields' => $this->getRoleFields($role),
            'oper' => 'edit',
            'disabled' => '',
            'datos' => ['exito' => 'Rol creado correctamente.']
        ]);
    }

    public function edit($id)
    {
        $role = Rol::findOrFail($id);
        
        return view('roles.create', [
            'role' => $role,
            'fields' => $this->getRoleFields($role),
            'oper' => 'edit',
            'disabled' => '',
            'datos' => ['exito' => '']
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:roles,code,' . $id,
        ]);

        $role = Rol::findOrFail($id);
        $role->name = $request->name;
        $role->code = $request->code;
        $role->save();

        return view('roles.create', [
            'role' => $role,
            'fields' => $this->getRoleFields($role),
            'oper' => 'edit',
            'disabled' => '',
            'datos' => ['exito' => 'Rol actualizado correctamente']
        ]);
    }

    public function show($id)
    {
        $role = Rol::findOrFail($id);
        
        return view('roles.create', [
            'role' => $role,
            'fields' => $this->getRoleFields($role),
            'oper' => 'show',
            'disabled' => 'disabled',
            'datos' => ['exito' => '']
        ]);
    }

    public function destroy($id)
    {
        $role = Rol::findOrFail($id);
        
        return view('roles.create', [
            'role' => $role,
            'fields' => $this->getRoleFields($role),
            'oper' => 'destroy',
            'disabled' => 'disabled',
            'datos' => ['exito' => '']
        ]);
    }

    public function destroyPost($id)
    {
        $role = Rol::findOrFail($id);
        $role->delete();

        return view('roles.create', [
            'role' => $role,
            'fields' => $this->getRoleFields($role),
            'oper' => 'destroy',
            'disabled' => 'disabled',
            'datos' => ['exito' => 'Rol eliminado correctamente']
        ]);
    }

    /**
     * Define los campos para el formulario de roles.
     */
    protected function getRoleFields($role = null)
    {
        return [
            ['name' => 'name', 'label' => 'Nombre del Rol', 'placeholder' => 'Ej: Editor', 'value' => old('name', $role->name ?? ''), 'required' => true],
            ['name' => 'code', 'label' => 'Código del Rol (Único)', 'placeholder' => 'Ej: EDTR', 'value' => old('code', $role->code ?? ''), 'required' => true]
        ];
    }
}
