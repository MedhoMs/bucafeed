<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol;
use App\Models\EducationalCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * CONTROLADOR DE USUARIOS (Refactorizado con Herencia TelamoNet)
 */
class UserController extends TemplateController
{
    protected $model = User::class;
    protected $viewPath = 'users';
    protected $with = ['educationalCenter', 'student.cycle', 'groupsAsStudent.cycle', 'groupsAsTeacher.subjectsWithTeachers']; 

    /**
     * Filtros extra específicos para usuarios
     */
    protected function extraFilters($query, Request $request)
    {
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('level')) {
            $query->where('education_level', $request->level);
        }
        // Sincronizado con el nombre del filter-dropdown en la vista
        if ($request->filled('institution')) {
            $value = $request->institution;
            if (is_numeric($value)) {
                $query->where('educational_center_id', $value);
            } else {
                $query->where('institution_name', $value);
            }
        }

        // Nuevo: Filtro por Área / Ciclo
        if ($request->filled('cycle')) {
            $cycleId = $request->cycle;
            $query->where(function($q) use ($cycleId) {
                $q->whereHas('student', function($sq) use ($cycleId) {
                    $sq->where('cycle_id', $cycleId);
                })->orWhereHas('groupsAsTeacher', function($tq) use ($cycleId) {
                    $tq->where('cycle_id', $cycleId);
                });
            });
        }
        
        return $query;
    }

    protected function indexExtras(Request $request)
    {
        return [
            'roles_disponibles' => Rol::pluck('name', 'code')->toArray(),
            'niveles_disponibles' => EducationalCenter::$niveles_disponibles,
            'centros' => EducationalCenter::orderBy('name')->pluck('name', 'id')->toArray(),
            'ciclos_disponibles' => \App\Models\Cycle::orderBy('name')->pluck('name', 'id')->toArray()
        ];
    }

    /**
     * DEFINICIÓN DE CAMPOS (Centralizado)
     */
    protected function getFormFields($user = null)
    {
        $centers = EducationalCenter::orderBy('name')->pluck('name', 'id')->toArray();
        $roles = Rol::pluck('name', 'code')->toArray();

        $fields = [
            ['name' => 'name', 'label' => 'Nombre', 'placeholder' => 'Ej: Juan', 'value' => old('name', $user->name ?? ''), 'required' => true],
            ['name' => 'last_name', 'label' => 'Apellidos', 'placeholder' => 'Ej: Pérez García', 'value' => old('last_name', $user->last_name ?? ''), 'required' => true],
            ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'placeholder' => 'juan@ejemplo.com', 'value' => old('email', $user->email ?? ''), 'required' => true],
        ];

        $isEdit = $user && $user->exists;
        $fields[] = [
            'name' => 'password', 
            'type' => 'password', 
            'label' => 'Contraseña', 
            'placeholder' => $isEdit ? 'Dejar en blanco para no cambiar' : 'Mínimo 8 caracteres', 
            'required' => !$isEdit
        ];

        $fields = array_merge($fields, [
            ['name' => 'dni', 'label' => 'DNI/NIE', 'placeholder' => '12345678A', 'value' => old('dni', $user->dni ?? ''), 'required' => true],
            ['name' => 'role', 'type' => 'select', 'label' => 'Rol', 'options' => $roles, 'selectedValue' => old('role', $user->role ?? ''), 'required' => true],
            ['name' => 'educational_center_id', 'type' => 'select', 'label' => 'Centro Educativo', 'options' => ['' => '-- Ninguno --'] + $centers, 'selectedValue' => old('educational_center_id', $user->educational_center_id ?? ''), 'placeholder' => 'Vincular a un centro...'],
            ['name' => 'education_level', 'type' => 'select', 'label' => 'Nivel Académico', 'options' => EducationalCenter::$niveles_disponibles, 'selectedValue' => old('education_level', $user->education_level ?? '')],
            ['name' => 'institution_name', 'label' => 'Nombre Institución (Texto)', 'placeholder' => 'Ej: IES Zonzamas', 'value' => old('institution_name', $user->institution_name ?? ''), 'full' => true],
            ['name' => 'description', 'type' => 'textarea', 'label' => 'Biografía', 'value' => old('description', $user->description ?? ''), 'full' => true]
        ]);

        return $fields;
    }

    protected function rules($user = null)
    {
        $isUpdate = $user && $user->exists;

        return [
            'name'      => ($isUpdate ? 'nullable' : 'required') . '|string|max:255',
            'last_name' => ($isUpdate ? 'nullable' : 'required') . '|string|max:255',
            'email'     => ($isUpdate ? 'nullable' : 'required') . '|string|email|max:255|unique:users,email,' . ($user->id ?? 'NULL'),
            'password'  => ($isUpdate ? 'nullable' : 'required') . '|string|min:8',
            'dni'       => ($isUpdate ? 'nullable' : 'required') . '|string|max:20|unique:users,dni,' . ($user->id ?? 'NULL'),
            'role'      => ($isUpdate ? 'nullable' : 'required') . '|string',
            'profile_picture' => 'nullable|image|max:5120',
            'banner'    => 'nullable|image|max:5120',
        ];
    }

    protected function save(Request $request, $user = null)
    {
        // 1. Manejo de contraseña automática para nuevos usuarios
        if (!$user && !$request->filled('password')) {
            $request->merge(['password' => $request->dni ?? 'telamonet']);
        }

        $data = $request->all();
        
        // 2. Encriptar contraseña si está presente
        if (!empty($data['password'])) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // 3. Sincronizar nombre de institución
        if ($request->filled('educational_center_id')) {
            $center = EducationalCenter::find($request->educational_center_id);
            if ($center) {
                $data['institution_name'] = $center->name;
            }
        }

        $request->replace($data);
        return parent::save($request, $user);
    }

    /**
     * Mantenemos métodos específicos extra
     */
    public function profileModal($id)
    {
        $user = User::with('educationalCenter')->findOrFail($id);
        return view('users.profile_modal', compact('user'));
    }

    /**
     * API: Obtener alumnos de un centro (para chats y charlas)
     */
    public function apiStudentsByCenter(Request $request)
    {
        $centerName = $request->query('center_name');
        
        if (!$centerName) {
            return response()->json([]);
        }

        $students = User::where('institution_name', $centerName)
            ->where('role', 'Student')
            ->limit(20)
            ->get(['id', 'name', 'last_name', 'profile_picture']);

        return response()->json($students);
    }
}