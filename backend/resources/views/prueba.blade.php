<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Laravel | TelamoNet</title>
    
    @if(app()->environment('local'))
        <!-- Modo Desarrollo: Carga desde el servidor de Vite en el puerto 5174 -->
        <script type="module" src="http://localhost:5174/@@vite/client"></script>
        <link rel="stylesheet" href="http://localhost:5174/backend/resources/css/app.css">
    @else
        <!-- Modo Producción: Carga los assets compilados para Railway -->
        @vite(['resources/css/app.css'], 'frontend')
    @endif
</head>
<body class="flex flex-col justify-center items-center min-h-screen bg-slate-900 text-white font-sans">
    <div class="bg-slate-800 p-10 rounded-2xl shadow-2xl border border-slate-700 text-center max-w-md w-full mx-4">
        <div class="mb-6 flex justify-center">
            <!-- Logo local si existe, si no remoto -->
            <img src="{{ asset('logoTelamon.png') }}" 
                 onerror="this.src='{{ config('app.frontend_url') }}/logoTelamon.png'"
                 alt="Logo TelamoNet" class="h-20 w-auto">
        </div>
        
        <p class="text-slate-400 mb-8 font-medium">
            hola mundo
        </p>

        <a href="{{ config('app.frontend_url') }}/home" 
           class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-blue-500/20 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver a TelamoNet
        </a>
    </div>


    <footer class="mt-8 text-slate-500 text-sm text-center">
        <div>Laravel {{ app()->version() }} • DB: {{ DB::connection()->getDatabaseName() }}</div>
        <div class="mt-2 opacity-50">
            DEBUG: APP_URL={{ config('app.url') }} | FRONTEND_URL={{ config('app.frontend_url') }}
        </div>
    </footer>
</body>
</html>
