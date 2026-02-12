<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Laravel | TelamoNet</title>
    
    <!-- CARGA DE ASSETS LOCALES (COMPILADOS POR VITE) -->
    <!-- Buscamos el archivo JS y CSS generado en public/frontend/assets -->
    @php
        $jsFile = collect(glob(public_path('frontend/assets/*.js')))->map(fn($path) => basename($path))->first();
        $cssFile = collect(glob(public_path('frontend/assets/*.css')))->map(fn($path) => basename($path))->first();
    @endphp

    @if($cssFile)
        <link rel="stylesheet" href="{{ asset('frontend/assets/' . $cssFile) }}">
    @else
        <script src="https://cdn.tailwindcss.com"></script>
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
        
        <h1 class="text-4xl font-black mb-2 text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">
            Backend con Vue Local
        </h1>
        <p class="text-slate-400 mb-8 font-medium">
            Laravel está sirviendo sus propios assets compilados de Vue.
        </p>

        <a href="{{ config('app.frontend_url') }}/home" 
           class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-blue-500/20 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver a TelamoNet
        </a>
    </div>

    <!-- Carga del JS de Vue local -->
    @if($jsFile)
        <script type="module" src="{{ asset('frontend/assets/' . $jsFile) }}"></script>
    @endif

    <footer class="mt-8 text-slate-500 text-sm">
        Laravel {{ app()->version() }} • DB: {{ DB::connection()->getDatabaseName() }}
    </footer>
</body>
</html>