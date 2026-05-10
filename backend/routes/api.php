<?php

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cycle;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EducationalCenterController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\KahootController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MensajeController;
use App\Http\Controllers\CycleController;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
    ]);
});

Route::get('/test-email', function () {
    // Diagnóstico: mostrar la config de mail que Laravel está usando realmente
    $mailConfig = [
        'default_mailer' => config('mail.default'),
        'smtp_host' => config('mail.mailers.smtp.host'),
        'smtp_port' => config('mail.mailers.smtp.port'),
        'smtp_encryption' => config('mail.mailers.smtp.encryption'),
        'smtp_username' => config('mail.mailers.smtp.username'),
        'smtp_password' => config('mail.mailers.smtp.password') ? '***SET(' . strlen(config('mail.mailers.smtp.password')) . ' chars)***' : '***NOT SET***',
        'from_address' => config('mail.from.address'),
        'from_name' => config('mail.from.name'),
        'env_MAIL_MAILER' => env('MAIL_MAILER'),
        'env_MAIL_HOST' => env('MAIL_HOST'),
        'env_MAIL_PORT' => env('MAIL_PORT'),
        'env_MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
    ];

    try {
        Mail::raw('Prueba de conexión SMTP desde Railway', function ($message) {
            $message->to('telamonetofficial@gmail.com')
                    ->subject('Test de Correo - TelamoNet');
        });
        return response()->json([
            'message' => '¡Correo enviado correctamente!',
            'config_usada' => $mailConfig,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'config_usada' => $mailConfig,
            'trace' => 'Verifica tus variables de MAIL en Railway'
        ], 500);
    }
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
        'status' => 'success',
        'cycles_count' => Cycle::count()
    ]);
});

Route::get('/test-cycles', function() {
    try {
        return Cycle::with('tags')->get();
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::post('/send-code', [AuthController::class, 'sendVerificationCode']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Devuelve los datos frescos del usuario autenticado (sincroniza el localStorage del frontend)
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json($request->user());
});

Route::get('/events', [EventController::class, 'apiIndex']);
Route::post('/events/{id}/join', [EventController::class, 'apiJoin'])->middleware('auth:sanctum');
Route::get('/events/{id}/image', [EventController::class, 'streamImage'])->name('api.event.image');
Route::post('/events/generate-kahoot', [KahootController::class, 'generateQuestions']);
Route::get('/educational-centers', [EducationalCenterController::class, 'apiIndex']);
Route::get('/meetings', [MeetingController::class, 'apiIndex']);
Route::get('/meetings/{id}', [MeetingController::class, 'apiShow']);
Route::post('/meetings', [MeetingController::class, 'apiStore']);
Route::delete('/meetings/{id}', [MeetingController::class, 'destroy']);
Route::get('/all-cycles', [CycleController::class, 'apiIndex']);

// Chat grupal (Mensajes)
Route::post('/groups/{group}/mensajes', [MensajeController::class, 'store']);
Route::get('/groups/{group}/mensajes', [MensajeController::class, 'index']);

// Chat de Charlas (Meetings)
Route::get('/meetings/{meeting}/mensajes', [\App\Http\Controllers\MeetingMessageController::class, 'index']);
Route::post('/meetings/{meeting}/mensajes', [\App\Http\Controllers\MeetingMessageController::class, 'store']);

// Usuarios
Route::apiResource('users', UserController::class);
Route::get('/users/by-center', [UserController::class, 'apiStudentsByCenter']);

// Preguntas y Respuestas
Route::apiResource('questions', QuestionController::class);
Route::apiResource('answers', AnswerController::class);
Route::apiResource('tags', TagController::class);
Route::post('answers/{answer}/useful', [AnswerController::class, 'markAsUseful']);

// ── Panel de Gestión de Centro (para usuarios EI) ──
Route::middleware('auth:sanctum')->prefix('my-center')->group(function () {
    Route::get('/', [EducationalCenterController::class, 'apiShowMyCenter']);
    Route::get('/groups', [EducationalCenterController::class, 'apiGroups']);
    Route::post('/groups', [EducationalCenterController::class, 'apiStoreGroup']);
    Route::put('/groups/{group}', [EducationalCenterController::class, 'apiUpdateGroup']);
    Route::delete('/groups/{group}', [EducationalCenterController::class, 'apiDeleteGroup']);
    Route::post('/groups/{group}/students', [EducationalCenterController::class, 'apiAssignStudents']);
    Route::delete('/groups/{group}/students/{user}', [EducationalCenterController::class, 'apiRemoveStudent']);
    Route::put('/groups/{group}/tutor', [EducationalCenterController::class, 'apiAssignTutor']);
    Route::post('/groups/{group}/subjects', [EducationalCenterController::class, 'apiAssignSubjectTeacher']);
    Route::delete('/groups/{group}/subjects/{tag}', [EducationalCenterController::class, 'apiRemoveSubjectTeacher']);
    Route::get('/teachers', [EducationalCenterController::class, 'apiTeachers']);
    Route::get('/students', [EducationalCenterController::class, 'apiStudents']);
    Route::get('/admins', [EducationalCenterController::class, 'apiAdmins']);
    Route::get('/cycles', [EducationalCenterController::class, 'apiCycles']);
    
    // Gestión de Eventos del Centro
    Route::get('/events', [EducationalCenterController::class, 'apiEvents']);
    Route::post('/events', [EducationalCenterController::class, 'apiStoreEvent']);
    Route::put('/events/{event}', [EducationalCenterController::class, 'apiUpdateEvent']);
    Route::delete('/events/{event}', [EducationalCenterController::class, 'apiDeleteEvent']);

    // Gestión de Usuarios Globales (Matriculación)
    Route::get('/search-users', [EducationalCenterController::class, 'apiSearchUsers']);
    Route::post('/enroll-users', [EducationalCenterController::class, 'apiEnrollUsers']);

    // Gestión de Ciclos del Centro
    Route::post('/enroll-cycles', [EducationalCenterController::class, 'apiEnrollCycles']);
    Route::delete('/cycles/{cycle}', [EducationalCenterController::class, 'apiRemoveCycle']);
});
