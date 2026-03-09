<!-- Mobile Header (Visible below lg) -->
<div class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-[#142b2b] border-b border-white/10 flex items-center justify-between px-4 z-[55] shadow-md">
    <div class="flex items-center gap-2">
        @if(app()->environment('local'))
            <img class="w-8 h-8" src="http://localhost:5173/src/assets/logo/logoTelamon.png" alt="Logo">
        @else
            <img class="w-8 h-8" src="{{ asset('assets/logo/logoTelamon.png') }}" alt="Logo">
        @endif
        <span class="text-white font-bold text-lg">Telamo<span class="text-[#a0c4d4]">Net</span></span>
    </div>
    <button id="mobile-toggle" class="p-2 text-white/70 hover:text-white transition-colors active:scale-95">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>
</div>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[58] lg:hidden transition-opacity duration-300 opacity-0"></div>

<nav class="fixed lg:sticky top-0 inset-y-0 right-0 lg:left-0 w-[300px] h-screen bg-gradient-to-b from-[#142b2b] to-[#0a141d] shadow-[-5px_0px_20px_rgba(0,0,0,0.6)] z-[60] transform translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col pl-5 pr-5 overflow-y-auto overflow-x-hidden" id="principalNav">
    <div class="hidden lg:flex flex-row items-center gap-3 mb-4 mt-4">
        @if(app()->environment('local'))
            <img class="w-[65px] h-[70px]" src="http://localhost:5174/src/assets/logo/logoTelamon.png" alt="Logo">
        @else
            <!-- En producción, los assets se copian a public/assets via Dockerfile -->
            <img class="w-[65px] h-[70px]" src="/assets/logo/logoTelamon.png" alt="Logo">
        @endif
        <h1 class="font-bold text-[20px] text-white tracking-wide">Telamo<span class="text-[#a0c4d4]">Net</span></h1>
    </div>
    <!-- Spacer for mobile drawer content to not start at the very top if branding is hidden -->
    <div class="lg:hidden h-8"></div>


<x-navbar-link title="Inicio" to="#" data-url="/admin" data-load="section">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-home"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
</x-navbar-link>

<x-navbar-link title="Usuarios" to="#" data-url="/admin/users" data-load="section" data-title="Gestión de Usuarios">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-users"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="9" cy="7" r="4" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
</x-navbar-link>

<x-navbar-link title="Centros educativos" to="#" data-url="/admin/schools" data-load="section" data-title="Centros Educativos">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-school"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M22 9l-10 -4l-10 4l10 4l10 -4v6" /><path d="M6 10.6v5.4a6 6 0 0 0 12 0v-5.4" /><path d="M12 20v-10" /></svg>
</x-navbar-link>

<x-navbar-link title="Eventos" to="#" data-url="/admin/events" data-load="section" data-title="Eventos">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-calendar-event"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="4" y="5" width="16" height="16" rx="2" /><line x1="16" y1="3" x2="16" y2="7" /><line x1="8" y1="3" x2="8" y2="7" /><line x1="4" y1="11" x2="20" y2="11" /><rect x="8" y="15" width="2" height="2" /></svg>
</x-navbar-link>

<x-navbar-link title="Preguntas / Foro" to="#" data-url="/admin/questions" data-load="section" data-title="Foro de Preguntas">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-message-question"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M14 18l-2 3l-2 -3h-3a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h10a3 3 0 0 1 3 3v4.5" /><path d="M19 22v.01" /><path d="M19 19a2.003 2.003 0 0 0 .914 -3.782a1.98 1.98 0 0 0 -2.414 .482" /></svg>
</x-navbar-link>

<x-navbar-link title="Reputación y logros" to="#" data-url="/admin/badges" data-load="section" data-title="Reputación y Logros">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-award"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="9" r="6" /><path d="M9 14.2l-3 6.8l6 -2l6 2l-3 -6.8" /></svg>
</x-navbar-link>

<x-navbar-link title="Chats" to="#" data-url="/admin/group-chats" data-load="section" data-title="Chats Grupales">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-messages"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M21 14l-3 -3h-7a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1h9a1 1 0 0 1 1 1v10" /><path d="M14 15v2a1 1 0 0 1 -1 1h-7l-3 3v-10a1 1 0 0 1 1 -1h2" /></svg>
</x-navbar-link>

<x-navbar-link title="Volver" to="{{ config('app.frontend_url') }}/home">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-arrow-left"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="5" y1="12" x2="19" y2="12" /><polyline points="5 12 11 18" /><polyline points="5 12 11 6" /></svg>
</x-navbar-link>


</nav>

<script>
    function toggleDotsPopup(event) {
        event.preventDefault();
        event.stopPropagation();
        const popup = document.getElementById('popupMenu');
        
        if (popup.classList.contains('hidden')) {
            // Show
            popup.classList.remove('hidden');
            // Small delay to allow transition to work
            setTimeout(() => {
                popup.classList.remove('opacity-0', 'translate-y-[5px]');
                popup.classList.add('opacity-100', 'translate-y-0');
            }, 10);
        } else {
            // Hide
            popup.classList.remove('opacity-100', 'translate-y-0');
            popup.classList.add('opacity-0', 'translate-y-[5px]');
            setTimeout(() => {
                popup.classList.add('hidden');
            }, 200);
        }
    }

    // Close on click outside
    document.addEventListener('click', function(event) {
        const popup = document.getElementById('popupMenu');
        const dots = document.getElementById('dots');
        if (popup && !popup.classList.contains('hidden') && !popup.contains(event.target) && event.target !== dots) {
            popup.classList.remove('opacity-100', 'translate-y-0');
            popup.classList.add('opacity-0', 'translate-y-[5px]');
            setTimeout(() => {
                popup.classList.add('hidden');
            }, 200);
        }
    });

    // Mobile Sidebar Logic
    const mobileToggle = document.getElementById('mobile-toggle');
    const sidebar = document.getElementById('principalNav');
    const overlay = document.getElementById('sidebar-overlay');

    function toggleMobileSidebar(show) {
        if (show) {
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.add('opacity-100'), 10);
            sidebar.classList.remove('translate-x-full');
            sidebar.classList.add('translate-x-0');
        } else {
            overlay.classList.remove('opacity-100');
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('translate-x-full');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }

    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
            const isHidden = sidebar.classList.contains('translate-x-full');
            toggleMobileSidebar(isHidden);
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => toggleMobileSidebar(false));
    }

    // Close on navigation
    sidebar.addEventListener('click', (e) => {
        if (window.innerWidth < 1024 && (e.target.closest('a') || e.target.closest('button'))) {
            // Don't close if clicking dots
            if (e.target.closest('#dots')) return;
            toggleMobileSidebar(false);
        }
    });
</script>
