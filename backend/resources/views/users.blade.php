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
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">
                    Usuarios en Base de Datos
                </h1>
                <p class="text-slate-400 mt-2">Gestión y visualización de usuarios registrados.</p>
            </div>
            
            <a href="{{ config('app.frontend_url') }}/home" 
               class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-blue-500/20 group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a TelamoNet
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($users as $user)
                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 shadow-xl hover:border-blue-500/50 hover:shadow-blue-500/10 transition-all group flex flex-col h-full">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-12 w-12 bg-blue-600/20 rounded-xl flex items-center justify-center text-blue-400 font-bold text-xl shrink-0">
                            {{ strtoupper(substr($user->name ?? $user->email, 0, 1)) }}
                        </div>
                        <span class="px-3 py-1 bg-slate-700 rounded-full text-xs font-bold uppercase tracking-wider text-slate-300 border border-slate-600">
                            {{ $user->role }}
                        </span>
                    </div>

                    <div class="mb-auto">
                        <h3 class="text-lg font-bold mb-1 truncate text-slate-100" title="{{ $user->name }} {{ $user->last_name }}">{{ $user->name }} {{ $user->last_name }}</h3>
                        <p class="text-sm text-slate-500 truncate mb-2" title="{{ $user->email }}">{{ $user->email }}</p>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-slate-700/50 space-y-2">
                        @if($user->role === 'student' && $user->student)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Curso:</span>
                                <span class="text-slate-200 font-semibold">{{ $user->student->course }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">ID Nacional:</span>
                                <span class="text-slate-200 font-semibold">{{ $user->national_id ?? 'N/A' }}</span>
                            </div>
                        @elseif(($user->role === 'institution' || $user->role === 'teacher') && $user->educationalCenter)
                             <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Centro:</span>
                                <span class="text-slate-200 font-semibold truncate ml-2" title="{{ $user->educationalCenter->name }}">{{ $user->educationalCenter->name }}</span>
                            </div>
                             <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Tipo:</span>
                                <span class="text-slate-200 font-semibold">{{ $user->educationalCenter->type }}</span>
                            </div>
                        @else
                            <div class="flex items-center text-sm text-slate-500 italic">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Sin datos adicionales
                            </div>
                        @endif
                        
                         <div class="flex justify-between text-xs pt-2 mt-2 border-t border-slate-700/30 text-slate-500">
                            <span>ID: #{{ $user->id }}</span>
                            <span>{{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-slate-800/50 border border-dashed border-slate-700 p-12 text-center rounded-3xl">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-slate-700/50 mb-4 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-200">No hay usuarios</h3>
                    <p class="text-slate-500 mt-1">Todavía no hay usuarios registrados en el sistema.</p>
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>
