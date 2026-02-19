<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $role = $request->input('role');

        //Validación base (siempre obligatoria)
        $rules = [
            'name'        => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8',
            'role'        => 'required|string|in:EU,Student,Teacher,EI',
            'dni' => 'required|string|max:20|unique:users,dni',
        ];

        //Validaciones adicionales según el rol
        if (in_array($role, ['Student', 'Teacher', 'EI'])) {
            $rules['institution_name'] = 'required|string|max:255';
        }

        if (in_array($role, ['Student', 'Teacher'])) {
            $rules['education_level'] = 'required';
        }

        try {
            $validated = $request->validate($rules);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = User::create([
            'name'                  => $validated['name'],
            'last_name'             => $validated['last_name'],
            'email'                 => $validated['email'],
            'password'              => Hash::make($validated['password']),
            'role'                  => $validated['role'],
            'dni'                   => $validated['dni'],
            //'educational_center_id' => $validated['educational_center_id'] ?? null,
            'education_level'       => $validated['education_level'] ?? null,
            'institution_name'      => $validated['institution_name'] ?? null,
        ]);

        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Usuario registrado correctamente',
            'user'    => $user,
        ], 201);
    }
}
