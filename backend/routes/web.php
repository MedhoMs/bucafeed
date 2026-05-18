<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BannedWordController;
use App\Http\Controllers\EducationalCenterController;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\PublicationController;

Route::get('/', function () {
    return redirect()->away(env('APP_FRONTEND_URL', 'http://localhost:5173'));
});

Route::get('/whoami', function () {
    return response()->json([
        'check' => auth()->check(),
        'id' => auth()->user()?->id,
        'email' => auth()->user()?->email,
        'role' => auth()->user()?->role,
    ]);
});


Route::get('/prueba', function () {
    return view('prueba');
});

// Puente de autenticación: permite a usuarios de Vue (con token API) iniciar sesión web para acceder al panel admin
Route::get('/admin-bridge', function (\Illuminate\Http\Request $request) {
    $token = $request->query('token');
    
    if (!$token) {
        return redirect()->away(env('APP_FRONTEND_URL', 'http://localhost:5173'));
    }

    // Buscar el token en la tabla personal_access_tokens de Sanctum
    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
    
    if (!$accessToken) {
        return redirect()->away(env('APP_FRONTEND_URL', 'http://localhost:5173'));
    }

    $user = $accessToken->tokenable;

    if ($user && strtolower($user->role ?? '') === 'admin') {
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect('/admin');
    }

    return redirect()->away(env('APP_FRONTEND_URL', 'http://localhost:5173'));
});





