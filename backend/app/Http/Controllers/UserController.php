<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol;
use App\Models\EducationalCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         $query = User::query();

         if ($request->filled('search')) {
             $search = $request->input('search');
             $query->where(function($q) use ($search) {
                 $q->where('name', 'like', "%$search%")
                   ->orWhere('last_name', 'like', "%$search%")
                   ->orWhere('email', 'like', "%$search%")
                   ->orWhere('dni', 'like', "%$search%");
             });
         }

         if ($request->filled('role')) {
             $query->where('role', $request->role);
         }

         if ($request->filled('institution')) {
             $query->where('institution_name', $request->institution);
         }

         if ($request->filled('level')) {
             $query->where('education_level', $request->level);
         }

         $users = $query->paginate(10);

         return view('users.index', [
             'users' => $users,
             'roles_disponibles' => Rol::all()->mapWithKeys(function ($r) {
                 return [$r->code ?? $r->name => $r->name];
             })->toArray(),
             'niveles_disponibles' => EducationalCenter::$niveles_disponibles,
             'instituciones_existentes' => array_combine($this->getInstitucionesExistentes(), $this->getInstitucionesExistentes())
         ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $data = ['exito' => ''];
        $user = new User();
        
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name'            => 'required|string|max:255',
                'last_name'       => 'required|string|max:255',
                'email'           => 'required|string|email|max:255|unique:users',
                'password'        => 'required|string|min:8',
                'dni'             => 'required|string|max:20|unique:users',
                'role'            => 'required|string',
                'education_level' => 'nullable|string',
                'institution_name'=> 'nullable|string',
                'description'     => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return view('users.create', [
                        'datos' => $data,
                        'user' => $user,
                        'fields' => $this->getUserFields($user),
                        'disabled' => '',
                        'oper' => 'create'
                    ])->withErrors($validator);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $user->name             = $request->input('name');
            $user->last_name        = $request->input('last_name');
            $user->email            = $request->input('email');
            $user->password         = Hash::make($request->input('password'));
            $user->dni              = $request->input('dni');
            $user->role             = $request->input('role');
            $user->education_level  = $request->input('education_level');
            $user->institution_name = $request->input('institution_name');
            $user->description      = $request->input('description');

            $user->save();   
            
            $data['exito'] = 'Operación realizada correctamente';
        }

        return view('users.create', [
            'datos' => $data,
            'user' => $user,
            'fields' => $this->getUserFields($user),
            'disabled' => '',
            'oper' => 'create'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        $datos = ['exito' => ''];

        return view('users.create',[
            'user' => $user,
            'datos' => $datos,
            'fields' => $this->getUserFields($user),
            'disabled' => 'disabled',
            'oper' => 'show'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $disabled = '';
        $datos['exito'] = '';

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name'            => 'required|string|max:255',
                'last_name'       => 'required|string|max:255',
                'email'           => 'required|string|email|max:255|unique:users,email,'.$user->id,
                'dni'             => 'required|string|max:20|unique:users,dni,'.$user->id,
                'role'            => 'required|string',
                'education_level' => 'nullable|string',
                'institution_name'=> 'nullable|string',
                'description'     => 'nullable|string|max:1000',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'banner'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return view('users.create', [
                        'datos' => $datos,
                        'user' => $user,
                        'fields' => $this->getUserFields($user),
                        'disabled' => $disabled,
                        'oper' => 'edit'
                    ])->withErrors($validator);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $user->name             = $request->input('name');
            $user->last_name        = $request->input('last_name');
            $user->email            = $request->input('email');
            if($request->filled('password')){
                 $user->password = Hash::make($request->input('password'));
            }
            $user->dni              = $request->input('dni');
            $user->role             = $request->input('role');
            $user->education_level  = $request->input('education_level');
            $user->institution_name = $request->input('institution_name');
            $user->description      = $request->input('description');

            // Handle Profile Picture
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filename = time() . '_pfp_' . $user->id . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/profiles'), $filename);
                $user->profile_picture = '/uploads/profiles/' . $filename;
            }

            // Handle Banner
            if ($request->hasFile('banner')) {
                $file = $request->file('banner');
                $filename = time() . '_banner_' . $user->id . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/banners'), $filename);
                $user->banner = '/uploads/banners/' . $filename;
            }
            
            $user->save();

            $datos['exito'] = 'Operación realizada correctamente';
            $disabled = 'disabled';

            // Si es AJAX, devolvemos Vista
            if ($request->ajax()) 
            {
                return view('users.create', [
                    'datos' => $datos,
                    'user' => $user,
                    'fields' => $this->getUserFields($user),
                    'disabled' => $disabled,
                    'oper' => 'edit' 
                ]); 
            }
        }

        return view('users.create', [
            'user' => $user,
            'datos' => $datos,
            'fields' => $this->getUserFields($user),
            'disabled' => $disabled,
            'oper' => 'edit'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(request $request,string $id='')
    {
        $user = User::findOrFail($id);

        if (!$user) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            };
        }

        if ($request->isMethod('post')) {
            $user->delete();

            $datos = ['exito' => 'Usuario eliminado correctamente'];
            $disabled = 'disabled';

            if ($request->ajax()) 
            {
                return view('users.create', [
                    'user' => $user,
                    'datos' => $data,
                    'fields' => $this->getUserFields($user),
                    'disabled' => 'disabled',
                    'oper' => 'destroy'
                ]);
            }

            return redirect()->route('user.index');
        }

        // GET: mostrar confirmación en modal
        $datos = ['exito' => ''];
        $disabled = 'disabled';
        return view('users.create', [
            'user' => $user,
            'datos' => $datos,
            'fields' => $this->getUserFields($user),
            'disabled' => $disabled,
            'oper' => 'destroy'
        ]);
    }

    /**
     * Admin: Show profile modal.
     */
    public function profileModal($id)
    {
        $user = User::findOrFail($id);
        return view('users.profile_modal', compact('user'));
    }

    /**
     * Define los campos para el formulario de usuarios.
     * Esta es la "plantilla" que centraliza la estructura.
     */
    protected function getUserFields($user = null)
    {
        $roles = Rol::all();
        $roles_disponibles = $roles->pluck('name', 'code')->toArray();
        $education_levels = EducationalCenter::$niveles_disponibles;
        $instituciones_existentes = $this->getInstitucionesExistentes();

        $roleOptions = [];
        foreach($roles as $rolDb) {
            $roleOptions[$rolDb->code ?? $rolDb->name] = $roles_disponibles[$rolDb->code] ?? $rolDb->name;
        }

        return [
            ['name' => 'name', 'label' => 'Nombre', 'placeholder' => 'Ej: Juan', 'value' => old('name', $user->name ?? ''), 'required' => true],
            ['name' => 'last_name', 'label' => 'Apellidos', 'placeholder' => 'Ej: Pérez García', 'value' => old('last_name', $user->last_name ?? ''), 'required' => true],
            ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'placeholder' => 'juan@ejemplo.com', 'value' => old('email', $user->email ?? ''), 'required' => true],
            ['name' => 'password', 'type' => 'password', 'label' => 'Contraseña', 'placeholder' => '********', 'required' => !$user?->id],
            ['name' => 'dni', 'label' => 'DNI/NIE', 'placeholder' => '12345678A', 'value' => old('dni', $user->dni ?? ''), 'required' => true],
            ['name' => 'role', 'type' => 'select', 'label' => 'Rol', 'options' => $roleOptions, 'selectedValue' => old('role', $user->role ?? ''), 'placeholder' => 'Seleccionar rol...', 'required' => true],
            ['name' => 'education_level', 'type' => 'select', 'label' => 'Nivel Educativo', 'options' => $education_levels, 'selectedValue' => old('education_level', $user->education_level ?? ''), 'placeholder' => 'Seleccionar...'],
            ['name' => 'institution_name', 'type' => 'select', 'label' => 'Institución', 'options' => ['' => '-- Ninguna --'] + array_combine($instituciones_existentes, $instituciones_existentes), 'selectedValue' => old('institution_name', $user->institution_name ?? ''), 'placeholder' => 'Seleccionar institución...'],
            ['name' => 'description', 'type' => 'textarea', 'label' => 'Descripción / Biografía', 'placeholder' => 'Cuenta algo sobre el usuario...', 'value' => old('description', $user->description ?? ''), 'full' => true]
        ];
    }

    /**
     * Obtiene la lista de instituciones (nombres manuales + centros educativos).
     */
    protected function getInstitucionesExistentes()
    {
        return EducationalCenter::pluck('name')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
}
