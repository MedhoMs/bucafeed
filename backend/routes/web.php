<?php

use Illuminate\Support\Facades\Route;

// Aquí puedes definir otras rutas web específicas ANTES de la catch-all
// Ejemplo: Route::get('/docs', fn() => view('docs'));

Route::get('/', function () {
    return response()->json([
        'status' => 'online',
        'service' => 'TelamoNet Backend API',
        'version' => app()->version(),
        'database' => DB::connection()->getDatabaseName() ? 'Connected' : 'Disconnected'
    ]);
});

Route::get('/prueba', function () {
    return view('prueba');
});

Route::get('/{any}', function () {
    return response()->json(['message' => 'API Endpoint Not Found'], 404);
})->where('any', '.*');
