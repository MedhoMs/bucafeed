@if(!request()->ajax())
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>Panel Adminstrador</title>

        @if(app()->environment('local'))
            <link rel="icon" type="image/png" href="http://localhost:5174/src/assets/logo/logoTelamon.png">
        @else
            <link rel="icon" type="image/png" href="{{ asset('logoTelamon.png') }}">
        @endif
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        

        {{-- Constants --}}
        <x-admin.constants.colors />

        @if(app()->environment('local'))
            <!-- Modo Desarrollo: Carga desde el servidor de Vite -->
            <script type="module" src="http://localhost:5174/@@vite/client"></script>
            <link rel="stylesheet" href="http://localhost:5174/backend/resources/sytles/style.css">
            <link rel="stylesheet" href="http://localhost:5174/backend/resources/sytles/main.css">
        @else
            <!-- Modo Producción: Carga los assets compilados -->
            @vite(['resources/css/app.css', 'resources/js/app.js'], 'frontend')
        @endif
    </head>
    <body class="hold-transition sidebar-mini">
        <div id="app" class="wrapper flex min-h-screen bg-[#0a141d]"> <!-- Override background -->
            <!-- Navbar -->
            <div class="shrink-0 z-50">
                @include('components.navbar')
            </div>
            
            <!-- Contenido Principal -->
            <div id="main-content-area" class="content-wrapper flex-1 relative z-0 min-w-0">
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

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const mainContent = document.getElementById("main-content-area");
                const modalEl = document.getElementById("default-modal");
                const modalBody = document.getElementById("modal-body");
                const modalTitle = document.getElementById("modal-title");
                
                // Función auxiliar para alternar modal
                window.toggleModal = function(show) {
                    if (!modalEl) return;
                    if (show) {
                        modalEl.classList.remove('hidden');
                        modalEl.classList.add('flex');
                        modalEl.setAttribute('aria-hidden', 'false');
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
                if (modalEl) {
                    modalEl.addEventListener('click', (e) => {
                        if (e.target === modalEl) toggleModal(false);
                    });
                }

                // Cargador AJAX
                window.ajaxLoad = function(url, target, isModal = false, shouldPushState = true) {
                    if (!target) return;
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
                        
                        // Execute scripts in the loaded HTML
                        const scripts = target.querySelectorAll('script');
                        scripts.forEach(oldScript => {
                            const newScript = document.createElement('script');
                            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                            newScript.innerHTML = oldScript.innerHTML;
                            if (oldScript.parentNode) {
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            }
                        });

                        if (isModal) toggleModal(true);
                        
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

                // Interceptor de navegación
                document.addEventListener("click", function(e) {
                    // Logic for custom dropdowns
                    const toggle = e.target.closest('.dropdown-toggle');
                    if (toggle) {
                        const menu = toggle.nextElementSibling;
                        // Close other menus
                        document.querySelectorAll('.dropdown-menu').forEach(m => {
                            if (m !== menu) m.classList.add('hidden');
                        });
                        menu.classList.toggle('hidden');
                        return;
                    }

                    // Close menus when clicking outside
                    if (!e.target.closest('.filter-dropdown')) {
                        document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
                    }

                    const navLink = e.target.closest("a[data-load]");
                    if (navLink) {
                        e.preventDefault();
                        const mode = navLink.dataset.load;
                        const url = navLink.dataset.url || navLink.href;
                        const title = navLink.dataset.title || 'Detalles';

                        if (mode === 'modal') {
                            modalTitle.textContent = title;
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
                            ajaxLoad(url, modalBody, true, false);

                        } else if (mode === 'section' || mode === 'main') {
                            ajaxLoad(url, mainContent, false, true);
                        }
                        return;
                    }

                    const modalTrigger = e.target.closest(".btn-modal");
                    if (modalTrigger) {
                        e.preventDefault();
                        const url = modalTrigger.dataset.url || modalTrigger.href;
                        const title = modalTrigger.dataset.title || 'Detalles';
                        
                        modalTitle.textContent = title;
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
                            ajaxLoad(url, modalBody, true, false);
                        }
                        return;
                    }
                });

                window.addEventListener('popstate', (e) => {
                     ajaxLoad(window.location.href, mainContent, false, false);
                });

                // Manejar envío de formularios dentro del modal
                document.addEventListener("submit", function(e) {
                    const form = e.target.closest("#modal-body form");
                    if (form) {
                        e.preventDefault();
                        
                        const formData = new FormData(form);
                        const url = form.action;
                        const method = form.method || 'POST';

                        const submitBtn = form.querySelector('[type="submit"]');
                        if(submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.dataset.originalText = submitBtn.innerHTML;
                            submitBtn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white inline-block mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...`;
                        }

                        // Limpiar errores previos
                        const existingErrors = modalBody.querySelector('.validation-errors');
                        if (existingErrors) existingErrors.remove();

                        fetch(url, {
                            method: method,
                            body: formData,
                            headers: { 
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        })
                        .then(async r => {
                            const responseText = await r.text();
                            let data = null;
                            try { data = JSON.parse(responseText); } catch(e) { }

                            if (!r.ok) {
                                if (r.status === 422 && data && data.errors) {
                                    // Error de validación estructurado
                                    let errorList = '<div class="validation-errors bg-red-500/10 border border-red-500/20 p-4 rounded-xl mb-6"><ul class="text-red-400 text-sm list-disc list-inside">';
                                    for (let field in data.errors) {
                                        data.errors[field].forEach(msg => {
                                            errorList += `<li>${msg}</li>`;
                                        });
                                    }
                                    errorList += '</ul></div>';
                                    modalBody.insertAdjacentHTML('afterbegin', errorList);
                                    
                                    if(submitBtn) {
                                        submitBtn.disabled = false;
                                        submitBtn.innerHTML = submitBtn.dataset.originalText || 'Reintentar';
                                    }
                                    return null; // Detener flujo
                                }
                                
                                // Otros errores (500, etc)
                                modalBody.innerHTML = responseText;
                                throw new Error("Error del servidor (HTTP " + r.status + ")");
                            }
                            
                            return { html: responseText, json: data };
                        })
                        .then(res => {
                            if (!res) return;

                            // Si es JSON de éxito
                            if (res.json && res.json.exito) {
                                // En lugar de el overlay, volvemos a cargar la vista para que salga el formulario con el mensaje
                                ajaxLoad(url, modalBody, true, false);
                                ajaxLoad(window.location.href, mainContent, false, false);
                                return;
                            }

                            // Si es HTML, inyectar directamente
                            modalBody.innerHTML = res.html;
                            
                            // Si detectamos éxito por texto/clase en el HTML
                            if (res.html.includes('alert-success') || res.html.includes('bg-green-500') || res.html.includes('exito') || res.html.includes('eliminado')) {
                                ajaxLoad(window.location.href, mainContent, false, false);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            if(submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = submitBtn.dataset.originalText || 'Reintentar';
                            }
                        });
                    }
                });
            });
        </script>
    
    </body>
</html>
@endif
