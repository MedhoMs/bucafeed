<?php

namespace App\Http\Controllers;

use App\Models\EducationalCenter;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
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
                    return view('educational_centers.create', compact('center', 'datos', 'adminUsers', 'disabled'))->with('oper', 'create')->withErrors($validator);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $cycles = $request->filled('cycles') 
                ? array_filter(array_map('trim', explode(',', $request->input('cycles')))) 
                : [];

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
            
            if ($request->ajax()) {
                return view('educational_centers.create', compact('center', 'datos', 'adminUsers', 'disabled'))->with('oper', 'edit');
            }
        }

        return view('educational_centers.create', compact('center', 'datos', 'adminUsers', 'disabled'))->with('oper', 'create');
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
                    return view('educational_centers.create', compact('center', 'datos', 'adminUsers', 'disabled'))->with('oper', 'edit')->withErrors($validator);
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
                // Remove center from the previous admin if any
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
            
            if ($request->ajax()) {
                return view('educational_centers.create', compact('center', 'datos', 'adminUsers', 'disabled'))->with('oper', 'edit');
            }
        }

        return view('educational_centers.create', compact('center', 'datos', 'adminUsers', 'disabled'))->with('oper', 'edit');
    }

    public function destroy(Request $request, $id)
    {
        $center = EducationalCenter::findOrFail($id);
        $datos = ['exito' => ''];
        $disabled = 'disabled';
        $adminUsers = [];

        if ($request->isMethod('post')) {
            // Nullify educational_center_id so we don't end up with constraint errors depending on cascade setup
            User::where('educational_center_id', $center->id)->update(['educational_center_id' => null]);
            $center->delete();
            $datos['exito'] = 'Centro eliminado correctamente.';
            
            if ($request->ajax()) {
                $center = new EducationalCenter();
                return view('educational_centers.create', compact('center', 'datos', 'adminUsers', 'disabled'))->with('oper', 'destroy');
            }
            return redirect()->route('educational_centers.index');
        }

        return view('educational_centers.create', compact('center', 'datos', 'adminUsers', 'disabled'))->with('oper', 'destroy');
    }

    public function show($id)
    {
        $center = EducationalCenter::with(['teachers', 'students', 'adminUser'])->findOrFail($id);
        $datos = ['exito' => ''];
        $disabled = 'disabled';
        $adminUsers = User::where('role', 'EI')->get()->mapWithKeys(function($user) {
            return [$user->id => trim($user->name . ' ' . $user->last_name) . ' (' . $user->email . ')'];
        })->toArray();

        return view('educational_centers.create', compact('center', 'datos', 'adminUsers', 'disabled'))->with('oper', 'show');
    }

    public function manageCycles($id)
    {
        $center = EducationalCenter::with('cycles')->findOrFail($id);
        $globalCycles = \App\Models\Cycle::orderBy('name')->get();
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
            $cycle = \App\Models\Cycle::firstOrCreate(['name' => $newCycleName]);
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

    public function apiIndex(Request $request)
    {
        $query = EducationalCenter::select('id', 'name', 'type')->orderBy('name');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->get());
    }
}


