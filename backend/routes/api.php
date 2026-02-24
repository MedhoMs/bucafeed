<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;

// Importante: No hace falta el prefijo /api aquí, Laravel lo añade automáticamente
Route::get('/test-connection', function () {
    try {
        DB::connection()->getPdo();
        $dbStatus = "Conectado a MySQL correctamente";
    } catch (\Exception $e) {
        $dbStatus = "Error en la base de datos: " . $e->getMessage();
    }

    return response()->json([
        'message' => '¡Hola desde Laravel!',
        'database' => $dbStatus,
        'status' => 'success'
    ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/send-code', [AuthController::class, 'sendVerificationCode']);
