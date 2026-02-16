<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios Registrados | TelamoNet</title>
    <!-- CARGA DE TAILWIND (BACKEND) -->
    @if(app()->environment('local'))
        <script type="module" src="http://localhost:5174/@@vite/client"></script>
        <link rel="stylesheet" href="http://localhost:5174/backend/resources/css/app.css">
    @else
        @vite(['backend/resources/css/app.css'], 'frontend')
    @endif
</head>
<body class="bg-slate-900 text-white font-sans p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">
                Usuarios en Base de Datos
            </h1>
        <a href="{{ config('app.frontend_url') }}/home" 
           class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-blue-500/20 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver a TelamoNet
        </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($users as $user)
                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 shadow-xl hover:border-blue-500/50 transition-all group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-12 w-12 bg-blue-600/20 rounded-xl flex items-center justify-center text-blue-400 font-bold text-xl">
                            {{ strtoupper(substr($user->name ?? $user->email, 0, 1)) }}
                        </div>
                        <span class="px-3 py-1 bg-slate-700 rounded-full text-xs font-bold uppercase tracking-wider text-slate-300">
                            {{ $user->role }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold mb-1 truncate">{{ $user->name }}</h3>
                    <p class="text-sm text-slate-500 truncate mb-4">{{ $user->email }}</p>
                    
                    <div class="mt-4 pt-4 border-t border-slate-700/50">
                        @if($user->role === 'student' && $user->student)
                            <p class="text-sm text-slate-400"><span class="font-bold text-slate-200">Nombre:</span> {{ $user->student->first_name }} {{ $user->student->last_name }}</p>
                            <p class="text-sm text-slate-400"><span class="font-bold text-slate-200">DNI:</span> {{ $user->student->dni_nie ?? 'N/A' }}</p>
                        @elseif($user->role === 'ei' && $user->educationalInstitution)
                            <p class="text-sm text-slate-400"><span class="font-bold text-slate-200">Centro:</span> {{ $user->educationalInstitution->institution_name }}</p>
                            <p class="text-sm text-slate-400"><span class="font-bold text-slate-200">Nivel:</span> {{ $user->educationalInstitution->education_level }}</p>
                        @else
                            <p class="text-sm text-slate-500 italic">Sin datos adicionales de perfil.</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-slate-800/50 border border-dashed border-slate-700 p-12 text-center rounded-3xl">
                    <p class="text-slate-500 text-lg italic">Todavía no hay usuarios registrados.</p>
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>
