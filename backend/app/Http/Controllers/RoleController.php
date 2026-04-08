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
        
        if ($request->ajax()) {
            return view('roles.index', [
                'roles' => $roles,
                'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray()
            ]);
        }
        
        return view('roles.index', [
            'roles' => $roles,
            'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray()
        ]);
    }

    public function create(Request $request)
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

            $role->name = $request->name;
            $role->code = $request->code;
            $role->save();

            $datos['exito'] = 'Rol creado correctamente.';
            if ($request->ajax()) {
                return response()->json(['exito' => 'Rol creado correctamente.']);
            }
        }

        if ($request->ajax()) {
            return view('roles.create', [
                'role' => $role,
                'fields' => $this->getRoleFields($role),
            'oper' => 'create',
                'disabled' => '',
                'datos' => $datos
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

    public function edit(Request $request, $id)
    {
        $role = Rol::findOrFail($id);
        $datos = ['exito' => ''];
        $disabled = '';
        
        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:roles,code,' . $id,
            ]);

            $role->name = $request->name;
            $role->code = $request->code;
            $role->save();

            $datos['exito'] = 'Rol actualizado correctamente';
            $disabled = 'disabled';
            
            if ($request->ajax()) {
                return response()->json(['exito' => 'Rol actualizado correctamente']);
            }
        }

        return view('roles.create', [
            'role' => $role,
            'fields' => $this->getRoleFields($role),
            'oper' => 'edit',
            'disabled' => $disabled,
            'datos' => $datos
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

    public function destroy(Request $request, $id)
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

            return redirect()->route('role.index');
        }

        $datos = ['exito' => ''];
        $disabled = 'disabled';

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
