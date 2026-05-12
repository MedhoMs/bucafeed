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
use App\Http\Controllers\MeetingMessageController;
use App\Http\Controllers\MensajeController;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\ChatController;

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
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Devuelve los datos frescos del usuario autenticado (sincroniza el localStorage del frontend)
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json($request->user());
});

Route::get('/events', [EventController::class, 'apiIndex']);
Route::post('/events/{id}/join', [EventController::class, 'apiJoin'])->middleware('auth:sanctum');
Route::get('/events/{id}/image', [EventController::class, 'streamImage'])->name('api.event.image');
Route::post('/events/generate-kahoot', [KahootController::class, 'generateQuestions']);
Route::get('/test-gemini', function (\Illuminate\Http\Request $request) {
    $apiKey = config('services.gemini.api_key');
    if (!$apiKey) return response()->json(['error' => 'No API key'], 500);
    try {
        $client = new \GuzzleHttp\Client(['timeout' => 30]);
        $response = $client->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
            'json' => ['contents' => [['parts' => [['text' => 'Responde solo: OK']]]]],
        ]);
        $body = json_decode($response->getBody(), true);
        return response()->json([
            'status' => $response->getStatusCode(),
            'response' => $body['candidates'][0]['content']['parts'][0]['text'] ?? 'no text',
            'model' => 'gemini-2.5-flash v1',
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/meetings/{meeting}/kahoot', [KahootController::class, 'createSession']);
    Route::get('/meetings/{meeting}/kahoot/active', [KahootController::class, 'getActiveSession']);
    Route::post('/kahoot/{session}/start', [KahootController::class, 'startSession']);
    Route::get('/kahoot/{session}/current', [KahootController::class, 'getCurrentQuestion']);
    Route::post('/kahoot/{session}/answer', [KahootController::class, 'submitAnswer']);
    Route::post('/kahoot/{session}/next', [KahootController::class, 'nextQuestion']);
    Route::get('/kahoot/{session}/leaderboard', [KahootController::class, 'getLeaderboard']);
    Route::get('/kahoot/{session}/results', [KahootController::class, 'getResults']);

    // File Upload
    Route::post('/upload', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,gif,pdf|max:10240',
        ]);
        $file = $request->file('file');
        $id = bin2hex(random_bytes(16));
        $ext = $file->getClientOriginalExtension();
        $filename = $id . '.' . $ext;
        $file->storeAs('chat-uploads', $filename, 'public');
        $baseUrl = rtrim(config('app.url'), '/');
        return response()->json([
            'url' => $baseUrl . '/api/serve-file/' . $filename,
            'filename' => $file->getClientOriginalName(),
        ]);
    });

    // Chat Messages
    Route::get('/meetings/{meeting}/messages', function (\App\Models\Meeting $meeting) {
        $messages = \App\Models\Message::where('meeting_id', $meeting->id)
            ->with('user')
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'type' => $msg->message_type,
                    'content' => $msg->content,
                    'file_name' => $msg->file_name,
                    'metadata' => $msg->metadata,
                    'sender' => $msg->user_id,
                    'user_name' => $msg->user ? $msg->user->name : 'Usuario',
                    'created_at' => $msg->created_at,
                ];
            });
        return response()->json($messages);
    });
    Route::post('/meetings/{meeting}/messages', function (\Illuminate\Http\Request $request, \App\Models\Meeting $meeting) {
        $validated = $request->validate([
            'content' => 'nullable|string',
            'type' => 'required|string|in:text,image,pdf,kahoot',
            'file_name' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);
        $message = \App\Models\Message::create([
            'meeting_id' => $meeting->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'] ?? '',
            'message_type' => $validated['type'],
            'file_name' => $validated['file_name'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);
        $message->load('user');
        return response()->json([
            'id' => $message->id,
            'type' => $message->message_type,
            'content' => $message->content,
            'file_name' => $message->file_name,
            'metadata' => $message->metadata,
            'sender' => $message->user_id,
            'user_name' => $message->user ? $message->user->name : 'Usuario',
            'created_at' => $message->created_at,
        ], 201);
    });
    
    // Private Chats
    Route::post('/chats/find-or-create', [ChatController::class, 'findOrCreate']);
    Route::get('/chats/{chat}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chats/{chat}/messages', [ChatController::class, 'sendMessage']);
});

// Serve uploaded files (no auth required for images/PDFs to display)
Route::get('/serve-file/{filename}', function ($filename) {
    try {
        $path = storage_path('app/public/chat-uploads/' . basename($filename));
        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        $mime = \Illuminate\Support\Facades\File::mimeType($path);
        return response(file_get_contents($path))->header('Content-Type', $mime);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

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
Route::get('/meetings/{meeting}/mensajes', [MeetingMessageController::class, 'index']);
Route::post('/meetings/{meeting}/mensajes', [MeetingMessageController::class, 'store']);

// Usuarios
Route::get('/users/by-center', [UserController::class, 'apiStudentsByCenter']);
Route::apiResource('users', UserController::class);

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
