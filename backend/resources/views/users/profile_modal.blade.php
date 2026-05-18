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

        <p class="text-[#8b98a5] text-base mb-6">{{ '@' . explode('@', $user->email)[0] }}</p>

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
                <span class="text-purple-300 font-medium">{{ $user->role_name }}</span>
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
            <span data-tab="questions" class="tab-item flex-1 text-center text-[#8b98a5] text-base font-bold cursor-pointer hover:text-white transition-colors py-4 tracking-wide">Preguntas</span>
            <span data-tab="answers"   class="tab-item flex-1 text-center text-[#8b98a5] text-base font-bold cursor-pointer hover:text-white transition-colors py-4 tracking-wide">Respuestas</span>
            <span data-tab="events"    class="tab-item flex-1 text-center text-[#8b98a5] text-base font-bold cursor-pointer hover:text-white transition-colors py-4 tracking-wide">Eventos</span>
            <div id="tab-line" class="absolute bottom-0 h-0.5 bg-blue-400 rounded-full transition-all duration-300 ease-in-out"></div>
        </div>

        {{-- CONTENIDO TABS --}}
        <div class="mt-8 tab-content" id="content-questions">
            @if($user->questions->isEmpty())
                <div class="bg-white/5 p-6 rounded-xl border border-white/10 text-center">
                    <svg class="w-8 h-8 text-white/20 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <p class="text-[#8b98a5] text-sm">Este usuario no ha publicado ninguna pregunta todavía.</p>
                </div>
            @else
                <div class="flex flex-col gap-4 max-h-100 overflow-y-auto pr-2">
                    @foreach($user->questions as $question)
                        <div class="bg-white/5 p-5 rounded-2xl border border-white/10 hover:border-cyan-500/30 transition-all duration-300 text-left">
                            <div class="flex justify-between items-start mb-2 gap-4">
                                <h4 class="text-lg font-bold text-white leading-snug line-clamp-1 pr-4">{{ $question->title }}</h4>
                                <span class="px-2.5 py-1 text-xs font-bold bg-cyan-500/10 text-cyan-400 rounded-full border border-cyan-500/20 shrink-0">
                                    {{ $question->answer_count }} {{ $question->answer_count == 1 ? 'respuesta' : 'respuestas' }}
                                </span>
                            </div>
                            <p class="text-white/60 text-sm mb-3 line-clamp-2">{{ $question->content }}</p>
                            <div class="text-[11px] text-white/40 font-bold uppercase tracking-wider">
                                {{ $question->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-8 tab-content hidden" id="content-answers">
            @if($user->answers->isEmpty())
                <div class="bg-white/5 p-6 rounded-xl border border-white/10 text-center">
                    <svg class="w-8 h-8 text-white/20 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    <p class="text-[#8b98a5] text-sm">Este usuario no ha respondido a ninguna pregunta todavía.</p>
                </div>
            @else
                <div class="flex flex-col gap-4 max-h-[400px] overflow-y-auto pr-2">
                    @foreach($user->answers as $answer)
                        <div class="bg-white/5 p-5 rounded-2xl border border-white/10 hover:border-emerald-500/30 transition-all duration-300 text-left">
                            <div class="flex justify-between items-start gap-4 mb-2">
                                <div class="flex-1 min-w-0">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-400">Respuesta a:</span>
                                    <h5 class="text-sm font-bold text-white/90 truncate leading-tight mt-0.5">{{ $answer->question->title ?? 'Pregunta no disponible' }}</h5>
                                </div>
                                @if($answer->is_useful)
                                    <span class="px-2.5 py-1 text-xs font-bold bg-emerald-500/10 text-emerald-400 rounded-full border border-emerald-500/20 shrink-0 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        Útil
                                    </span>
                                @endif
                            </div>
                            <p class="text-white/70 text-sm mb-3 italic leading-relaxed">"{{ $answer->content }}"</p>
                            <div class="flex items-center justify-between text-[11px] text-white/40 font-bold uppercase tracking-wider">
                                <span>{{ $answer->created_at->diffForHumans() }}</span>
                                @if($answer->reputation > 0)
                                    <span class="text-yellow-500/80">+{{ $answer->reputation }} pts reputación</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-8 tab-content hidden" id="content-events">
            @if($user->events->isEmpty())
                <div class="bg-white/5 p-6 rounded-xl border border-white/10 text-center">
                    <svg class="w-8 h-8 text-white/20 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <p class="text-[#8b98a5] text-sm">Este usuario no está inscrito en ningún evento de momento.</p>
                </div>
            @else
                <div class="flex flex-col gap-4 max-h-[400px] overflow-y-auto pr-2">
                    @foreach($user->events as $event)
                        <div class="bg-white/5 p-5 rounded-2xl border border-white/10 hover:border-purple-500/30 transition-all duration-300 text-left">
                            <div class="flex justify-between items-start gap-4 mb-2">
                                <div>
                                    <h4 class="text-base font-bold text-white leading-snug">{{ $event->title }}</h4>
                                    <span class="text-[10px] text-white/40 uppercase tracking-wider">{{ $event->educationalCenter->name ?? 'Centro no especificado' }}</span>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-bold bg-purple-500/10 text-purple-400 rounded-full border border-purple-500/20 shrink-0">
                                    {{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}
                                </span>
                            </div>
                            <p class="text-white/60 text-sm mb-3 line-clamp-2">{{ $event->description }}</p>
                            <div class="flex items-center gap-4 text-[11px] text-white/40 font-bold uppercase tracking-wider">
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
                                    {{ $event->location }}
                                </span>
                                @if($event->start_time)
                                    <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} @if($event->end_time) - {{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }} @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
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
    
    /* Estilizar scrollbar en las listas internas */
    .profile-modal-content ::-webkit-scrollbar {
        width: 6px;
    }
    .profile-modal-content ::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.02);
        border-radius: 10px;
    }
    .profile-modal-content ::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }
    .profile-modal-content ::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.2);
    }
</style>
