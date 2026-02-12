<?php

use Illuminate\Support\Facades\Route;

// Aquí puedes definir otras rutas web específicas ANTES de la catch-all
// Ejemplo: Route::get('/docs', fn() => view('docs'));

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
