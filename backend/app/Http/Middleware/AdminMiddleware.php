<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'admin') {
            return $next($request);
        }

        // Si es una petición de la API, devolver JSON (importante para evitar errores en llamadas fetch de Vue)
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        // Si intenta entrar desde el navegador, lo expulsamos al frontend.
        $frontendUrl = env('APP_FRONTEND_URL', 'http://localhost:5173');
        return redirect()->away($frontendUrl);
    }
}
