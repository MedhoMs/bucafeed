<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>Panel Adminstrador</title>

        @if(app()->environment('local'))
            <link rel="icon" type="image/png" href="http://localhost:5173/src/assets/logo/logoTelamon.png">
        @else
            <link rel="icon" type="image/png" href="{{ asset('assets/logo/logoTelamon.png') }}">
        @endif
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        

        @if(app()->environment('local'))
            <!-- Modo Desarrollo: Carga desde el servidor de Vite -->
            <script type="module" src="http://localhost:5173/@@vite/client"></script>
            <link rel="stylesheet" href="http://localhost:5173/src/sytles/style.css">
            <link rel="stylesheet" href="http://localhost:5173/src/sytles/main.css">
        @else
            <!-- Modo Producción: Carga los assets compilados -->
            @vite(['src/sytles/style.css', 'src/sytles/main.css'], 'frontend')
        @endif
    </head>
    <body class="hold-transition sidebar-mini">
        <div id="app" class="wrapper flex min-h-screen"> <!-- ID app para estilos globales + Flex container -->
            <!-- Navbar -->
            @include('components.navbar')
            
            <!-- Contenido Principal -->
            <div class="content-wrapper flex-1 p-4">
                @yield('content')
            </div>
        </div>
    </body>
</html>