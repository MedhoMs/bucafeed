document.addEventListener("DOMContentLoaded", function () {
    // Auto-hide existing toasts
    document.querySelectorAll('.admin-toast').forEach(t => {
        t.style.transition = 'all 0.5s ease';
        setTimeout(() => {
            t.style.opacity = '0';
            t.style.transform = 'translateY(-20px)';
            setTimeout(() => t.remove(), 500);
        }, 4000);
    });

    const mainContent = document.getElementById("main-content-area");
    const modalEl = document.getElementById("default-modal");
    const modalBody = document.getElementById("modal-body");
    const modalTitle = document.getElementById("modal-title");

    if (!modalEl) return;

    // Función auxiliar para alternar modal
    window.toggleModal = function (show) {
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
    modalEl.addEventListener('click', (e) => {
        if (e.target === modalEl) toggleModal(false);
    });

    // Cargador AJAX
    window.ajaxLoad = function (url, target, isModal = false, shouldPushState = true) {
        if (!target) return;

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
                    window.history.pushState({ path: url }, '', url);
                }
            })
            .catch(err => {
                console.error(err);
                target.innerHTML = `<div class="p-4 text-red-400 bg-red-900/20 rounded-xl border border-red-500/30">Error al cargar contenido: ${err.message}. Inténtalo de nuevo.</div>`;
                target.style.opacity = '1';
            });
    }

    // Interceptor de navegación
    document.addEventListener("click", function (e) {
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

            const isInModal = navLink.closest('#modal-body');

            if (isInModal) {
                // Si la navegación ocurre dentro del modal, recargamos el propio modal
                ajaxLoad(url, modalBody, true, false);
            } else if (mode === 'modal') {
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

            } else if (mode === 'section') {
                ajaxLoad(url, mainContent, false, true);
            } else {
                ajaxLoad(url, mainContent, false, true);
            }
            return;
        }

        // 2. Disparadores de Modal
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

    // Manejar botones Atrás/Adelante
    window.addEventListener('popstate', (e) => {
        ajaxLoad(window.location.href, mainContent, false, false);
    });

    // 3. Manejar envío de formularios dentro del modal
    document.addEventListener("submit", function (e) {
        const form = e.target.closest("#modal-body form");
        if (form) {
            e.preventDefault();

            const formData = new FormData(form);
            const url = form.action;
            const method = form.method || 'POST';

            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `<svg class="animate-spin h-4 w-4 text-white inline-block mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...`;
            }

            fetch(url, {
                method: method,
                body: formData,
                headers: { "X-Requested-With": "XMLHttpRequest" }
            })
                .then(async r => {
                    const text = await r.text();
                    try {
                        const data = JSON.parse(text);
                        return { isJson: true, data };
                    } catch (e) {
                        return { isJson: false, html: text };
                    }
                })
                .then(res => {
                    if (res.isJson && res.data.success) {
                        if (url.includes('banned-words')) {
                            modalTitle.textContent = 'Palabras Vetadas';
                            ajaxLoad('/admin/banned-words', modalBody, true, false);
                            ajaxLoad(window.location.href, mainContent, false, false);
                        } else {
                            ajaxLoad(window.location.href, mainContent, false, false);
                            toggleModal(false);
                        }
                    } else {
                        if (res.isJson) {
                            let errorMsg = res.data.message || "Error desconocido";
                            let errorList = "";
                            if (res.data.errors) {
                                errorList = `<ul class="list-disc list-inside mt-2 text-sm">`;
                                for (let field in res.data.errors) {
                                    errorList += `<li>${res.data.errors[field][0]}</li>`;
                                }
                                errorList += `</ul>`;
                            }

                            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalText; }

                            const oldError = form.querySelector('.validation-error-alert');
                            if (oldError) oldError.remove();

                            const errorDiv = document.createElement('div');
                            errorDiv.className = "validation-error-alert p-4 mb-4 text-red-400 bg-red-900/20 rounded-xl border border-red-500/30";
                            errorDiv.innerHTML = `<strong>${errorMsg}</strong>${errorList}`;
                            form.prepend(errorDiv);

                        } else {
                            modalBody.innerHTML = res.html;

                            if (res.html.includes('bg-green-500') || res.html.includes('Operación completada con éxito')) {
                                if (url.includes('banned-words')) {
                                    modalTitle.textContent = 'Palabras Vetadas';
                                    ajaxLoad('/admin/banned-words', modalBody, true, false);
                                    ajaxLoad(window.location.href, mainContent, false, false);
                                } else {
                                    ajaxLoad(window.location.href, mainContent, false, false);
                                }
                            }

                            modalBody.querySelectorAll('script').forEach(oldScript => {
                                const newScript = document.createElement('script');
                                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            });
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerText = 'Reintentar'; }
                    const errorDiv = document.createElement('div');
                    errorDiv.className = "p-4 mb-4 text-red-400 bg-red-900/20 rounded-xl border border-red-500/30";
                    errorDiv.innerText = `Error: ${err.message}`;
                    form.prepend(errorDiv);
                });
        }
    });
});
