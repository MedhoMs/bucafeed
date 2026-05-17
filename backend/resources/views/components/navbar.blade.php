<!-- Mobile Header (Visible below lg) -->
<div class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-[#142b2b] border-b border-white/10 flex justify-between items-center px-4 z-55 shadow-md">
    <button id="mobile-toggle" class="p-2 text-white/70 hover:text-white transition-colors">
        <x-admin.constants.icons name="menu" />
    </button>
    <div class="flex items-center gap-2">
        <img class="w-8 h-8" src="{{ asset('logoTelamon.png') }}" alt="Logo">
        <span class="text-white font-bold text-lg">Telamo<span class="text-[#a0c4d4]">Net</span></span>
    </div>
</div>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/60 z-58 lg:hidden transition-opacity duration-300 opacity-0"></div>

<nav class="fixed lg:sticky top-0 inset-y-0 left-0 w-72 min-w-[288px] h-screen bg-linear-to-b from-[#142b2b] to-[#0a141d] border-r border-white/5 z-60 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col shrink-0 overflow-y-auto" id="principalNav">
    
    <!-- Logo Area -->
    <div class="flex flex-row items-center gap-4 p-6 pt-8 mb-6">
        <img class="w-16.25 h-17.5 object-contain" src="{{ asset('logoTelamon.png') }}" alt="Logo">
        <h1 class="font-bold text-[22px] text-white tracking-wide">Telamo<span class="text-[#a0c4d4]">Net</span></h1>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 px-4 space-y-1">
        <x-navbar-link title="Inicio" to="/admin" data-load="section">
            <x-admin.constants.icons name="home" />
        </x-navbar-link>

        <x-navbar-link title="Usuarios" to="/admin/users" data-load="section" data-title="Gestión de Usuarios">
            <x-admin.constants.icons name="users" />
        </x-navbar-link>

        <x-navbar-link title="Centros educativos" to="/admin/educational-centers" data-load="section" data-title="Centros Educativos">
            <x-admin.constants.icons name="school" />
        </x-navbar-link>

        <x-navbar-link title="Eventos" to="/admin/events" data-load="section" data-title="Eventos">
            <x-admin.constants.icons name="calendar" />
        </x-navbar-link>

        <x-navbar-link title="Preguntas / Foro" to="/admin/questions" data-load="section" data-title="Foro de Preguntas">
            <x-admin.constants.icons name="question" />
        </x-navbar-link>

        <x-navbar-link title="Publicaciones" to="/admin/publications" data-load="section" data-title="Publicaciones activas">
            <x-admin.constants.icons name="article" />
        </x-navbar-link>

        <x-navbar-link title="Reputación y logros" to="/admin/badges" data-load="section" data-title="Reputación y Logros">
            <x-admin.constants.icons name="award" />
        </x-navbar-link>

        <x-navbar-link title="Chats" to="/admin/group-chats" data-load="section" data-title="Chats Grupales">
            <x-admin.constants.icons name="messages" />
        </x-navbar-link>
    </div>

    <!-- Bottom Actions -->
    <div class="p-4 border-t border-white/5">
        @php
            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        @endphp
        <x-navbar-link title="Volver" to="{{ rtrim($frontendUrl, '/') }}/home">
            <x-admin.constants.icons name="arrow-left" />
        </x-navbar-link>
    </div>
</nav>

<script>
    // Mobile Sidebar Logic
    const mobileToggle = document.getElementById('mobile-toggle');
    const sidebar = document.getElementById('principalNav');
    const overlay = document.getElementById('sidebar-overlay');

    function toggleMobileSidebar(show) {
        if (show) {
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.add('opacity-100'), 10);
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
        } else {
            overlay.classList.remove('opacity-100');
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
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

    // Close on navigation in mobile
    sidebar.addEventListener('click', (e) => {
        if (window.innerWidth < 1024 && e.target.closest('a')) {
            toggleMobileSidebar(false);
        }
    });
</script>

