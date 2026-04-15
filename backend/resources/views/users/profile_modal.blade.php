<div class="profile-modal-content text-white">

    {{-- BANNER + AVATAR --}}
    <div class="relative mb-20">
        <div class="relative overflow-hidden rounded-t-xl">
            <img src="{{ $user->banner ?? 'https://estaticos-cdn.prensaiberica.es/clip/3bffd319-f839-4e57-9ccb-b95ec474f104_source-aspect-ratio_default_0.jpg' }}"
                alt="banner"
                class="w-full h-65 object-cover brightness-75" />
            <div class="absolute inset-0 bg-linear-to-b from-transparent via-transparent to-[#15202b]"></div>
        </div>
        <img src="{{ $user->profile_picture ?? asset('logoTelamon.png') }}"
            alt="icono"
            class="absolute w-32.5 h-32.5 rounded-full border-4 border-[#15202b] bg-[#15202b] object-cover -bottom-16.25 left-8 shadow-xl ring-2 ring-sky-500/20"/>
    </div>

    <div class="px-8 pb-8">

        {{-- NOMBRE + BADGE + BOTÓN --}}
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
                <h2 class="text-3xl font-bold text-[#e7e9ea]">{{ $user->name }}</h2>
                @if(strtolower($user->role) === 'admin')
                    <img src="{{ asset('logoTelamon.png') }}" alt="Admin" class="w-7 h-7 object-contain" title="Administrador Certificado">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="#179cf0"><path d="M12.01 2.011a3.2 3.2 0 0 1 2.113 .797l.154 .145l.698 .698a1.2 1.2 0 0 0 .71 .341l.135 .008h1a3.2 3.2 0 0 1 3.195 3.018l.005 .182v1c0 .27 .092 .533 .258 .743l.09 .1l.697 .698a3.2 3.2 0 0 1 .147 4.382l-.145 .154l-.698 .698a1.2 1.2 0 0 0 -.341 .71l-.008 .135v1a3.2 3.2 0 0 1 -3.018 3.195l-.182 .005h-1a1.2 1.2 0 0 0 -.743 .258l-.1 .09l-.698 .697a3.2 3.2 0 0 1 -4.382 .147l-.154 -.145l-.698 -.698a1.2 1.2 0 0 0 -.71 -.341l-.135 -.008h-1a3.2 3.2 0 0 1 -3.195 -3.018l-.005 -.182v-1a1.2 1.2 0 0 0 -.258 -.743l-.09 -.1l-.697 -.698a3.2 3.2 0 0 1 -.147 -4.382l.145 -.154l.698 -.698a1.2 1.2 0 0 0 .341 -.71l.008 -.135v-1l.005 -.182a3.2 3.2 0 0 1 3.013 -3.013l.182 -.005h1a1.2 1.2 0 0 0 .743 -.258l.1 -.09l.698 -.697a3.2 3.2 0 0 1 2.269 -.944zm3.697 7.282a1 1 0 0 0 -1.414 0l-3.293 3.292l-1.293 -1.292l-.094 -.083a1 1 0 0 0 -1.32 1.497l2 2l.094 .083a1 1 0 0 0 1.32 -.083l4 -4l.083 -.094a1 1 0 0 0 -.083 -1.32z" /></svg>
                @endif
            </div>

            <button
                class="btn-modal flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-500/10 text-blue-400 text-sm font-semibold hover:bg-blue-500/20 transition-all cursor-pointer border border-blue-500/30 shadow-lg"
                data-url="{{ route('user.edit', $user->id) }}"
                data-title="Editar Perfil"
                data-load="modal"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Editar Perfil
            </button>
        </div>

        <p class="text-[#8b98a5] text-base mb-6">{{ '@' . strtolower(str_replace(' ', '', $user->name)) }}</p>

        {{-- DESCRIPCIÓN --}}
        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 mb-6 text-base leading-relaxed">
            {{ $user->description ?? 'El usuario no ha proporcionado una descripción todavía.' }}
        </div>

        {{-- STATS GRID (original) --}}
        <div class="grid grid-cols-2 gap-6 text-base mb-8 p-6 bg-white/5 rounded-2xl border border-white/10">
            <div class="flex flex-col gap-1.5">
                <span class="text-white/40 uppercase text-[11px] font-bold tracking-wider">Email</span>
                <span class="text-white/80 truncate font-medium">{{ $user->email }}</span>
            </div>
            <div class="flex flex-col gap-1.5">
                <span class="text-white/40 uppercase text-[11px] font-bold tracking-wider">Rol</span>
                <span class="text-purple-300 font-medium">{{ $user->role }}</span>
            </div>
            <div class="flex flex-col gap-1.5">
                <span class="text-white/40 uppercase text-[11px] font-bold tracking-wider">Reputación</span>
                <span class="text-yellow-500 font-bold">{{ $user->reputation }} pts</span>
            </div>
            <div class="flex flex-col gap-1.5">
                <span class="text-white/40 uppercase text-[11px] font-bold tracking-wider">Miembro desde</span>
                <span class="text-white/80 font-medium">{{ $user->created_at->format('M Y') }}</span>
            </div>
        </div>

        {{-- TABS con línea deslizante --}}
        <div class="relative flex border-b border-white/10 mt-6" id="modal-tabs">
            <span data-tab="posts"  class="tab-item flex-1 text-center text-[#8b98a5] text-base font-bold cursor-pointer hover:text-white transition-colors py-4 tracking-wide">Publicaciones</span>
            <span data-tab="photos" class="tab-item flex-1 text-center text-[#8b98a5] text-base font-bold cursor-pointer hover:text-white transition-colors py-4 tracking-wide">Fotos</span>
            <span data-tab="likes"  class="tab-item flex-1 text-center text-[#8b98a5] text-base font-bold cursor-pointer hover:text-white transition-colors py-4 tracking-wide">Me gusta</span>
            <div id="tab-line" class="absolute bottom-0 h-0.5 bg-blue-400 rounded-full transition-all duration-300 ease-in-out"></div>
        </div>

        {{-- CONTENIDO TABS --}}
        <div class="mt-8 tab-content" id="content-posts">
            <div class="bg-white/5 p-6 rounded-xl border border-white/10 text-center">
                <svg class="w-8 h-8 text-white/20 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <p class="text-[#8b98a5] text-sm">Aquí aparecerán todas las publicaciones que ha realizado el usuario.</p>
            </div>
        </div>

        <div class="mt-8 tab-content hidden" id="content-photos">
            <div class="grid grid-cols-3 gap-2">
                <div class="aspect-square bg-white/5 border border-white/10 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white/10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <div class="aspect-square bg-white/5 border border-white/10 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white/10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <div class="aspect-square bg-white/5 border border-white/10 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white/10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
            </div>
            <p class="text-[#8b98a5] text-center text-sm mt-4">Fotos se cargarán próximamente.</p>
        </div>

        <div class="mt-8 tab-content hidden" id="content-likes">
            <div class="bg-white/5 p-6 rounded-xl border border-white/10 text-center">
                <svg class="w-8 h-8 text-pink-500/20 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <p class="text-[#8b98a5] text-sm">Aquí se mostrarán las publicaciones que le gustan al usuario.</p>
            </div>
        </div>

    </div>
</div>

<script>
(function () {
    const tabs = document.querySelectorAll('.tab-item');
    const line = document.getElementById('tab-line');

    function moveLine(el) {
        line.style.width = el.offsetWidth + 'px';
        line.style.left  = el.offsetLeft  + 'px';
    }

    function activate(tab) {
        tabs.forEach(t => {
            t.classList.remove('text-white');
            t.classList.add('text-[#8b98a5]');
        });
        tab.classList.remove('text-[#8b98a5]');
        tab.classList.add('text-white');
        moveLine(tab);

        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.getElementById('content-' + tab.dataset.tab).classList.remove('hidden');
    }

    tabs.forEach(tab => tab.addEventListener('click', () => activate(tab)));
    activate(tabs[0]);
})();
</script>

<style>
    .profile-modal-content {
        max-width: 850px;
        margin: 0 auto;
        background: #15202b;
        border-radius: 16px;
        overflow: hidden;
    }
</style>