// Array de rutas CRUD
$adminRoutes = [
    [
        'prefix'     => 'users',
        'name'       => 'user.',
        'controller' => UserController::class,
        'routes'     => [
            ['get', 'create', 'create', 'create'],
            ['post', 'create', 'store', 'create.post'],
            ['get', 'edit/{id}', 'edit', 'edit'],
            ['post', 'edit/{id}', 'update', 'edit.post'],
            ['get', 'show/{id}', 'show', 'show'],
            ['get', 'profile-modal/{id}', 'profileModal', 'profile_modal'],
            ['get', 'destroy/{id}', 'destroy', 'destroy'],
            ['post', 'destroy/{id}', 'destroy', 'destroy.post'],
        ],
    ],
    [
        'prefix'     => 'admin/roles',
        'name'       => 'role.',
        'controller' => RoleController::class,
        'routes'     => [
            ['get', '/', 'index', 'index'],
            ['get', 'create', 'create', 'create'],
            ['post', 'create', 'store', 'store'],
            ['get', 'edit/{id}', 'edit', 'edit'],
            ['post', 'edit/{id}', 'update', 'update'],
            ['get', 'show/{id}', 'show', 'show'],
            ['get', 'destroy/{id}', 'destroy', 'destroy'],
            ['post', 'destroy/{id}', 'destroy', 'destroy.post'],
        ],
    ],
    [
        'prefix'     => 'admin/events',
        'name'       => 'users_events.',
        'controller' => EventController::class,
        'routes'     => [
            ['get', '/', 'index', 'index'],
            ['get', 'create', 'create', 'create'],
            ['post', 'create', 'store', 'create.post'],
            ['get', 'edit/{id}', 'edit', 'edit'],
            ['post', 'edit/{id}', 'update', 'edit.post'],
            ['get', 'show/{id}', 'show', 'show'],
            ['get', 'destroy/{id}', 'destroy', 'destroy'],
            ['post', 'destroy/{id}', 'destroy', 'destroy.post'],
        ],
    ],
    [
        'prefix'     => 'admin/banned-words',
        'name'       => 'banned_words.',
        'controller' => BannedWordController::class,
        'routes'     => [
            ['get', '/', 'index', 'index'],
            ['get', 'create', 'create', 'create'],
            ['post', 'create', 'store', 'create.post'],
            ['get', 'edit/{id}', 'edit', 'edit'],
            ['post', 'edit/{id}', 'update', 'edit.post'],
            ['get', '{id}', 'destroy', 'destroy'],
            ['post', '{id}', 'destroy', 'destroy.post'],
        ],
    ],
    [
        'prefix'     => 'admin/educational-centers',
        'name'       => 'educational_centers.',
        'controller' => EducationalCenterController::class,
        'routes'     => [
            ['get', '/', 'index', 'index'],
            ['get', 'create', 'create', 'create'],
            ['post', 'create', 'store', 'create.post'],
            ['get', 'edit/{id}', 'edit', 'edit'],
            ['post', 'edit/{id}', 'update', 'edit.post'],
            ['get', 'show/{id}', 'show', 'show'],
            ['get', 'destroy/{id}', 'destroy', 'destroy'],
            ['post', 'destroy/{id}', 'destroy', 'destroy.post'],
            ['get', 'profile-modal/{id}', 'profileModal', 'profile_modal'],
            ['get', 'assign/{id}', 'assign', 'assign_view'],
            ['post', 'assign/{id}', 'assignStudent', 'assign'],
            ['get', 'add-users/{id}', 'addUsers', 'add_users'],
            ['post', 'add-users/{id}', 'storeUsers', 'store_users'],
            ['get', 'manage-cycles/{id}', 'manageCycles', 'manage_cycles'],
            ['post', 'add-cycle/{id}', 'addCycle', 'add_cycle'],
            ['post', 'remove-cycle/{id}', 'removeCycle', 'remove_cycle'],
            ['get', '{id}/manage-groups', 'manageGroups', 'manage_groups'],
            ['post', '{id}/store-group', 'storeGroup', 'store_group'],
            ['get', '{id}/edit-group/{groupId}', 'editGroup', 'edit_group'],
            ['get', '{id}/group-details/{groupId}', 'groupDetailsModal', 'group_details'],
            ['post', '{id}/update-group/{groupId}', 'updateGroup', 'update_group'],
            ['post', '{id}/delete-group/{groupId}', 'deleteGroup', 'delete_group'],
            ['get', 'list-users/{id}/{role}', 'listUsersModal', 'list_users_modal'],
        ],
    ],
    [
        'prefix'     => 'admin/global-cycles',
        'name'       => 'global_cycles.',
        'controller' => CycleController::class,
        'routes'     => [
            ['get', '/', 'index', 'index'],
            ['get', 'create', 'create', 'create'],
            ['post', 'add', 'store', 'store'],
            ['get', 'edit/{id}', 'edit', 'edit'],
            ['post', 'edit/{id}', 'update', 'update'],
            ['get', 'destroy/{id}', 'destroy', 'destroy'],
            ['post', 'delete/{id}', 'destroy', 'destroy.post'],
        ],
    ],
    [
        'prefix'     => 'admin/questions',
        'name'       => 'question.',
        'controller' => QuestionController::class,
        'routes'     => [
            ['get', '/', 'index', 'index'],
            ['get', 'create', 'create', 'create'],
            ['post', 'store', 'store', 'store'],
            ['get', 'tags-by-user/{userId}', 'getTagsByUser', 'tags_by_user'],
            ['get', 'show/{id}', 'show', 'show'],
            ['get', 'destroy/{id}', 'destroy', 'destroy'],
            ['post', 'destroy/{id}', 'destroy', 'destroy.post'],
        ],
    ],
    [
        'prefix'     => 'admin/publications',
        'name'       => 'publication.',
        'controller' => PublicationController::class,
        'routes'     => [
            ['get', '/', 'index', 'index'],
            ['get', 'create', 'create', 'create'],
            ['post', 'store', 'store', 'store'],
            ['get', 'edit/{id}', 'edit', 'edit'],
            ['post', 'update/{id}', 'update', 'update'],
            ['get', 'show/{id}', 'show', 'show'],
            ['get', 'destroy/{id}', 'destroy', 'destroy'],
            ['post', 'destroy/{id}', 'destroy', 'destroy.post'],
        ],
    ],
];

Route::middleware(['admin'])->group(function () use ($adminRoutes) {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/users', [AdminController::class, 'users']);

    // Mapeo automático de rutas
    foreach ($adminRoutes as $group) {
        Route::prefix($group['prefix'])
            ->name($group['name'])
            ->controller($group['controller'])
            ->group(function () use ($group) {
                foreach ($group['routes'] as $route) {
                    $method = $route[0]; // get o post
                    Route::$method($route[1], $route[2])->name($route[3]);
                }
            });
    }
});
