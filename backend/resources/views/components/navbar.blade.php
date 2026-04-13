<!-- Mobile Header (Visible below lg) -->
<div class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-[#142b2b] border-b border-white/10 flex justify-between items-center px-4 z-[55] shadow-md">
    <button id="mobile-toggle" class="p-2 text-white/70 hover:text-white transition-colors active:scale-95">
        <x-admin.constants.icons name="menu" />
    </button>
    <div class="flex items-center gap-2">
        <span class="text-white font-bold text-lg">Telamo<span class="text-[#a0c4d4]">Net</span></span>
        @if(app()->environment('local'))
            <img class="w-8 h-8" src="http://localhost:5173/src/assets/logo/logoTelamon.png" alt="Logo">
        @else
            <img class="w-8 h-8" src="{{ asset('assets/logo/logoTelamon.png') }}" alt="Logo">
        @endif
    </div>
</div>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/60 z-[58] lg:hidden transition-opacity duration-300 opacity-0 ease-out"></div>

<nav class="fixed lg:sticky top-0 inset-y-0 left-0 w-[300px] h-screen bg-gradient-to-b from-[#142b2b] to-[#0a141d] shadow-[-5px_0px_20px_rgba(0,0,0,0.6)] z-[60] transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-out flex flex-col pl-5 pr-5 overflow-y-auto overflow-x-hidden" id="principalNav">
    <div class="hidden lg:flex flex-row items-center gap-3 mb-4 mt-4">
        @if(app()->environment('local'))
            <img class="w-[65px] h-[70px]" src="http://localhost:5173/src/assets/logo/logoTelamon.png" alt="Logo">
        @else
            <img class="w-[65px] h-[70px]" src="{{ asset('assets/logo/logoTelamon.png') }}" alt="Logo">
        @endif
        <h1 class="font-bold text-[20px] text-white tracking-wide">Telamo<span class="text-[#a0c4d4]">Net</span></h1>
    </div>
    <!-- Spacer for mobile drawer content to not start at the very top if branding is hidden -->
    <div class="lg:hidden h-8"></div>


<x-navbar-link title="Inicio" to="#" data-url="/admin" data-load="section">
    <x-admin.constants.icons name="home" />
</x-navbar-link>

<x-navbar-link title="Usuarios" to="#" data-url="/admin/users" data-load="section" data-title="Gestión de Usuarios">
    <x-admin.constants.icons name="users" />
</x-navbar-link>

<x-navbar-link title="Centros educativos" to="#" data-url="/admin/educational-centers" data-load="section" data-title="Centros Educativos">
    <x-admin.constants.icons name="school" />
</x-navbar-link>

<x-navbar-link title="Eventos" to="#" data-url="/admin/events" data-load="section" data-title="Eventos">
    <x-admin.constants.icons name="calendar" />
</x-navbar-link>

<x-navbar-link title="Preguntas / Foro" to="#" data-url="/admin/questions" data-load="section" data-title="Foro de Preguntas">
    <x-admin.constants.icons name="question" />
</x-navbar-link>

<x-navbar-link title="Reputación y logros" to="#" data-url="/admin/badges" data-load="section" data-title="Reputación y Logros">
    <x-admin.constants.icons name="award" />
</x-navbar-link>

<x-navbar-link title="Chats" to="#" data-url="/admin/group-chats" data-load="section" data-title="Chats Grupales">
    <x-admin.constants.icons name="messages" />
</x-navbar-link>

@php
    $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
@endphp
<x-navbar-link title="Volver" to="{{ rtrim($frontendUrl, '/') }}/home">
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
            overlay.classList.remove('hidden', 'ease-in');
            overlay.classList.add('ease-out');
            setTimeout(() => overlay.classList.add('opacity-100'), 10);
            sidebar.classList.remove('-translate-x-full', 'ease-in');
            sidebar.classList.add('translate-x-0', 'ease-out');
        } else {
            overlay.classList.remove('opacity-100', 'ease-out');
            overlay.classList.add('ease-in');
            sidebar.classList.remove('translate-x-0', 'ease-out');
            sidebar.classList.add('-translate-x-full', 'ease-in');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }

    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
            const isHidden = sidebar.classList.contains('-translate-x-full');
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
