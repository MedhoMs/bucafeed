<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Genera un codigo de 6 digitos, lo guarda en cache junto al payload
     * del formulario y envia el email al usuario.
     */
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        //Codigo aleatorio de 6 digitos
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        //Guardo el codigo y el payload completo en cache durante 10 minutos
        //La clave se basa en el email para poder recuperarlo luego
        Cache::put('verification_code_' . $request->email, $code, now()->addMinutes(10));
        Cache::put('verification_payload_' . $request->email, $request->all(), now()->addMinutes(10));

        // Enviar el email
        Mail::to($request->email)->send(new VerificationCodeMail($code));

        return response()->json([
            'status'  => 'success',
            'message' => 'Código de verificación enviado',
        ]);
    }

    /**
     * Verifica el codigo introducido por el usuario y, si es correcto,
     * recupera el payload guardado en cache y crea el usuario.
     */
    public function register(Request $request)
    {
        $request->validate([
            'email'             => 'required|email',
            'verification_code' => 'required|string|size:6',
        ]);

        $cachedCode    = Cache::get('verification_code_'   . $request->email);
        $cachedPayload = Cache::get('verification_payload_' . $request->email);

        //Compruebo que el codigo existe y coincide
        if (!$cachedCode || $cachedCode !== $request->verification_code) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Código de verificación incorrecto o expirado',
            ], 422);
        }

        //Uso el payload guardado para validar y crear el usuario
        $role = $cachedPayload['role'] ?? null;

        $rules = [
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|string|in:EU,Student,Teacher,EI',
        ];

        if (in_array($role, ['Student', 'Teacher', 'EI'])) {
            $rules['education_level']  = 'required|string|max:255';
            $rules['institution_name'] = 'required|string|max:255';
        }

        if (in_array($role, ['Student', 'Teacher', 'EU'])) {
            $rules['name']      = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
            $rules['dni']       = 'required|string|max:20|unique:users,dni';
        }

        //Valido usando el payload de la cache (no el de la request actual)
        $validator = \Illuminate\Support\Facades\Validator::make($cachedPayload, $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::create([
            'name'             => $validated['name'] ?? null,
            'last_name'        => $validated['last_name'] ?? null,
            'email'            => $validated['email'],
            'password'         => Hash::make($validated['password']),
            'role'             => $validated['role'],
            'dni'              => $validated['dni'] ?? null,
            'education_level'  => $validated['education_level'] ?? null,
            'institution_name' => $validated['institution_name'] ?? null,
        ]);

        //Limpio la cache una vez creado el usuario
        Cache::forget('verification_code_'   . $request->email);
        Cache::forget('verification_payload_' . $request->email);

        return response()->json([
            'status'  => 'success',
            'message' => 'Usuario registrado correctamente',
            'user'    => $user,
        ], 201);
    }

    /**
     * Comprueba que el email existe y que la contraseña es correcta.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Buscar el usuario por email
        $user = User::where('email', $request->email)->first();

        // Si no existe el usuario o la contraseña no coincide
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El email o la contraseña son incorrectos',
            ], 401);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Login correcto',
            'user'    => $user,
        ]);
    }
}
