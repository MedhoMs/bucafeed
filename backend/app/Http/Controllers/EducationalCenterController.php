<?php

namespace App\Http\Controllers;

use App\Models\EducationalCenter;
use App\Models\User;
use App\Models\Cycle;
use App\Models\Group;
use App\Models\Rol;
use App\Models\Tag;

use Illuminate\Http\Request;

class EducationalCenterController extends TemplateController
{
    protected $model = EducationalCenter::class;
    protected $viewPath = 'educational_centers';
    protected $with = ['adminUser', 'students', 'teachers', 'cycles'];
    protected $withCount = ['students', 'teachers'];

    protected function extraFilters($query, Request $request)
    {
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
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

        $niveles = EducationalCenter::$niveles_disponibles;
        
        return [
            'locations' => $locations,
            'types' => $types,
            'roles_disponibles' => Rol::pluck('name', 'code')->toArray(),
            'niveles_disponibles' => $niveles,
            'ciclos_disponibles' => Cycle::orderBy('name')->pluck('name', 'id')->toArray()
        ];
    }

    protected function getFormFields($center = null)
    {
        $adminUsers = User::where('role', 'EI')->get()->mapWithKeys(function($user) {
            return [$user->id => trim($user->name . ' ' . $user->last_name) . ' (' . $user->email . ')'];
        })->toArray();
        $adminOptions = ['' => '-- Sin Administrador --'] + $adminUsers;

        return [
            ['name' => 'name', 'label' => 'Nombre del Centro', 'placeholder' => 'Ej: IES Zonzamas', 'value' => old('name', $center->name ?? ''), 'required' => true],
            ['name' => 'category', 'type' => 'select', 'label' => 'Categoría del Centro', 'options' => ['CEIP' => 'Colegio (CEIP)', 'IES' => 'Instituto (IES)', 'CIFP' => 'Centro de FP (CIFP)', 'CEO' => 'Centro Obligatoria (CEO)', 'UR' => 'Universidad'], 'selectedValue' => old('category', $center->category ?? ''), 'placeholder' => 'Selecciona tipo...'],
            ['name' => 'admin_user_id', 'type' => 'select', 'label' => 'Administrador Principal (EI)', 'options' => $adminOptions, 'selectedValue' => old('admin_user_id', $center->admin_user_id ?? ''), 'placeholder' => 'Selecciona al responsable'],
            ['name' => 'location', 'label' => 'Ubicación / Municipio', 'placeholder' => 'Ej: Arrecife', 'value' => old('location', $center->location ?? '')],
            ['name' => 'type', 'type' => 'select', 'label' => 'Tipo de Educación', 'options' => EducationalCenter::$niveles_disponibles, 'selectedValue' => old('type', $center->type ?? ''), 'placeholder' => 'Selecciona el nivel...'],
            ['name' => 'icon', 'type' => 'file', 'label' => 'Logo / Icono', 'previewUrl' => $center->icon ?? null, 'full' => true],
            ['name' => 'banner', 'type' => 'file', 'label' => 'Imagen de Banner', 'previewUrl' => $center->banner ?? null, 'full' => true]
        ];
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

    public function show($id)
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
        $availableStudents = User::where('role', 'Student')
            ->where(function($q) use ($id) {
                $q->whereNull('educational_center_id')->orWhere('educational_center_id', '!=', $id);
            })->orderBy('name')->get();
            
        $availableTeachers = User::where('role', 'Teacher')
            ->where(function($q) use ($id) {
                $q->whereNull('educational_center_id')->orWhere('educational_center_id', '!=', $id);
            })->orderBy('name')->get();

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
        $center = EducationalCenter::withCount(['students', 'teachers', 'groups'])->find($user->educational_center_id);
        return response()->json($center);
    }

    public function apiGroups(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->educational_center_id) {
            return response()->json([], 403);
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
            'cycle_id' => $validated['cycle_id'],
            'tutor_id' => $validated['tutor_id'],
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

        $group->update($validated);
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
            'tutor_id' => 'nullable|exists:users,id'
        ]);
        
        $group->update(['tutor_id' => $validated['tutor_id']]);
        return response()->json(['message' => 'Tutor asignado']);
    }

    public function apiAssignSubjectTeacher(Request $request, $groupId)
    {
        $user = $request->user();
        $group = Group::where('educational_center_id', $user->educational_center_id)->findOrFail($groupId);
        
        $validated = $request->validate([
            'tag_id' => 'required|exists:tags,id',
            'teacher_id' => 'required|exists:users,id'
        ]);
        
        $group->subjectsWithTeachers()->detach($validated['tag_id']);
        $group->subjectsWithTeachers()->attach($validated['tag_id'], ['user_id' => $validated['teacher_id']]);
        
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
                        ->get();
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
        $students = User::where('educational_center_id', $user->educational_center_id)
                        ->where('role', 'Student')
                        ->get();
        return response()->json($students);
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
}
