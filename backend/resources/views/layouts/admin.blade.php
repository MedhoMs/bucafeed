<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>Panel Adminstrador</title>

        @if(app()->environment('local'))
            <link rel="icon" type="image/png" href="http://localhost:5174/src/assets/logo/logoTelamon.png">
        @else
            <link rel="icon" type="image/png" href="{{ asset('assets/logo/logoTelamon.png') }}">
        @endif
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        

        @if(app()->environment('local'))
            <!-- Modo Desarrollo: Carga desde el servidor de Vite -->
            <script type="module" src="http://localhost:5174/@@vite/client"></script>
            <link rel="stylesheet" href="http://localhost:5174/backend/resources/sytles/style.css">
            <link rel="stylesheet" href="http://localhost:5174/backend/resources/sytles/main.css">
        @else
            <!-- Modo Producción: Carga los assets compilados -->
            @vite(['resources/sytles/style.css', 'resources/sytles/main.css'], 'frontend')
        @endif
    </head>
    <body class="hold-transition sidebar-mini">
        <div id="app" class="wrapper flex min-h-screen bg-[#0a141d]"> <!-- Override background -->
            <!-- Navbar -->
            @include('components.navbar')
            
            <!-- Contenido Principal -->
            <div id="main-content-area" class="content-wrapper flex-1 relative z-0">
                @yield('content')
            </div>
        </div>
        
        <!-- Modal Tailwind (Hidden by default) -->
        <div id="default-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/60 backdrop-blur-sm transition-opacity duration-300">
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

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const mainContent = document.getElementById("main-content-area");
                const modalEl = document.getElementById("default-modal");
                const modalBody = document.getElementById("modal-body");
                const modalTitle = document.getElementById("modal-title");
                
                // Función auxiliar para alternar modal
                window.toggleModal = function(show) {
                    if (show) {
                        modalEl.classList.remove('hidden');
                        modalEl.classList.add('flex');
                        modalEl.setAttribute('aria-hidden', 'false');
                        // Animation check could go here
                    } else {
                        modalEl.classList.add('hidden');
                        modalEl.classList.remove('flex');
                        modalEl.setAttribute('aria-hidden', 'true');
                    }
                }

                // Botones de cierre
                document.querySelectorAll('[data-modal-hide="default-modal"]').forEach(btn => {
                    btn.addEventListener('click', () => toggleModal(false));
                });

                // Cerrar al hacer click fuera
                modalEl.addEventListener('click', (e) => {
                    if (e.target === modalEl) toggleModal(false);
                });

                // Cargador AJAX
                window.ajaxLoad = function(url, target, isModal = false, shouldPushState = true) {
                    // Mostrar cargador si es contenido principal
                    if (!isModal) {
                        target.style.opacity = '0.5';
                    }

                    fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
                    .then(r => {
                        if (!r.ok) throw new Error("HTTP " + r.status);
                        return r.text();
                    })
                    .then(html => {
                        target.innerHTML = html;
                        target.style.opacity = '1';
                        if (isModal) toggleModal(true);
                        
                        // Re-adjuntar listeners para nuevo contenido (si es necesario)
                        // attachFormListeners(target);
                        
                        // Actualizar historial solo si se solicita
                        if (shouldPushState && url !== window.location.href) {
                            window.history.pushState({path: url}, '', url);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        target.innerHTML = `<div class="p-4 text-red-400 bg-red-900/20 rounded-xl border border-red-500/30">Error al cargar contenido: ${err.message}. Inténtalo de nuevo.</div>`;
                        target.style.opacity = '1';
                    });
                }

                // Lógica de estado activo inicial eliminada

                // Interceptor de navegación
                document.addEventListener("click", function(e) {
                    const navLink = e.target.closest("a[data-load]");
                    if (navLink) {
                        e.preventDefault();
                        const mode = navLink.dataset.load; // 'section', 'modal', 'main' (legacy)
                        // Si el modo es 'section', usamos data-url. Si es main, usamos href.
                        const url = navLink.dataset.url || navLink.href;
                        const title = navLink.dataset.title || 'Detalles';

                        if (mode === 'modal') {
                            modalTitle.textContent = title;
                            // Resetear y mostrar cargador
                            modalBody.innerHTML = `
                                 <div class="text-center py-10">
                                    <svg class="animate-spin h-8 w-8 text-cyan-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <p class="text-cyan-200/70 animate-pulse">Cargando...</p>
                                </div>
                            `;
                            toggleModal(true);
                            // Obtener contenido para modal sin cambiar URL
                            ajaxLoad(url, modalBody, true /* isModal */, false /* shouldPushState */);

                        } else if (mode === 'section') {
                            // Navegación de sección (Usuarios, Escuelas, etc.)
                            // Cargar contenido en el área principal actualizando la URL (SIN MODAL)
                            ajaxLoad(url, mainContent, false /* esModal */, true /* actualizarUrl */);
                        } else {
                            // Enlaces 'main' heredados o normales
                             ajaxLoad(url, mainContent, false /* esModal */, true /* actualizarUrl */);
                        }
                        return;
                    }

                    // 2. Disparadores de Modal (Botones/enlaces dentro del contenido)
                    const modalTrigger = e.target.closest(".btn-modal");
                    if (modalTrigger) {
                        e.preventDefault();
                        const url = modalTrigger.dataset.url || modalTrigger.href;
                        const title = modalTrigger.dataset.title || 'Detalles';
                        
                        modalTitle.textContent = title;
                        // Mostrar cargador en modal
                        modalBody.innerHTML = `
                             <div class="text-center py-10">
                                <svg class="animate-spin h-8 w-8 text-cyan-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-cyan-200/70 animate-pulse">Cargando...</p>
                            </div>
                        `;
                        toggleModal(true);
                        
                        if (url) {
                            ajaxLoad(url, modalBody, true /* isModal */, false /* shouldPushState */);
                        }
                        return;
                    }
                });

                // Manejar botones Atrás/Adelante del navegador
                window.addEventListener('popstate', (e) => {
                     ajaxLoad(window.location.href, mainContent, false, false);
                });

                // 3. Manejar envío de formularios dentro del modal
                document.addEventListener("submit", function(e) {
                    const form = e.target.closest("#modal-body form");
                    if (form) {
                        e.preventDefault();
                        
                        const formData = new FormData(form);
                        const url = form.action;
                        const method = form.method || 'POST';

                        // Mostrar estado de carga (opcionalmente deshabilitar botón)
                        const submitBtn = form.querySelector('[type="submit"]');
                        if(submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white inline-block mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...`;
                        }

                        fetch(url, {
                            method: method,
                            body: formData,
                            headers: { 
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        })
                        .then(async r => {
                            const html = await r.text();
                            if (!r.ok && r.status !== 422) {
                                // If error (500, 404, etc), show the HTML response (Laravel error page) in the modal
                                modalBody.innerHTML = html;
                                throw new Error("Server Error"); // Throw to skip next then block
                            }
                            return html;
                        })
                        .then(html => {
                            // Actualizar contenido del modal con la respuesta (éxito o validación 422)
                            modalBody.innerHTML = html;
                            
                            // Si detectamos éxito
                            if (html.includes('alert-success') || html.includes('bg-green-500')) {
                               ajaxLoad(window.location.href, mainContent, false, false);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            if(submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerText = 'Reintentar';
                            }
                             // Mostrar error en el modal (prepend)
                             const errorDiv = document.createElement('div');
                             errorDiv.className = "p-4 mb-4 text-red-400 bg-red-900/20 rounded-xl border border-red-500/30";
                             errorDiv.innerText = `Error: ${err.message}`;
                             form.prepend(errorDiv);
                        });
                    }
                });
            });
        </script>    
    </body>
</html>





