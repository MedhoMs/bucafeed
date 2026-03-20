<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BannedWordController;
use App\Http\Controllers\EducationalCenterController;
use App\Http\Controllers\CycleController;

Route::get('/', function () {
    return view('welcome');
});

// Rutas de emergencia para Railway (Migraciones y Seeders)
Route::get('/migrate', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return "Migraciones ejecutadas: " . \Illuminate\Support\Facades\Artisan::output();
    } catch (\Exception $e) {
        return "Error en migraciones: " . $e->getMessage();
    }
});

Route::get('/seed', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return "Seeders ejecutados: " . \Illuminate\Support\Facades\Artisan::output();
    } catch (\Exception $e) {
        return "Error en seeders: " . $e->getMessage();
    }
});

Route::get('/prueba', function () {
    return view('prueba');
});



Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
Route::get('/admin/users', [AdminController::class, 'users']);

// Array de rutas CRUD
$adminRoutes = [
    [
        'prefix'     => 'users',
        'name'       => 'user.',
        'controller' => UserController::class,
        'routes'     => [
            ['get', 'create', 'create', 'create'],
            ['post', 'create', 'create', 'create'],
            ['get', 'edit/{id}', 'edit', 'edit'],
            ['post', 'edit/{id}', 'edit', 'edit.post'],
            ['get', 'show/{id}', 'show', 'show'],
            ['get', 'profile-modal/{id}', 'profileModal', 'profile_modal'],
            ['get', 'destroy/{id}', 'destroy', 'destroy'],
            ['post', 'destroy/{id}', 'destroy', 'destroy'],
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
            ['post', 'destroy/{id}', 'destroyPost', 'destroy.post'],
        ],
    ],
    [
        'prefix'     => 'admin/events',
        'name'       => 'users_events.',
        'controller' => EventController::class,
        'routes'     => [
            ['get', '/', 'index', 'index'],
            ['get', 'create', 'create', 'create'],
            ['post', 'create', 'create', 'create.post'],
            ['get', 'edit/{id}', 'edit', 'edit'],
            ['post', 'edit/{id}', 'edit', 'edit.post'],
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
            ['post', 'create', 'create', 'create.post'],
            ['get', 'edit/{id}', 'edit', 'edit'],
            ['post', 'edit/{id}', 'edit', 'edit.post'],
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
            ['post', 'create', 'create', 'create.post'],
            ['get', 'edit/{id}', 'edit', 'edit'],
            ['post', 'edit/{id}', 'edit', 'edit.post'],
            ['get', 'show/{id}', 'show', 'show'],
            ['get', 'destroy/{id}', 'destroy', 'destroy'],
            ['post', 'destroy/{id}', 'destroy', 'destroy.post'],
            ['get', 'profile-modal/{id}', 'profileModal', 'profile_modal'],
            ['get', 'assign/{id}', 'assign', 'assign_view'],
            ['post', 'assign/{id}', 'assignStudent', 'assign'],
            ['get', 'add-users/{id}', 'addUsers', 'add_users'],
            ['post', 'add-users/{id}', 'storeUsers', 'store_users'],
            ['get', 'cycles/{id}', 'manageCycles', 'manage_cycles'],
            ['post', 'cycles/{id}', 'addCycle', 'add_cycle'],
            ['post', 'cycles/remove/{id}', 'removeCycle', 'remove_cycle'],
            ['get', 'list-users/{id}/{role}', 'listUsersModal', 'list_users_modal'],
        ],
    ],
    [
        'prefix'     => 'admin/global-cycles',
        'name'       => 'global_cycles.',
        'controller' => CycleController::class,
        'routes'     => [
            ['get', '/', 'index', 'index'],
            ['post', 'add', 'store', 'store'],
            ['post', 'delete/{id}', 'destroy', 'destroy'],
        ],
    ],
];

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
