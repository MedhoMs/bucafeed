@if(!request()->ajax())
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>Panel Adminstrador</title>

        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        

        {{-- Constants --}}
        <x-admin.constants.colors />

        @if(app()->environment('local'))
            <!-- Modo Desarrollo: Carga desde el servidor de Vite -->
            <script type="module" src="http://localhost:5173/@@vite/client"></script>
            <link rel="stylesheet" href="http://localhost:5173/src/styles/style.css">
            <link rel="stylesheet" href="http://localhost:5173/src/styles/main.css">
        @else
            <!-- Modo Producción: Carga los assets compilados -->
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="hold-transition sidebar-mini">
        <div id="app" class="wrapper flex min-h-screen bg-[#0a141d]"> <!-- Override background -->
            <!-- Navbar -->
            @include('components.navbar')
            
            <!-- Contenido Principal -->
            <div id="main-content-area" class="content-wrapper flex-1 relative z-0">
@endif
                @yield('content')
@if(!request()->ajax())
            </div>
        </div>
        
        <!-- Modal Tailwind (Hidden by default) -->
        <div id="default-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/60 transition-opacity duration-300">
            <div class="relative p-4 w-full max-w-4xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-[#0f1922] rounded-2xl shadow-2xl border border-cyan-900/40">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between p-4 md:p-5 border-b border-cyan-900/40 rounded-t">
                        <h3 class="text-xl font-bold text-white tracking-wide" id="modal-title">
                            Detalles
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-white/10 hover:text-white rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center transition-colors" data-modal-hide="default-modal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                            <span class="sr-only">Cerrar modal</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-4 md:p-5 space-y-4 text-white" id="modal-body">
                         <div class="text-center py-10">
                            <svg class="animate-spin h-8 w-8 text-cyan-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-cyan-200/70 animate-pulse">Cargando datos...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Core Admin Scripts -->
        <script src="{{ asset('js/admin-core.js') }}"></script>
    </body>
</html>
@endif
