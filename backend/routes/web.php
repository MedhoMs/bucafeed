<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BannedWordController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/prueba', function () {
    return view('prueba');
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

Route::get('/admin/roles', [RoleController::class, 'index'])->name('role.index');
Route::get('/admin/roles/create', [RoleController::class, 'create'])->name('role.create');
Route::post('/admin/roles/create', [RoleController::class, 'create'])->name('role.store');

Route::get('/admin/roles/edit/{id}', [RoleController::class, 'edit'])->name('role.edit');
Route::post('/admin/roles/edit/{id}', [RoleController::class, 'edit'])->name('role.update');

Route::get('/admin/roles/show/{id}', [RoleController::class, 'show'])->name('role.show');

Route::get('/admin/roles/destroy/{id}', [RoleController::class, 'destroy'])->name('role.destroy');
Route::post('/admin/roles/destroy/{id}', [RoleController::class, 'destroy'])->name('role.destroy.post');


Route::get('/admin/events', [EventController::class, 'index'])->name('users_events.index');
Route::get('/admin/events/create', [EventController::class, 'create'])->name('users_events.create');
Route::post('/admin/events/create', [EventController::class, 'create'])->name('users_events.create.post');

Route::get('/admin/events/edit/{id}', [EventController::class, 'edit'])->name('users_events.edit');
Route::post('/admin/events/edit/{id}', [EventController::class, 'edit'])->name('users_events.edit.post');

Route::get('/admin/events/show/{id}', [EventController::class, 'show'])->name('users_events.show');

Route::get('/admin/events/destroy/{id}', [EventController::class, 'destroy'])->name('users_events.destroy');
Route::post('/admin/events/destroy/{id}', [EventController::class, 'destroy'])->name('users_events.destroy.post');



Route::get('/admin/banned-words', [BannedWordController::class, 'index'])->name('banned_words.index');

Route::get('/admin/banned-words/create', [BannedWordController::class, 'create'])->name('banned_words.create');
Route::post('/admin/banned-words/create', [BannedWordController::class, 'create'])->name('banned_words.create.post');

Route::get('/admin/banned-words/edit/{id}', [BannedWordController::class, 'edit'])->name('banned_words.edit');
Route::post('/admin/banned-words/edit/{id}', [BannedWordController::class, 'edit'])->name('banned_words.edit.post');

Route::get('/admin/banned-words/{id}', [BannedWordController::class, 'destroy'])->name('banned_words.destroy');
Route::post('/admin/banned-words/{id}', [BannedWordController::class, 'destroy'])->name('banned_words.destroy.post');
