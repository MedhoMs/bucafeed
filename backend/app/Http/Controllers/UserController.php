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
         $users = User::all();
         return view('users.index', [
             'users' => clone $users,
             'roles_disponibles' => Rol::all()->mapWithKeys(function ($r) {
                 return [$r->code ?? $r->name => $r->name];
             })->toArray(),
             'niveles_disponibles' => EducationalCenter::$niveles_disponibles
         ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $data = ['exito' => ''];
        $user = new User();
        
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
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
                        'roles' => Rol::all(), 
                        'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray(),
                        'education_levels' => EducationalCenter::$niveles_disponibles,
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
            $user->educational_center_id = $request->input('educational_center_id');
            $user->description      = $request->input('description');

            $user->save();   
            
            $data['exito'] = 'Operación realizada correctamente';
        }

        if ($request->ajax()) 
        {
            return view('users.create', [
                'datos' => $data,
                'user' => $user,
                'roles' => Rol::all(), 
                'roles_disponibles' => Rol::all()->mapWithKeys(function ($r) {
                    return [$r->code ?? $r->name => $r->name];
                })->toArray(),
                'education_levels' => EducationalCenter::$niveles_disponibles,
                'disabled' => '',
                'oper' => 'create'
            ]); 
        }

        $user = new User();

        return view('users.create',['datos' => $data,'user' => $user,'roles' => Rol::all(), 'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray(), 'education_levels' => EducationalCenter::$niveles_disponibles, 'disabled' => '','oper' => 'create']);
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
            'roles' => Rol::all(), 
            'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray(),
            'education_levels' => EducationalCenter::$niveles_disponibles,
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
            $validator = Validator::make($request->all(), [
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
                        'roles' => Rol::all(), 
                        'roles_disponibles' => Rol::all()->mapWithKeys(function ($r) {
                            return [$r->code ?? $r->name => $r->name];
                        })->toArray(),
                        'education_levels' => EducationalCenter::$niveles_disponibles,
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
            $user->educational_center_id = $request->input('educational_center_id');
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
                    'roles' => Rol::all(), 
                    'roles_disponibles' => Rol::all()->mapWithKeys(function ($r) {
                        return [$r->code ?? $r->name => $r->name];
                    })->toArray(),
                    'education_levels' => EducationalCenter::$niveles_disponibles,
                    'disabled' => $disabled,
                    'oper' => 'edit' 
                ]); 
            }
        }

        return view('users.create', [
            'user' => $user,
            'datos' => $datos,
            'roles' => Rol::all(), 
            'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray(),
            'education_levels' => EducationalCenter::$niveles_disponibles,
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

            $data = ['exito' => 'Usuario eliminado correctamente'];

            if ($request->ajax()) {
                // Devolver Vista con éxito para que se cierre/actualice
                return view('users.create', [
                    'user' => $user,
                    'datos' => $data,
                    'roles' => Rol::all(), 
                    'roles_disponibles' => Rol::all()->mapWithKeys(function ($r) {
                        return [$r->code ?? $r->name => $r->name];
                    })->toArray(),
                    'education_levels' => EducationalCenter::$niveles_disponibles,
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
            'roles' => Rol::all(), 
            'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray(),
            'education_levels' => EducationalCenter::$niveles_disponibles,
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

}
