<div class="profile-modal-content text-white">

    {{-- BANNER + ICON --}}
    <div class="relative mb-20">
        <div class="relative overflow-hidden rounded-t-xl">
            <img src="{{ !empty($center->banner) ? $center->banner : asset('uploads/centers/default_banner.png') }}"
                alt="banner"
                class="w-full h-60 object-cover brightness-75" />
            <div class="absolute inset-0 bg-linear-to-b from-transparent via-transparent to-[#15202b]"></div>
        </div>
        <img src="{{ !empty($center->icon) ? $center->icon : asset('logoTelamon.png') }}"
            alt="icono"
            class="absolute w-32.5 h-32.5 rounded-3xl border-4 border-[#15202b] bg-[#15202b] object-cover -bottom-16.25 left-8 shadow-xl ring-2 ring-emerald-500/20 shadow-emerald-500/10"/>

        <div class="absolute -bottom-12.5 right-8">
            <button
                class="btn-modal flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-500/10 text-emerald-400 text-sm font-bold hover:bg-emerald-500/20 transition-all cursor-pointer border border-emerald-500/30 shadow-lg shadow-emerald-500/5 group"
                data-url="{{ route('educational_centers.edit', $center->id) }}"
                data-title="Editar Centro"
                data-load="modal"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 group-hover:rotate-12 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Gestionar Centro
            </button>
        </div>
    </div>

    <div class="px-8 pb-8">
        {{-- NOMBRE + TIPO --}}
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1">
                <h2 class="text-3xl font-black text-white tracking-tight">{{ $center->name }}</h2>
                <div class="bg-emerald-500/20 p-1.5 rounded-lg border border-emerald-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M3 7v1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7H3l2-4h14l2 4"/><path d="M5 21V10.85"/><path d="M19 21V10.85"/><path d="M9 21v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4"/></svg>
                </div>
            </div>
            <p class="text-emerald-400/80 font-semibold text-sm flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ App\Models\EducationalCenter::$niveles_disponibles[$center->type] ?? $center->type }}
            </p>
        </div>

        {{-- INFO GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white/5 p-4 rounded-2xl border border-white/10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center border border-blue-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div>
                    <p class="text-white/40 text-[10px] uppercase font-bold tracking-widest leading-none mb-1">Ubicación</p>
                    <p class="text-white font-medium text-sm">{{ $center->location }}</p>
                </div>
            </div>

            <div class="bg-white/5 p-4 rounded-2xl border border-white/10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center border border-purple-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <p class="text-white/40 text-[10px] uppercase font-bold tracking-widest leading-none mb-1">Responsable</p>
                    <p class="text-white font-medium text-sm truncate">{{ $center->adminUser->name ?? 'Sin asignar' }}</p>
                </div>
            </div>

            <div class="bg-white/5 p-4 rounded-2xl border border-white/10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M8 7h6"/><path d="M8 11h8"/></svg>
                </div>
                <div>
                    <p class="text-white/40 text-[10px] uppercase font-bold tracking-widest leading-none mb-1">Carga Académica</p>
                    <p class="text-white font-medium text-sm">{{ $center->cycles->count() }} Ciclos activos</p>
                </div>
            </div>
        </div>

        {{-- TABS --}}
        <div class="relative flex border-b border-white/10" id="center-modal-tabs">
            <span data-tab="cycles" class="tab-item flex-1 text-center text-white/40 text-sm font-bold cursor-pointer hover:text-white transition-colors py-4">Ciclos Formativos</span>
            <span data-tab="teachers" class="tab-item flex-1 text-center text-white/40 text-sm font-bold cursor-pointer hover:text-white transition-colors py-4">Cuerpo Docente</span>
            <span data-tab="students" class="tab-item flex-1 text-center text-white/40 text-sm font-bold cursor-pointer hover:text-white transition-colors py-4">Alumnado</span>
            <div id="tab-line-center" class="absolute bottom-0 h-0.5 bg-emerald-500 rounded-full transition-all duration-300 ease-in-out"></div>
        </div>

        {{-- CONTENIDO TABS --}}
        <div class="mt-6 tab-content-center min-h-50" id="content-cycles">
            @if($center->cycles->count() > 0)
                <div class="grid grid-cols-1 gap-2">
                    @foreach($center->cycles as $cycle)
                        <div class="bg-emerald-500/5 border border-emerald-500/10 p-3 rounded-xl flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                <span class="text-emerald-400 text-xs font-bold">{{ substr($cycle->name, 0, 1) }}</span>
                            </div>
                            <span class="text-white/80 text-sm font-medium">{{ $cycle->name }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white/5 p-8 rounded-2xl border border-white/10 text-center">
                    <p class="text-white/30 text-sm italic">Este centro no tiene ciclos formativos registrados.</p>
                </div>
            @endif
        </div>

        <div class="mt-6 tab-content-center hidden" id="content-teachers">
            @if($center->teachers->count() > 0)
                <div class="grid grid-cols-2 gap-3">
                    @foreach($center->teachers as $teacher)
                        <div class="bg-white/5 p-3 rounded-xl border border-white/10 flex items-center gap-3">
                            <img src="{{ $teacher->profile_picture ?? asset('logoTelamon.png') }}" class="w-8 h-8 rounded-full object-cover">
                            <div class="flex flex-col">
                                <span class="text-white text-xs font-semibold">{{ $teacher->name }}</span>
                                <span class="text-white/40 text-[10px]">{{ $teacher->email }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white/5 p-8 rounded-2xl border border-white/10 text-center">
                    <p class="text-white/30 text-sm italic">No hay docentes vinculados a este centro.</p>
                </div>
            @endif
        </div>

        <div class="mt-6 tab-content-center hidden" id="content-students">
            @if($center->students->count() > 0)
                <div class="grid grid-cols-2 gap-3">
                    @foreach($center->students as $student)
                        <div class="bg-white/5 p-3 rounded-xl border border-white/10 flex items-center gap-3">
                            <img src="{{ $student->profile_picture ?? asset('logoTelamon.png') }}" class="w-10 h-10 rounded-full object-cover">
                            <div class="flex flex-col">
                                <span class="text-white text-xs font-semibold">{{ $student->name }}</span>
                                <span class="text-white/40 text-[10px] truncate max-w-30">{{ $student->email }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white/5 p-8 rounded-2xl border border-white/10 text-center">
                    <p class="text-white/30 text-sm italic">No hay alumnos matriculados en este centro.</p>
                </div>
            @endif
        </div>

    </div>
</div>

<script>
(function () {
    const tabs = document.querySelectorAll('.tab-item');
    const line = document.getElementById('tab-line-center');

    function moveLine(el) {
        if (!line) return;
        line.style.width = el.offsetWidth + 'px';
        line.style.left  = el.offsetLeft  + 'px';
    }

    function activate(tab) {
        tabs.forEach(t => {
            t.classList.remove('text-white');
            t.classList.add('text-white/40');
        });
        tab.classList.remove('text-white/40');
        tab.classList.add('text-white');
        moveLine(tab);

        document.querySelectorAll('.tab-content-center').forEach(c => c.classList.add('hidden'));
        const targetId = 'content-' + tab.dataset.tab;
        const target = document.getElementById(targetId);
        if (target) target.classList.remove('hidden');
    }

    tabs.forEach(tab => tab.addEventListener('click', () => activate(tab)));
    if (tabs.length > 0) activate(tabs[0]);
})();
</script>

<style>
    .profile-modal-content {
        max-width: 900px;
        margin: 0 auto;
        background: #15202b;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 0 50px -10px rgba(0,0,0,0.5);
    }
</style>
