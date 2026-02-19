<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/prueba', function () {
    return view('prueba');
});
Route::get('/db-check', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $tablesList = array_map(function($t) { return array_values((array)$t)[0]; }, $tables);
        
        return response()->json([
            'status' => 'ok',
            'message' => 'Database connected',
            'tables' => $tablesList,
            'user_count' => \App\Models\User::count(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});


Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
Route::get('/admin/users', [AdminController::class, 'users']);

// User CRUD routes

Route::get('/users/create', [UserController::class, 'create'])->name('user.create');
Route::post('/users/create', [UserController::class, 'create'])->name('user.create');

Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
Route::post('/users/edit/{id}', [UserController::class, 'edit'])->name('user.edit.post');

Route::get('/users/show/{id}', [UserController::class, 'show'])->name('user.show');

Route::get('/users/destroy/{id}', [UserController::class, 'destroy'])->name('user.destroy');
Route::post('/users/destroy/{id}', [UserController::class, 'destroy'])->name('user.destroy');



