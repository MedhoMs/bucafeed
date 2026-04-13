<?php

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EducationalCenterController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\TagController;

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
Route::post('/login', [AuthController::class, 'login']);
Route::get('/events', [EventController::class, 'apiIndex']);
Route::get('/events/{id}/image', [EventController::class, 'streamImage'])->name('api.event.image');
Route::get('/educational-centers', [EducationalCenterController::class, 'apiIndex']);

// Usuarios (para poder ver perfiles públicos desde Vue)
Route::get('/users/{user}', function (User $user) {
    return response()->json($user);
});

// Preguntas y Respuestas
Route::apiResource('questions', QuestionController::class);
Route::apiResource('answers', AnswerController::class);
Route::apiResource('tags', TagController::class);
Route::post('answers/{answer}/useful', [AnswerController::class, 'markAsUseful']);