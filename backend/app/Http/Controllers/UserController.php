<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Rol;
use App\Models\Group;
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
        // Mapa de centro_id => tipo de educación (para auto-rellenar nivel académico)
        $centerTypes = EducationalCenter::orderBy('name')->pluck('type', 'id')->toArray();
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
            ['name' => 'educational_center_id', 'type' => 'select', 'label' => 'Centro Educativo', 'options' => ['' => '-- Ninguno --'] + $centers, 'selectedValue' => old('educational_center_id', $user->educational_center_id ?? ''), 'placeholder' => 'Vincular a un centro...', 'data' => ['center-types' => json_encode($centerTypes)]],
            ['name' => 'education_level', 'type' => 'select', 'label' => 'Nivel Académico', 'options' => EducationalCenter::$niveles_disponibles, 'selectedValue' => old('education_level', $user->education_level ?? ''), 'disabled' => 'disabled'],
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
            'profile_picture' => 'nullable|image|max:15360',
            'banner'    => 'nullable|image|max:15360',
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
        // 3. Sincronizar nombre de institución y nivel académico desde el centro
        if ($request->filled('educational_center_id')) {
            $center = EducationalCenter::find($request->educational_center_id);
            if ($center) {
                $data['institution_name'] = $center->name;
                $data['education_level'] = $center->type;
            }
        }
        // 4. Si cambia el rol, revocar todos los tokens del usuario
        //    para que el JWT quede inválido de inmediato y el frontend
        //    no pueda seguir usando datos obsoletos del localStorage.
        $roleChanged = $user
            && isset($data['role'])
            && $data['role'] !== $user->role;
        $request->replace($data);
        $result = parent::save($request, $user);
        if ($roleChanged) {
            $user->tokens()->delete();
        }
        return $result;
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
        $centerId = $request->query('center_id');
        $centerName = $request->query('center_name');
        $groupId = $request->query('group_id');
        
        if (!$centerId && !$centerName && !$groupId) {
            return response()->json([]);
        }
        $query = User::query();
        if ($groupId) {
            $query->whereHas('groupsAsStudent', function($q) use ($groupId) {
                $q->where('groups.id', $groupId);
            });
        } elseif ($centerId && $centerName) {
            $query->where(function($q) use ($centerId, $centerName) {
                $q->where('educational_center_id', $centerId)
                  ->orWhereRaw('LOWER(institution_name) = ?', [strtolower($centerName)]);
            });
        } elseif ($centerId) {
            $query->where('educational_center_id', $centerId);
        } elseif ($centerName && strtolower($centerName) !== 'varios') {
            $query->whereRaw('LOWER(institution_name) = ?', [strtolower($centerName)]);
        }
        $roles = ['Student', 'student', 'Alumno', 'alumno', 'Estudiante', 'estudiante', 'estudiantes'];
        
        // If it's the admin center, include admins as "students" for the chat list
        $isAdminCenter = ($centerId && $centerId == 40) || ($centerName && strtolower($centerName) === 'administración telamonet');
        if ($isAdminCenter) {
            $roles[] = 'Admin';
            $roles[] = 'admin';
        }

        $students = $query->whereIn('role', $roles)
            ->limit(100)
            ->get(['id', 'name', 'last_name', 'profile_picture']);
        return response()->json($students);
    }

    /**
     * API: Generar usuarios de prueba
     */
    public function apiGenerateTestUsers(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array(strtolower($user->role), ['admin', 'ei', 'administrador'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $count = min((int) $request->input('count', 20), 100);
        $centerId = $request->input('center_id', $user->educational_center_id);

        if (!$centerId) {
            return response()->json(['message' => 'No tienes un centro asignado'], 400);
        }

        $center = EducationalCenter::find($centerId);
        if (!$center) {
            return response()->json(['message' => 'Centro no encontrado'], 404);
        }

        $nombres = ['Carlos', 'Lucía', 'Mateo', 'Valentina', 'Alejandro', 'Sofía', 'Hugo', 'Martina', 'Daniel', 'Julia', 'Pablo', 'Emma', 'Diego', 'Valeria', 'Alba', 'Mario', 'Elena', 'Adrian', 'Paula', 'Marcos', 'Irene', 'Raúl', 'Lara', 'Sergio', 'Claudia', 'Jorge', 'Natalia', 'Álvaro', 'Celia', 'Ismael', 'Ainoa', 'Rubén', 'Cristina', 'Oriol', 'Marina', 'Guille', 'Laia', 'Nacho', 'Rocío', 'Edu', 'Ángela', 'Unai', 'Marta', 'Víctor', 'Silvia', 'Iván', 'Gema', 'Javier', 'Nerea', 'Álex'];
        $apellidos = ['García', 'Fernández', 'López', 'Martínez', 'González', 'Pérez', 'Rodríguez', 'Sánchez', 'Ramírez', 'Torres', 'Díaz', 'Muñoz', 'Romero', 'Alonso', 'Navarro', 'Ruiz', 'Jiménez', 'Moreno', 'Álvarez', 'Gutiérrez', 'Castro', 'Ortiz', 'Rubio', 'Molina', 'Delgado', 'Gil', 'Serrano', 'Blanco', 'Cortés', 'Suárez', 'Mendoza', 'Herrera', 'Medina', 'Garrido', 'Vargas', 'Flores', 'Peña', 'Cabrera', 'Campos', 'Santos', 'Iglesias', 'Cruz', 'Reyes', 'Vega', 'Aguilar', 'Carrasco', 'Benítez', 'Moya', 'Rivas', 'Pascual'];

        $groups = Group::where('educational_center_id', $center->id)->get();
        $existingEmails = User::pluck('email')->toArray();
        $created = 0;

        for ($i = 0; $i < $count; $i++) {
            $name = $nombres[array_rand($nombres)];
            $surname = $apellidos[array_rand($apellidos)];
            $seed = rand(100000, 999999);
            $email = strtolower($name) . "." . strtolower($surname) . "." . $seed . "@" . strtolower(str_replace(' ', '', $center->name)) . ".es";

            if (in_array($email, $existingEmails)) continue;

            $role = rand(0, 4) === 0 ? 'Teacher' : 'Student';
            $existingEmails[] = $email;

            $newUser = User::create([
                'name' => $name,
                'last_name' => $surname,
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
                'role' => $role,
                'educational_center_id' => $center->id,
                'dni' => sprintf('%08d', 20000000 + $seed) . ($role === 'Teacher' ? 'T' : 'S'),
                'institution_name' => $center->name,
                'education_level' => $center->type,
            ]);

            if ($role === 'Student' && $groups->isNotEmpty()) {
                $group = $groups->random();
                $group->students()->syncWithoutDetaching([$newUser->id]);
            }

            $created++;
        }

        return response()->json([
            'message' => "{$created} usuarios creados correctamente en {$center->name}",
            'created' => $created
        ]);
    }
    /**
     * API: Obtener perfil de usuario detallado
     * También soporta la vista Admin (Blade) mediante herencia
     */
    public function show(Request $request, $id)
    {
        $user = User::withCount(['followers', 'following'])
            ->with($this->with)
            ->findOrFail($id);

        // Si la petición espera JSON o es de la API, devolvemos JSON (para el perfil en Vue)
        if ($request->is('api/*') || ($request->expectsJson() && !$request->ajax())) {
            $authUser = $request->user();
            $userData = $user->toArray();
            
            // Añadir si el usuario autenticado sigue a este usuario
            if ($authUser) {
                $userData['is_following'] = $user->followers()->where('follower_id', $authUser->id)->exists();
            } else {
                $userData['is_following'] = false;
            }

            return response()->json($userData);
        }

        // Si es una petición web (Admin), usamos la lógica de la plantilla (TemplateController)
        return $this->renderForm($user, 'show', 'disabled');
    }

    /**
     * API: Seguir o dejar de seguir a un usuario
     */
    public function follow(Request $request, $id)
    {
        $follower = $request->user();
        if (!$follower) return response()->json(['message' => 'No autenticado'], 401);
        
        if ($follower->id == $id) {
            return response()->json(['message' => 'No puedes seguirte a ti mismo'], 400);
        }

        $userToFollow = User::findOrFail($id);
        
        // Toggle follow
        $isFollowing = $userToFollow->followers()->where('follower_id', $follower->id)->exists();
        
        if ($isFollowing) {
            $userToFollow->followers()->detach($follower->id);
            $message = 'Has dejado de seguir a ' . $userToFollow->name;
            $following = false;
        } else {
            $userToFollow->followers()->attach($follower->id);
            $message = 'Ahora sigues a ' . $userToFollow->name;
            $following = true;
        }

        return response()->json([
            'is_following' => $following,
            'followers_count' => $userToFollow->followers()->count(),
            'message' => $message
        ]);
    }

    /**
     * API: Buscar tutor por DNI
     */
    public function findTutorByDni(Request $request)
    {
        $dni = $request->query('dni');
        if (!$dni) return response()->json(['message' => 'DNI requerido'], 400);

        $tutor = User::where('dni', $dni)
            ->where('role', 'EU')
            ->first(['id', 'name', 'last_name', 'dni', 'profile_picture']);

        if (!$tutor) {
            return response()->json(['message' => 'Tutor no encontrado o no tiene el rol de Usuario Externo'], 404);
        }

        return response()->json($tutor);
    }

    /**
     * API: Añadir tutor legal
     */
    public function addTutor(Request $request)
    {
        $student = $request->user();
        if (!$student) return response()->json(['message' => 'No autenticado'], 401);

        $tutorId = $request->input('tutor_id');
        $tutor = User::where('id', $tutorId)->where('role', 'EU')->first();

        if (!$tutor) {
            return response()->json(['message' => 'Tutor no válido'], 404);
        }

        $student->tutors()->syncWithoutDetaching([$tutorId]);

        return response()->json([
            'message' => 'Tutor añadido correctamente',
            'tutors' => $student->tutors()->get(['users.id', 'name', 'last_name', 'profile_picture', 'dni'])
        ]);
    }

    /**
     * API: Eliminar tutor legal
     */
    public function removeTutor(Request $request, $tutorId)
    {
        $student = $request->user();
        if (!$student) return response()->json(['message' => 'No autenticado'], 401);

        $student->tutors()->detach($tutorId);

        return response()->json(['message' => 'Tutor eliminado correctamente']);
    }

    /**
     * API: Obtener tutores de un usuario
     */
    public function getTutors(Request $request, $userId)
    {
        $authUser = $request->user();
        if (!$authUser) return response()->json(['message' => 'No autenticado'], 401);

        $role = strtolower($authUser->role);
        $canView = $authUser->id == $userId 
            || in_array($role, ['teacher', 'ei', 'admin', 'profesor', 'institución educativa', 'administrador']);

        if (!$canView) {
            return response()->json(['message' => 'No tienes permiso para ver esta información'], 403);
        }

        $user = User::findOrFail($userId);
        $tutors = $user->tutors()->get(['users.id', 'name', 'last_name', 'profile_picture', 'dni']);

        return response()->json($tutors);
    }

    /**
     * API: Obtener profesores de los centros educativos de los hijos/alumnos del tutor
     */
    public function getTutorTeachers(Request $request, $userId)
    {
        $authUser = $request->user();
        if (!$authUser) return response()->json(['message' => 'No autenticado'], 401);

        // A tutor can only see teachers for their own students
        if ($authUser->id != $userId && strtolower($authUser->role) !== 'admin') {
            return response()->json(['message' => 'No tienes permiso para ver esta información'], 403);
        }

        $user = User::findOrFail($userId);
        if ($user->role !== 'EU') {
            return response()->json([]);
        }

        // Get all educational_center_ids of the students that this tutor has
        $centerIds = $user->studentsOfTutor()
            ->whereNotNull('educational_center_id')
            ->pluck('educational_center_id')
            ->unique()
            ->toArray();

        if (empty($centerIds)) {
            return response()->json([]);
        }

        // Get all teachers from these educational centers
        $teachers = User::whereIn('educational_center_id', $centerIds)
            ->where('role', 'Teacher')
            ->get(['id', 'name', 'last_name', 'role', 'profile_picture', 'dni', 'educational_center_id', 'institution_name']);

        return response()->json($teachers);
    }

    /**
     * API: Obtener alumnos/hijos asociados a un tutor legal
     */
    public function getTutorStudents(Request $request, $userId)
    {
        $authUser = $request->user();
        if (!$authUser) return response()->json(['message' => 'No autenticado'], 401);

        $role = strtolower($authUser->role);
        // Solo profesores, centros educativos y administradores tienen acceso
        $canView = in_array($role, ['teacher', 'ei', 'admin', 'profesor', 'institución educativa', 'administrador']);

        if (!$canView) {
            return response()->json(['message' => 'No tienes permiso para ver esta información'], 403);
        }

        $user = User::findOrFail($userId);
        if ($user->role !== 'EU') {
            return response()->json([]);
        }

        // Get all students of this tutor
        $students = $user->studentsOfTutor()->get(['users.id', 'name', 'last_name', 'role', 'profile_picture', 'dni']);

        return response()->json($students);
    }
}