<?php

namespace App\Http\Controllers;

use App\Models\EducationalCenter;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Cycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class EducationalCenterController extends Controller
{
    public function index(Request $request)
    {
        $query = EducationalCenter::with(['adminUser', 'students', 'teachers', 'cycles']);
        
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%$search%");
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $centers = $query->paginate(10);
        
        $locations = EducationalCenter::whereNotNull('location')->where('location', '!=', 'N/A')->pluck('location', 'location')->unique()->sort()->toArray();
        $types_raw = EducationalCenter::whereNotNull('type')->where('type', '!=', 'N/A')->pluck('type')->unique()->toArray();
        $types = [];
        foreach($types_raw as $t) {
            $types[$t] = EducationalCenter::$niveles_disponibles[$t] ?? $t;
        }

        return view('educational_centers.index', [
            'centers' => $centers,
            'locations' => $locations,
            'types' => $types,
        ]);
    }

    public function create(Request $request)
    {
        $center = new EducationalCenter();
        $datos = ['exito' => ''];
        $disabled = '';
        
        $adminUsers = User::where('role', 'EI')->get()->mapWithKeys(function($user) {
            return [$user->id => trim($user->name . ' ' . $user->last_name) . ' (' . $user->email . ')'];
        })->toArray();

        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'admin_user_id' => 'nullable|exists:users,id',
                'cycles' => 'nullable|string',
                'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return view('educational_centers.create', [
                        'center' => $center,
                        'datos' => $datos,
                        'fields' => $this->getCenterFields($center, $adminUsers),
                        'disabled' => $disabled,
                        'oper' => 'create'
                    ])->withErrors($validator);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $center->name = $request->input('name');
            $center->type = $request->input('type', 'N/A');
            $center->location = $request->input('location', 'N/A');

            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $filename = time() . '_icon_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/centers/icons'), $filename);
                $center->icon = '/uploads/centers/icons/' . $filename;
            }

            if ($request->hasFile('banner')) {
                $file = $request->file('banner');
                $filename = time() . '_banner_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/centers/banners'), $filename);
                $center->banner = '/uploads/centers/banners/' . $filename;
            }

            $center->save();

            if ($request->filled('admin_user_id')) {
                User::where('educational_center_id', $center->id)->where('role', 'EI')->update(['educational_center_id' => null]);
                $adminUser = User::find($request->input('admin_user_id'));
                if ($adminUser) {
                    $adminUser->educational_center_id = $center->id;
                    $adminUser->save();
                }
            }

            $datos['exito'] = 'Centro Educativo creado correctamente.';
        }

        return view('educational_centers.create', [
            'center' => $center,
            'datos' => $datos,
            'fields' => $this->getCenterFields($center, $adminUsers),
            'disabled' => $disabled,
            'oper' => 'create'
        ]);
    }

    public function edit(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);
        $datos = ['exito' => ''];
        $disabled = '';
        
        $adminUsers = User::where('role', 'EI')->get()->mapWithKeys(function($user) {
            return [$user->id => trim($user->name . ' ' . $user->last_name) . ' (' . $user->email . ')'];
        })->toArray();

        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'admin_user_id' => 'nullable|exists:users,id',
                'location' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return view('educational_centers.create', [
                        'center' => $center,
                        'datos' => $datos,
                        'fields' => $this->getCenterFields($center, $adminUsers),
                        'disabled' => $disabled,
                        'oper' => 'edit'
                    ])->withErrors($validator);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $center->name = $request->input('name');
            $center->location = $request->input('location');
            $center->type = $request->input('type');

            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $filename = time() . '_icon_' . $center->id . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/centers/icons'), $filename);
                $center->icon = '/uploads/centers/icons/' . $filename;
            }

            if ($request->hasFile('banner')) {
                $file = $request->file('banner');
                $filename = time() . '_banner_' . $center->id . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/centers/banners'), $filename);
                $center->banner = '/uploads/centers/banners/' . $filename;
            }

            $center->save();

            if ($request->has('admin_user_id')) {
                $adminId = $request->input('admin_user_id');
                User::where('educational_center_id', $center->id)->where('role', 'EI')->update(['educational_center_id' => null]);
                if ($request->filled('admin_user_id')) {
                    $adminUser = User::find($adminId);
                    if ($adminUser) {
                        $adminUser->educational_center_id = $center->id;
                        $adminUser->save();
                    }
                }
            }

            $datos['exito'] = 'Centro Educativo actualizado correctamente.';
            $disabled = 'disabled';
        }

        return view('educational_centers.create', [
            'center' => $center,
            'datos' => $datos,
            'fields' => $this->getCenterFields($center, $adminUsers),
            'disabled' => $disabled,
            'oper' => 'edit'
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);
        $datos = ['exito' => ''];
        $disabled = 'disabled';

        if ($request->isMethod('post')) {
            User::where('educational_center_id', $center->id)->update(['educational_center_id' => null]);
            $center->delete();
            $datos['exito'] = 'Centro eliminado correctamente.';
            
            if ($request->ajax()) {
                return view('educational_centers.create', [
                    'center' => $center,
                    'datos' => $datos,
                    'fields' => $this->getCenterFields($center),
                    'disabled' => $disabled,
                    'oper' => 'destroy'
                ]);
            }
            return redirect()->route('educational_centers.index');
        }

        return view('educational_centers.create', [
            'center' => $center,
            'datos' => $datos,
            'fields' => $this->getCenterFields($center),
            'disabled' => $disabled,
            'oper' => 'destroy'
        ]);
    }

    public function show($id)
    {
        $center = EducationalCenter::with(['teachers', 'students', 'adminUser'])->findOrFail($id);
        $datos = ['exito' => ''];
        $disabled = 'disabled';
        
        $adminUsers = User::where('role', 'EI')->get()->mapWithKeys(function($user) {
            return [$user->id => trim($user->name . ' ' . $user->last_name) . ' (' . $user->email . ')'];
        })->toArray();

        return view('educational_centers.create', [
            'center' => $center,
            'datos' => $datos,
            'fields' => $this->getCenterFields($center, $adminUsers),
            'disabled' => $disabled,
            'oper' => 'show'
        ]);
    }

    /**
     * Define los campos para el formulario de centros educativos.
     */
    protected function getCenterFields($center = null, $adminUsers = [])
    {
        $adminOptions = ['' => '-- Sin Administrador --'] + $adminUsers;
        
        return [
            ['name' => 'name', 'label' => 'Nombre del Centro', 'placeholder' => 'Ej: IES Zonzamas', 'value' => old('name', $center->name ?? ''), 'required' => true],
            ['name' => 'admin_user_id', 'type' => 'select', 'label' => 'Administrador Principal (EI)', 'options' => $adminOptions, 'selectedValue' => old('admin_user_id', $center->adminUser ? $center->adminUser->id : ''), 'placeholder' => 'Selecciona al responsable'],
            ['name' => 'location', 'label' => 'Ubicación / Municipio', 'placeholder' => 'Ej: Arrecife', 'value' => old('location', $center->location ?? '')],
            ['name' => 'type', 'type' => 'select', 'label' => 'Tipo de Educación', 'options' => EducationalCenter::$niveles_disponibles, 'selectedValue' => old('type', $center->type ?? ''), 'placeholder' => 'Selecciona el nivel...'],
            ['name' => 'icon', 'type' => 'file', 'label' => 'Logo / Icono', 'previewUrl' => $center->icon ?? null],
            ['name' => 'banner', 'type' => 'file', 'label' => 'Imagen de Banner', 'previewUrl' => $center->banner ?? null]
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
        $cycleId = $request->input('cycle_id');
        $newCycleName = trim($request->input('new_cycle'));
        
        if ($cycleId) {
            $center->cycles()->syncWithoutDetaching([$cycleId]);
        } elseif ($newCycleName) {
            $cycle = Cycle::firstOrCreate(['name' => $newCycleName]);
            $center->cycles()->syncWithoutDetaching([$cycle->id]);
        }

        if ($request->ajax()) {
            return $this->manageCycles($id);
        }
        return back();
    }

    public function removeCycle(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);
        $cycleId = $request->input('cycle_id');
        
        if ($cycleId) {
            $center->cycles()->detach($cycleId);
        }

        if ($request->ajax()) {
            return $this->manageCycles($id);
        }
        return back();
    }

    public function assignStudent(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:users,id',
            'teacher_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $student = User::where('educational_center_id', $center->id)->where('role', 'Student')->findOrFail($request->student_id);
        $teacher = User::where('educational_center_id', $center->id)->where('role', 'Teacher')->findOrFail($request->teacher_id);

        $student->assignedTeachers()->syncWithoutDetaching([$teacher->id]);

        if ($request->ajax()) {
            return $this->show($id);
        }

        return back()->with('success', 'Alumno asignado correctamente al profesor.');
    }

    public function addUsers($id)
    {
        $center = EducationalCenter::findOrFail($id);
        
        $availableStudents = User::where('role', 'Student')->where(function($q) use($center) {
            $q->whereNull('educational_center_id')->orWhere('educational_center_id', '!=', $center->id);
        })->get();
        
        $availableTeachers = User::where('role', 'Teacher')->where(function($q) use($center) {
            $q->whereNull('educational_center_id')->orWhere('educational_center_id', '!=', $center->id);
        })->get();

        return view('educational_centers.add_users', compact('center', 'availableStudents', 'availableTeachers'));
    }

    public function storeUsers(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);
        
        if ($request->has('students') && is_array($request->students)) {
            User::whereIn('id', $request->students)->update(['educational_center_id' => $center->id]);
        }
        
        if ($request->has('teachers') && is_array($request->teachers)) {
            User::whereIn('id', $request->teachers)->update(['educational_center_id' => $center->id]);
        }
        
        if ($request->ajax()) {
            return $this->addUsers($id);
        }
        
        return back()->with('success', 'Usuarios matriculados en el centro correctamente.');
    }

    public function listUsersModal($id, $role)
    {
        $center = EducationalCenter::findOrFail($id);
        $users = User::where('educational_center_id', $id)
            ->where('role', $role)
            ->orderBy('name')
            ->get();
            
        $title = $role === 'Student' ? 'Alumnos de ' . $center->name : 'Docentes de ' . $center->name;
        
        return view('educational_centers.users_modal', compact('center', 'users', 'title', 'role'));
    }

    public function profileModal($id)
    {
        $center = EducationalCenter::with(['adminUser', 'students', 'teachers', 'cycles'])->findOrFail($id);
        return view('educational_centers.profile_modal', compact('center'));
    }

    public function manageGroups($id)
    {
        $center = EducationalCenter::with(['groups.tutor', 'groups.cycle', 'groups.students', 'groups.subjectsWithTeachers', 'teachers', 'students', 'cycles.tags'])->findOrFail($id);
        
        // Asignaturas sugeridas según el tipo de centro si no tiene ciclos
        $suggestedTags = collect([]);
        if ($center->type === 'PE') {
            $suggestedTags = \App\Models\Tag::whereIn('name', ['Lengua Castellana', 'Matemáticas', 'Ciencias Naturales', 'Ciencias Sociales', 'Inglés', 'Educación Física', 'Plástica', 'Religión/Valores'])->get();
        } elseif ($center->type === 'SE') {
            $suggestedTags = \App\Models\Tag::whereIn('name', ['Geografía e Historia', 'Física y Química', 'Biología y Geología', 'Matemáticas', 'Lengua Castellana', 'Inglés', 'Tecnología', 'Educación Física'])->get();
        }

        $allTags = \App\Models\Tag::orderBy('name')->get();
        
        return view('educational_centers.manage_groups', compact('center', 'suggestedTags', 'allTags'));
    }

    public function storeGroup(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);
        
        \Log::info('Group Store Attempt at center ' . $id, $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cycle_id' => 'nullable|exists:cycles,id',
            'tutor_id' => 'nullable|exists:users,id',
            'students' => 'nullable|array',
            'students.*' => 'exists:users,id',
            'teachers' => 'nullable|array', // tag_id => teacher_id
        ]);

        $group = $center->groups()->create([
            'name' => $validated['name'],
            'cycle_id' => $validated['cycle_id'],
            'tutor_id' => $validated['tutor_id'],
        ]);

        if (!empty($validated['students'])) {
            $group->students()->attach($validated['students']);
        }

        if (!empty($validated['teachers'])) {
            foreach ($validated['teachers'] as $tagId => $teacherId) {
                if ($teacherId) {
                    $group->subjectsWithTeachers()->attach($tagId, ['user_id' => $teacherId]);
                }
            }
        }

        if ($request->ajax()) {
            return $this->manageGroups($id);
        }
        
        return back()->with('success', 'Grupo creado correctamente.');
    }

    public function deleteGroup(Request $request, $id, $groupId)
    {
        $group = \App\Models\Group::where('educational_center_id', $id)->findOrFail($groupId);
        $group->delete();

        if ($request->ajax()) {
            return $this->manageGroups($id);
        }
        return back();
    }

    public function editGroup($id, $groupId)
    {
        $group = \App\Models\Group::with(['students', 'subjectsWithTeachers'])->where('educational_center_id', $id)->findOrFail($groupId);
        return response()->json($group);
    }

    public function groupDetailsModal($id, $groupId)
    {
        $center = EducationalCenter::findOrFail($id);
        $group = \App\Models\Group::with(['students', 'subjectsWithTeachers', 'tutor', 'cycle'])->where('educational_center_id', $id)->findOrFail($groupId);
        
        return view('educational_centers.group_details_modal', compact('center', 'group'));
    }

    public function updateGroup(Request $request, $id, $groupId)
    {
        $group = \App\Models\Group::where('educational_center_id', $id)->findOrFail($groupId);
        
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

        $group->students()->sync($validated['students'] ?? []);

        // Actualizar materias y profesores
        $syncData = [];
        if (!empty($validated['teachers'])) {
            foreach ($validated['teachers'] as $tagId => $teacherId) {
                if ($teacherId) {
                    $syncData[$tagId] = ['user_id' => $teacherId];
                }
            }
        }
        $group->subjectsWithTeachers()->sync($syncData);

        if ($request->ajax()) {
            return $this->manageGroups($id);
        }
        return back();
    }

    public function apiIndex(Request $request)
    {
        $query = EducationalCenter::select('id', 'name', 'type')->orderBy('name');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->get());
    }
}


