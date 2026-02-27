<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rol;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Rol::all();
        
        if ($request->ajax()) {
            return view('roles.index', [
                'roles' => $roles,
                'roles_disponibles' => Rol::$roles_disponibles
            ])->renderSections()['content'];
        }
        
        return view('roles.index', [
            'roles' => $roles,
            'roles_disponibles' => Rol::$roles_disponibles
        ]);
    }

    public function create()
    {
        $datos = ['exito' => ''];
        return view('roles.create', [
            'role' => new Rol(),
            'oper' => 'create',
            'disabled' => '',
            'datos' => $datos
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
            'oper' => 'edit',
            'disabled' => '',
            'datos' => ['exito' => 'Rol creado correctamente.']
        ]);
    }

    public function edit($id)
    {
        $role = Rol::findOrFail($id);
        $datos = ['exito' => ''];
        
        return view('roles.create', [
            'role' => $role,
            'oper' => 'edit',
            'disabled' => '',
            'datos' => $datos
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
            'oper' => 'show',
            'disabled' => 'disabled',
            'datos' => ['exito' => '']
        ]);
    }

    public function destroy($id)
    {
        $role = Rol::findOrFail($id);
        $datos = ['exito' => ''];
        
        return view('roles.create', [
            'role' => $role,
            'oper' => 'destroy',
            'disabled' => 'disabled',
            'datos' => $datos
        ]);
    }

    public function destroyPost($id)
    {
        $role = Rol::findOrFail($id);
        $role->delete();

        return view('roles.create', [
            'role' => new Rol(),
            'oper' => 'destroy',
            'disabled' => 'disabled',
            'datos' => ['exito' => 'Rol eliminado correctamente']
        ]);
    }
}
