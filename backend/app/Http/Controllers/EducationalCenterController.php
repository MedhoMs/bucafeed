<?php

namespace App\Http\Controllers;

use App\Models\EducationalCenter;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Cycle;
use App\Models\Group;
use App\Models\Rol;
use App\Models\Tag;
use App\Models\Event;
use App\Models\Publication;



use Illuminate\Http\Request;

class EducationalCenterController extends TemplateController
{
    protected $model = EducationalCenter::class;
    protected $viewPath = 'educational_centers';
    protected $with = ['adminUser', 'students', 'teachers', 'cycles.tags'];
    protected $withCount = ['students', 'teachers'];

    public function destroy(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);

        if ($request->isMethod('get')) {
            return $this->renderForm($center, 'destroy');
        }

        // 1. Desasignar usuarios (poner a null su educational_center_id)
        User::where('educational_center_id', $id)->update([
            'educational_center_id' => null,
            'institution_name' => null
        ]);

        Group::where('educational_center_id', $id)->delete();
        Event::where('educational_center_id', $id)->delete();
        $center->cycles()->detach();

        $center->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Centro eliminado correctamente.']);
        }
        return redirect()->route('educational_centers.index')->with('success', 'Centro eliminado correctamente.');
    }

    protected function extraFilters($query, Request $request)
    {
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        $isAdmin = $request->user() && strtolower($request->user()->role) === 'admin';
        if (!$isAdmin) {
            $query->where('type', '!=', 'TM');
        }
        return $query;
    }

    protected function indexExtras(Request $request)
    {
        $locations = EducationalCenter::whereNotNull('location')->where('location', '!=', 'N/A')->pluck('location', 'location')->unique()->sort()->toArray();
        $types_raw = EducationalCenter::whereNotNull('type')->where('type', '!=', 'N/A')->pluck('type')->unique()->toArray();
        $types = [];
        foreach($types_raw as $t) {
            $types[$t] = EducationalCenter::$niveles_disponibles[$t] ?? $t;
        }

        return [
            'roles_disponibles' => [
                'Admin' => 'Administrador',
                'EI' => 'Institución Educativa',
                'Teacher' => 'Profesor',
                'Student' => 'Alumno'
            ],
            'niveles_disponibles' => EducationalCenter::$niveles_disponibles,
            'locations' => $locations,
            'types' => $types,
            'ciclos_disponibles' => Cycle::orderBy('name')->pluck('name', 'id')->toArray()
        ];
    }

    protected function getFormFields($center = null)
    {
        $adminUsers = User::where('role', 'EI')->get()->mapWithKeys(function($user) {
            return [$user->id => trim($user->name . ' ' . $user->last_name) . ' (' . $user->email . ')'];
        })->toArray();
        $adminOptions = ['' => '-- Sin Administrador --'] + $adminUsers;

        $fields = [
            ['name' => 'name', 'label' => 'Nombre del Centro', 'placeholder' => 'Ej: IES Zonzamas', 'value' => old('name', $center->name ?? ''), 'required' => true],
        ];

        // Se quita de la edición (modificación). Solo se muestra si el centro no está persistido todavía (creación)
        if (!$center || !$center->exists) {
            $fields[] = ['name' => 'category', 'type' => 'select', 'label' => 'Categoría del Centro', 'options' => ['CEIP' => 'Colegio (CEIP)', 'IES' => 'Instituto (IES)', 'CIFP' => 'Centro de FP (CIFP)', 'CEO' => 'Centro Obligatoria (CEO)', 'UR' => 'Universidad'], 'selectedValue' => old('category', $center->category ?? ''), 'placeholder' => 'Selecciona tipo...'];
        }

        $fields = array_merge($fields, [
            ['name' => 'admin_user_id', 'type' => 'select', 'label' => 'Administrador Principal (EI)', 'options' => $adminOptions, 'selectedValue' => old('admin_user_id', $center->admin_user_id ?? ''), 'placeholder' => 'Selecciona al responsable'],
            ['name' => 'location', 'label' => 'Ubicación / Municipio', 'placeholder' => 'Ej: Arrecife', 'value' => old('location', $center->location ?? '')],
            ['name' => 'type', 'type' => 'select', 'label' => 'Tipo de Educación', 'options' => EducationalCenter::$niveles_disponibles, 'selectedValue' => old('type', $center->type ?? ''), 'placeholder' => 'Selecciona el nivel...'],
            ['name' => 'icon', 'type' => 'file', 'label' => 'Logo / Icono', 'previewUrl' => $center->icon ?? null, 'full' => true],
            ['name' => 'banner', 'type' => 'file', 'label' => 'Imagen de Banner', 'previewUrl' => $center->banner ?? null, 'full' => true]
        ]);

        return $fields;
    }

    protected function rules($center = null)
    {
        return [
            'name' => 'required|string|max:255',
            'admin_user_id' => 'nullable|exists:users,id',
            'icon' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:4096',
        ];
    }

    public function manageCycles($id)
    {
        $center = EducationalCenter::with('cycles')->findOrFail($id);
        $globalCycles = Cycle::orderBy('name')->get();
        return view('educational_centers.manage_cycles', compact('center', 'globalCycles'));
    }

    public function addCycle(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);

        if ($request->filled('cycle_id')) {
            $center->cycles()->syncWithoutDetaching([$request->cycle_id]);
        }

        if ($request->filled('new_cycle')) {
            $cycle = Cycle::firstOrCreate(['name' => $request->new_cycle]);
            $center->cycles()->syncWithoutDetaching([$cycle->id]);
        }

        return $request->ajax() ? $this->manageCycles($id) : back();
    }

    public function removeCycle(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);
        if ($request->filled('cycle_id')) {
            $center->cycles()->detach($request->cycle_id);
        }
        return $request->ajax() ? $this->manageCycles($id) : back();
    }

    public function listUsersModal($id, $role)
    {
        $center = EducationalCenter::findOrFail($id);
        $users = User::where('educational_center_id', $id)->where('role', $role)->orderBy('name')->get();
        $title = $role === 'Student' ? 'Alumnos de ' . $center->name : 'Docentes de ' . $center->name;
        return view('educational_centers.users_modal', compact('center', 'users', 'title', 'role'));
    }

    public function manageGroups($id)
    {
        $center = EducationalCenter::with(['groups.tutor', 'groups.cycle', 'groups.students', 'groups.subjectsWithTeachers', 'teachers', 'students', 'cycles.tags'])->findOrFail($id);
        $allTags = Tag::orderBy('name')->get();
        $suggestedTags = $center->cycles->flatMap->tags->unique('id');

        $cycleTagsMap = [];
        foreach($center->cycles as $c) {
            $cycleTagsMap[$c->id] = $c->tags->pluck('id')->toArray();
        }

        return view('educational_centers.manage_groups', compact('center', 'allTags', 'suggestedTags', 'cycleTagsMap'));
    }

    public function storeGroup(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cycle_id' => 'nullable|exists:cycles,id',
            'tutor_id' => 'nullable|exists:users,id',
            'students' => 'nullable|array',
            'teachers' => 'nullable|array',
        ]);

        $group = $center->groups()->create([
            'name' => $validated['name'],
            'cycle_id' => $validated['cycle_id'],
            'tutor_id' => $validated['tutor_id'],
        ]);

        if (!empty($validated['students'])) $group->students()->attach($validated['students']);

        if (!empty($validated['teachers'])) {
            foreach ($validated['teachers'] as $tagId => $teacherId) {
                if ($teacherId) $group->subjectsWithTeachers()->attach($tagId, ['user_id' => $teacherId]);
            }
        }

        return $request->ajax() ? $this->manageGroups($id) : back();
    }

    public function groupDetailsModal($id, $groupId)
    {
        $center = EducationalCenter::findOrFail($id);
        $group = Group::with(['students', 'subjectsWithTeachers', 'tutor', 'cycle'])->where('educational_center_id', $id)->findOrFail($groupId);
        return view('educational_centers.group_details_modal', compact('center', 'group'));
    }

    public function profileModal($id)
    {
        $center = EducationalCenter::with(['adminUser', 'students', 'teachers', 'cycles'])->findOrFail($id);
        return view('educational_centers.profile_modal', compact('center'));
    }

    public function show(Request $request, $id)
    {
        $center = EducationalCenter::with(['adminUser', 'students', 'teachers', 'cycles'])->findOrFail($id);
        return view('educational_centers.show', compact('center'));
    }

    public function assign($id)
    {
        $center = EducationalCenter::findOrFail($id);
        $students = User::where('role', 'Student')
            ->where(function($q) use ($id) {
                $q->whereNull('educational_center_id')->orWhere('educational_center_id', '!=', $id);
            })
            ->orderBy('name')->get();

        return view('educational_centers.assign_modal', compact('center', 'students'));
    }

    public function assignStudent(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($validated['student_id']);
        $user->educational_center_id = $center->id;
        $user->institution_name = $center->name;
        $user->save();

        return $request->ajax() ? $this->listUsersModal($id, 'Student') : back();
    }

    public function addUsers($id)
    {
        $center = EducationalCenter::findOrFail($id);

        $studentRoles = ['Student'];
        if ($center->name === 'Administración TelamoNet') {
            $studentRoles[] = 'Admin';
        }

        $availableStudents = User::whereIn('role', $studentRoles)
            ->whereNull('educational_center_id')
            ->where('institution_name', $center->name)
            ->orderBy('name')->get();

        $availableTeachers = User::where('role', 'Teacher')
            ->whereNull('educational_center_id')
            ->where('institution_name', $center->name)
            ->orderBy('name')->get();

        return view('educational_centers.add_users', compact('center', 'availableStudents', 'availableTeachers'));
    }

    public function deleteGroup($id, $groupId)
    {
        $group = Group::findOrFail($groupId);
        $group->delete();

        return $this->manageGroups($id);
    }

    public function editGroup($id, $groupId)
    {
        $group = Group::with(['students', 'subjectsWithTeachers'])->findOrFail($groupId);
        return response()->json($group);
    }

    public function updateGroup(Request $request, $id, $groupId)
    {
        $group = Group::findOrFail($groupId);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cycle_id' => 'nullable|exists:cycles,id',
            'tutor_id' => 'nullable|exists:users,id',
            'students' => 'nullable|array',
            'teachers' => 'nullable|array',
        ]);

        $group->update([
            'name' => $validated['name'],
            'cycle_id' => $validated['cycle_id'],
            'tutor_id' => $validated['tutor_id'],
        ]);

        if (isset($validated['students'])) {
            $group->students()->sync($validated['students']);
        } else {
            $group->students()->detach();
        }

        $group->subjectsWithTeachers()->detach();
        if (!empty($validated['teachers'])) {
            foreach ($validated['teachers'] as $tagId => $teacherId) {
                if ($teacherId) {
                    $group->subjectsWithTeachers()->attach($tagId, ['user_id' => $teacherId]);
                }
            }
        }

        return $request->ajax() ? $this->manageGroups($id) : back();
    }

    public function storeUsers(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);

        if ($request->has('students')) {
            User::whereIn('id', $request->students)->update([
                'educational_center_id' => $center->id,
                'institution_name' => $center->name,
            ]);
        }

        if ($request->has('teachers')) {
            User::whereIn('id', $request->teachers)->update([
                'educational_center_id' => $center->id,
                'institution_name' => $center->name,
            ]);
        }

        if ($request->ajax()) {
            return "<div class='p-6 text-center text-green-400 font-bold'>Usuarios asignados correctamente al centro. <br><br> Ya puedes cerrar esta ventana.</div>";
        }

        return back()->with('success', 'Usuarios matriculados correctamente.');
    }


    public function apiShowMyCenter(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json(['message' => 'No tienes un centro asignado'], 403);
        }
        $center = EducationalCenter::withCount(['students', 'teachers', 'groups', 'publications'])->find($user->educational_center_id);
        return response()->json($center);
    }

    public function apiGroups(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json([]);
        }
        $groups = Group::with(['cycle', 'tutor', 'students', 'subjectsWithTeachers'])
                        ->where('educational_center_id', $user->educational_center_id)
                        ->get();
        return response()->json($groups);
    }

    public function apiStoreGroup(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cycle_id' => 'nullable|exists:cycles,id',
            'tutor_id' => 'nullable|exists:users,id',
        ]);

        $center = EducationalCenter::findOrFail($user->educational_center_id);

        $group = $center->groups()->create([
            'name' => $validated['name'],
            'cycle_id' => $validated['cycle_id'] ?? null,
            'tutor_id' => $validated['tutor_id'] ?? null,
        ]);

        return response()->json($group, 201);
    }

    public function apiUpdateGroup(Request $request, $groupId)
    {
        $user = $request->user();
        $group = Group::where('educational_center_id', $user->educational_center_id)->findOrFail($groupId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cycle_id' => 'nullable|exists:cycles,id',
            'tutor_id' => 'nullable|exists:users,id',
        ]);

        $group->update([
            'name' => $validated['name'],
            'cycle_id' => $validated['cycle_id'] ?? null,
            'tutor_id' => $validated['tutor_id'] ?? null,
        ]);
        return response()->json($group);
    }

    public function apiDeleteGroup(Request $request, $groupId)
    {
        $user = $request->user();
        $group = Group::where('educational_center_id', $user->educational_center_id)->findOrFail($groupId);
        $group->delete();
        return response()->json(['message' => 'Grupo eliminado']);
    }

    public function apiAssignStudents(Request $request, $groupId)
    {
        $user = $request->user();
        $group = Group::where('educational_center_id', $user->educational_center_id)->findOrFail($groupId);

        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id'
        ]);

        // Use syncWithoutDetaching to add them without removing existing
        $group->students()->syncWithoutDetaching($validated['student_ids']);
        return response()->json(['message' => 'Alumnos asignados']);
    }

    public function apiRemoveStudent(Request $request, $groupId, $studentId)
    {
        $user = $request->user();
        $group = Group::where('educational_center_id', $user->educational_center_id)->findOrFail($groupId);
        $group->students()->detach($studentId);
        return response()->json(['message' => 'Alumno removido']);
    }

    public function apiAssignTutor(Request $request, $groupId)
    {
        $user = $request->user();
        $group = Group::where('educational_center_id', $user->educational_center_id)->findOrFail($groupId);

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id'
        ]);

        $group->update(['tutor_id' => $validated['user_id'] ?? null]);
        return response()->json(['message' => 'Tutor asignado']);
    }

    public function apiSearchUsers(Request $request)
    {
        $search = $request->query('q');
        $role = $request->query('role');

        $authUser = $request->user();
        if (!$authUser || !$authUser->educational_center_id) {
            return response()->json([]);
        }

        $center = $authUser->educationalCenter;
        if (!$center) {
            return response()->json([]);
        }

        $query = User::whereNull('educational_center_id')
            ->where('institution_name', $center->name);

        if ($role) {
            if ($role === 'Student' && $center->name === 'Administración TelamoNet') {
                $query->whereIn('role', ['Student', 'Admin']);
            } else {
                $query->where('role', $role);
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        return response()->json($query->limit(20)->get());
    }

    public function apiEnrollUsers(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'user_ids' => 'required_without:ids|array',
            'user_ids.*' => 'exists:users,id',
            'ids' => 'required_without:user_ids|array',
            'ids.*' => 'exists:users,id',
        ]);

        $userIds = $validated['user_ids'] ?? $validated['ids'];

        $center = EducationalCenter::findOrFail($user->educational_center_id);

        User::whereIn('id', $userIds)->update([
            'educational_center_id' => $center->id,
            'institution_name' => $center->name
        ]);

        // For Students enrolled by the center, create/update their Student record with verified=false (pending verification)
        $enrolledStudents = User::whereIn('id', $userIds)->where('role', 'Student')->get();
        foreach ($enrolledStudents as $enrolledUser) {
            Student::updateOrCreate(
                ['user_id' => $enrolledUser->id],
                [
                    'educational_center_id' => $center->id,
                    'course' => $enrolledUser->education_level ?? '',
                    'verified' => false,   // Must be verified by center before full access
                ]
            );
        }

        // For Teachers enrolled by the center, create/update their Teacher record with verified=false (pending verification)
        $enrolledTeachers = User::whereIn('id', $userIds)->where('role', 'Teacher')->get();
        foreach ($enrolledTeachers as $enrolledUser) {
            Teacher::updateOrCreate(
                ['user_id' => $enrolledUser->id],
                [
                    'educational_center_id' => $center->id,
                    'specialty' => $enrolledUser->education_level ?? '',
                    'verified' => false,   // Must be verified by center before full access
                ]
            );
        }

        return response()->json(['message' => 'Usuarios matriculados correctamente']);
    }

    public function apiEnrollCycles(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'cycle_ids' => 'required|array',
            'cycle_ids.*' => 'exists:cycles,id'
        ]);

        $center = EducationalCenter::findOrFail($user->educational_center_id);
        $center->cycles()->syncWithoutDetaching($validated['cycle_ids']);

        return response()->json(['message' => 'Ciclos vinculados correctamente']);
    }

    public function apiRemoveCycle(Request $request, $cycleId)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $center = EducationalCenter::findOrFail($user->educational_center_id);
        $center->cycles()->detach($cycleId);

        return response()->json(['message' => 'Ciclo desvinculado']);
    }

    public function apiAssignSubjectTeacher(Request $request, $groupId)
    {
        $user = $request->user();
        $group = Group::where('educational_center_id', $user->educational_center_id)->findOrFail($groupId);

        $validated = $request->validate([
            'tag_id' => 'required|exists:tags,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $group->subjectsWithTeachers()->detach($validated['tag_id']);
        $group->subjectsWithTeachers()->attach($validated['tag_id'], ['user_id' => $validated['user_id']]);

        return response()->json(['message' => 'Materia y profesor asignados']);
    }

    public function apiRemoveSubjectTeacher(Request $request, $groupId, $tagId)
    {
        $user = $request->user();
        $group = Group::where('educational_center_id', $user->educational_center_id)->findOrFail($groupId);
        $group->subjectsWithTeachers()->detach($tagId);
        return response()->json(['message' => 'Materia removida']);
    }

    public function apiTeachers(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json([]);
        }
        $teachers = User::where('educational_center_id', $user->educational_center_id)
                        ->where('role', 'Teacher')
                        ->with('teacher')
                        ->get()
                        ->filter(fn($u) => $u->is_verified)
                        ->values();
        return response()->json($teachers);
    }

    public function apiAdmins(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json([]);
        }
        $admins = User::where('educational_center_id', $user->educational_center_id)
                        ->whereIn('role', ['EI', 'Admin'])
                        ->get();
        return response()->json($admins);
    }

    public function apiStudents(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json([]);
        }
        // Only return students that have been verified by the center
        $students = User::where('educational_center_id', $user->educational_center_id)
                        ->where('role', 'Student')
                        ->with('student')
                        ->get()
                        ->filter(fn($u) => $u->is_verified)
                        ->values();
        return response()->json($students);
    }

    public function apiTutors(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json([]);
        }

        // Get all student IDs of this center
        $studentIds = User::where('educational_center_id', $user->educational_center_id)
            ->where('role', 'Student')
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            return response()->json([]);
        }

        // Get all unique tutors for these students
        $tutors = User::where('role', 'EU')
            ->whereHas('studentsOfTutor', function($query) use ($studentIds) {
                $query->whereIn('student_id', $studentIds);
            })
            ->get(['id', 'name', 'last_name', 'role', 'profile_picture', 'dni']);

        return response()->json($tutors);
    }

    /**
     * Returns students that belong to this center but are still pending verification.
     */
    public function apiPendingStudents(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json([], 403);
        }
        $pending = User::where('educational_center_id', $user->educational_center_id)
                        ->where('role', 'Student')
                        ->with('student')
                        ->get()
                        ->filter(fn($u) => !$u->is_verified)
                        ->values();
        return response()->json($pending);
    }

    /**
     * Marks a student as verified by the educational center.
     */
    public function apiVerifyStudent(Request $request, $userId)
    {
        $authUser = $request->user();
        if (!$authUser || !$authUser->educational_center_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $targetUser = User::where('id', $userId)
                          ->where('educational_center_id', $authUser->educational_center_id)
                          ->where('role', 'Student')
                          ->first();

        if (!$targetUser) {
            return response()->json(['message' => 'Usuario no encontrado o no pertenece a este centro'], 404);
        }

        // Usar updateOrCreate para asegurar que el registro existe y marcarlo como verificado
        $student = Student::updateOrCreate(
            ['user_id' => $targetUser->id],
            [
                'educational_center_id' => $authUser->educational_center_id,
                'course' => $targetUser->education_level ?? '',
                'verified' => true
            ]
        );

        // Refrescar el usuario para incluir is_verified actualizado
        $targetUser->refresh();
        $targetUser->load('student');

        return response()->json([
            'message' => 'Estudiante verificado correctamente',
            'user' => $targetUser,
        ]);
    }

    /**
     * Returns teachers that belong to this center but are still pending verification.
     */
    public function apiPendingTeachers(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json([], 403);
        }
        $pending = User::where('educational_center_id', $user->educational_center_id)
                        ->where('role', 'Teacher')
                        ->with('teacher')
                        ->get()
                        ->filter(fn($u) => !$u->is_verified)
                        ->values();
        return response()->json($pending);
    }

    /**
     * Marks a teacher as verified by the educational center.
     */
    public function apiVerifyTeacher(Request $request, $userId)
    {
        $authUser = $request->user();
        if (!$authUser || !$authUser->educational_center_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $targetUser = User::where('id', $userId)
                          ->where('educational_center_id', $authUser->educational_center_id)
                          ->where('role', 'Teacher')
                          ->first();

        if (!$targetUser) {
            return response()->json(['message' => 'Usuario no encontrado o no pertenece a este centro'], 404);
        }

        // Usar updateOrCreate para asegurar que el registro existe y marcarlo como verificado
        $teacher = Teacher::updateOrCreate(
            ['user_id' => $targetUser->id],
            [
                'educational_center_id' => $authUser->educational_center_id,
                'specialty' => $targetUser->education_level ?? '',
                'verified' => true
            ]
        );

        // Refrescar el usuario para incluir is_verified actualizado
        $targetUser->refresh();
        $targetUser->load('teacher');

        return response()->json([
            'message' => 'Profesor verificado correctamente',
            'user' => $targetUser,
        ]);
    }

    public function apiCycles(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json([]);
        }
        $center = EducationalCenter::with('cycles.tags')->find($user->educational_center_id);
        return response()->json($center ? $center->cycles : []);
    }

    public function apiEvents(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json([], 403);
        }
        // Devolvemos los eventos organizados por este centro
        $events = Event::where('educational_center_id', $user->educational_center_id)
                       ->orderBy('date', 'desc')
                       ->get();
        return response()->json($events);
    }

    public function apiStoreEvent(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'location'    => 'nullable|string|max:255',
            'date'        => 'required|date',
            'start_time'  => 'required',
            'end_time'    => 'required',
            'target_role' => 'nullable|string',
            'image'       => 'nullable',
        ]);

        $validated['educational_center_id'] = $user->educational_center_id;

        // Procesar imagen (Archivo o Base64)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = time() . '_image_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/events';
            $file->move(public_path($path), $imageName);
            $validated['image'] = '/' . $path . '/' . $imageName;
        } elseif ($request->filled('image') && str_starts_with($request->image, 'data:image')) {
            try {
                $base64Image = $request->image;
                $format = str_contains($base64Image, 'image/jpeg') ? 'jpg' : 'png';
                $image = str_replace(['data:image/jpeg;base64,', 'data:image/png;base64,', 'data:image/jpg;base64,', ' '], ['', '', '', '+'], $base64Image);
                $imageName = time() . '_image_' . rand(100, 999) . '.' . $format;
                $path = 'uploads/events';
                $fullPath = public_path($path);
                if (!file_exists($fullPath)) mkdir($fullPath, 0777, true);
                \File::put($fullPath . '/' . $imageName, base64_decode($image));
                $validated['image'] = '/' . $path . '/' . $imageName;
            } catch (\Exception $e) { \Log::error("Error Base64: " . $e->getMessage()); }
        }

        $event = Event::create($validated);

        return response()->json($event, 201);
    }

    public function apiUpdateEvent(Request $request, $eventId)
    {
        $user = $request->user();
        $event = Event::where('educational_center_id', $user->educational_center_id)->findOrFail($eventId);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'location'    => 'nullable|string|max:255',
            'date'        => 'required|date',
            'start_time'  => 'required',
            'end_time'    => 'required',
            'target_role' => 'nullable|string',
            'image'       => 'nullable',
        ]);

        // Procesar imagen (Archivo o Base64)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = time() . '_image_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/events';
            $file->move(public_path($path), $imageName);
            $validated['image'] = '/' . $path . '/' . $imageName;
        } elseif ($request->filled('image') && str_starts_with($request->image, 'data:image')) {
            try {
                $base64Image = $request->image;
                $format = str_contains($base64Image, 'image/jpeg') ? 'jpg' : 'png';
                $image = str_replace(['data:image/jpeg;base64,', 'data:image/png;base64,', 'data:image/jpg;base64,', ' '], ['', '', '', '+'], $base64Image);
                $imageName = time() . '_image_' . rand(100, 999) . '.' . $format;
                $path = 'uploads/events';
                $fullPath = public_path($path);
                if (!file_exists($fullPath)) mkdir($fullPath, 0777, true);
                \File::put($fullPath . '/' . $imageName, base64_decode($image));
                $validated['image'] = '/' . $path . '/' . $imageName;
            } catch (\Exception $e) { \Log::error("Error Base64: " . $e->getMessage()); }
        } elseif (!$request->filled('image')) {
            // Si no se envía una nueva imagen, mantenemos la que tiene
            unset($validated['image']);
        }

        $event->update($validated);

        return response()->json($event);
    }

    public function apiDeleteEvent(Request $request, $eventId)
    {
        $user = $request->user();
        $event = Event::where('educational_center_id', $user->educational_center_id)->findOrFail($eventId);
        $event->delete();
        return response()->json(['message' => 'Evento eliminado']);
    }

    /**
     * Devuelve todos los centros educativos sin paginación para evitar cortes en el formulario de registro y otros selectores.
     */
    public function apiIndex(Request $request)
    {
        $query = $this->model::query();

        if (!empty($this->with)) {
            $query->with($this->with);
        }

        if (!empty($this->withCount)) {
            $query->withCount($this->withCount);
        }

        $query = $this->extraFilters($query, $request);

        return response()->json($query->orderBy('name', 'asc')->get());
    }

    public function apiPublications(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json([], 403);
        }
        $publications = Publication::where('educational_center_id', $user->educational_center_id)
                                   ->orderBy('created_at', 'desc')
                                   ->get();
        return response()->json($publications);
    }

    public function apiStorePublication(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'nullable',
        ]);

        $validated['educational_center_id'] = $user->educational_center_id;

        // Procesar imagen (Archivo o Base64)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = time() . '_image_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/publications';
            $file->move(public_path($path), $imageName);
            $validated['image'] = '/' . $path . '/' . $imageName;
        } elseif ($request->filled('image') && str_starts_with($request->image, 'data:image')) {
            try {
                $base64Image = $request->image;
                $format = str_contains($base64Image, 'image/jpeg') ? 'jpg' : 'png';
                $image = str_replace(['data:image/jpeg;base64,', 'data:image/png;base64,', 'data:image/jpg;base64,', ' '], ['', '', '', '+'], $base64Image);
                $imageName = time() . '_image_' . rand(100, 999) . '.' . $format;
                $path = 'uploads/publications';
                $fullPath = public_path($path);
                if (!file_exists($fullPath)) mkdir($fullPath, 0777, true);
                \File::put($fullPath . '/' . $imageName, base64_decode($image));
                $validated['image'] = '/' . $path . '/' . $imageName;
            } catch (\Exception $e) { \Log::error("Error Base64: " . $e->getMessage()); }
        }

        $publication = Publication::create($validated);

        return response()->json($publication, 201);
    }

    public function apiUpdatePublication(Request $request, $publicationId)
    {
        $user = $request->user();
        $publication = Publication::where('educational_center_id', $user->educational_center_id)->findOrFail($publicationId);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'nullable',
        ]);

        // Procesar imagen (Archivo o Base64)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = time() . '_image_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/publications';
            $file->move(public_path($path), $imageName);
            $validated['image'] = '/' . $path . '/' . $imageName;
        } elseif ($request->filled('image') && str_starts_with($request->image, 'data:image')) {
            try {
                $base64Image = $request->image;
                $format = str_contains($base64Image, 'image/jpeg') ? 'jpg' : 'png';
                $image = str_replace(['data:image/jpeg;base64,', 'data:image/png;base64,', 'data:image/jpg;base64,', ' '], ['', '', '', '+'], $base64Image);
                $imageName = time() . '_image_' . rand(100, 999) . '.' . $format;
                $path = 'uploads/publications';
                $fullPath = public_path($path);
                if (!file_exists($fullPath)) mkdir($fullPath, 0777, true);
                \File::put($fullPath . '/' . $imageName, base64_decode($image));
                $validated['image'] = '/' . $path . '/' . $imageName;
            } catch (\Exception $e) { \Log::error("Error Base64: " . $e->getMessage()); }
        } elseif (!$request->filled('image')) {
            unset($validated['image']);
        }

        $publication->update($validated);

        return response()->json($publication);
    }

    public function apiDeletePublication(Request $request, $publicationId)
    {
        $user = $request->user();
        $publication = Publication::where('educational_center_id', $user->educational_center_id)->findOrFail($publicationId);
        $publication->delete();
        return response()->json(['message' => 'Publicación eliminada']);
    }
}

