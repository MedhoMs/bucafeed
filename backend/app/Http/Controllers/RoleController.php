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
            ]);
        }
        
        return view('roles.index', [
            'roles' => $roles,
            'roles_disponibles' => Rol::$roles_disponibles
        ]);
    }

    public function create(Request $request)
    {
        $datos = ['exito' => ''];
        $role = new Rol();
        
        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'code' => 'required|string|max:50|unique:roles,code',
            ]);

            $role->name = $request->name;
            $role->code = $request->code;
            $role->save();

            $datos['exito'] = 'Rol creado correctamente.';
        }

        if ($request->ajax()) {
            return view('roles.create', [
                'role' => $role,
                'oper' => 'create',
                'disabled' => '',
                'datos' => $datos
            ]);
        }

        $role = new Rol();

        return view('roles.create', [
            'role' => $role,
            'oper' => 'create',
            'disabled' => '',
            'datos' => $datos
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
                return view('roles.create', [
                    'role' => $role,
                    'oper' => 'edit',
                    'disabled' => $disabled,
                    'datos' => $datos
                ]);
            }
        }

        return view('roles.create', [
            'role' => $role,
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
            'oper' => 'show',
            'disabled' => 'disabled',
            'datos' => ['exito' => '']
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $role = Rol::findOrFail($id);
        $datos = ['exito' => ''];
        $disabled = 'disabled';
        
        if (!$role) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Rol no encontrado'], 404);
            }
        }

        if ($request->isMethod('post')) {
            $role->delete();

            if ($request->ajax()) {
                return response()->json([
                    'exito' => 'Rol eliminado correctamente'
                ]);
            }

            return redirect()->route('role.index');
        }

        return view('roles.create', [
            'role' => $role,
            'oper' => 'destroy',
            'disabled' => $disabled,
            'datos' => $datos
        ]);
    }
}
