<?php

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EducationalCenterController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\KahootController;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
    ]);
});

Route::get('/ready', function () {
    try {
        DB::select('SELECT 1');

        if (!Schema::hasTable('migrations')) {
            return response()->json([
                'status' => 'error',
                'message' => 'The migrations table was not found.',
            ], 500);
        }

        return response()->json([
            'status' => 'ready',
        ]);
    } catch (\Throwable $exception) {
        return response()->json([
            'status' => 'error',
            'message' => $exception->getMessage(),
        ], 500);
    }
});

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

Route::post('/send-code', [AuthController::class, 'sendVerificationCode']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/events', [EventController::class, 'apiIndex']);
Route::get('/events/{id}/image', [EventController::class, 'streamImage'])->name('api.event.image');
Route::post('/events/generate-kahoot', [KahootController::class, 'generateQuestions']);
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
