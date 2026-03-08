@extends('layouts.admin')

@section('content')
<div class="p-8 min-h-screen text-white bg-gradient-to-b from-[#1a3a3a] via-[#10202e] to-[#0a141d]">

    {{-- Header --}}
    <div class="mb-10">
        <p class="text-white/70 text-sm font-medium tracking-widest uppercase mb-1">Panel de administración</p>
        <h1 class="text-4xl font-bold text-white">¡Bienvenido de nuevo! 👋</h1>
        <p class="text-white/70 mt-1 text-sm">Aquí tienes un resumen general de TelamoNet.</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 xl:grid-cols-3 gap-5 mb-10">

        <a href="#" data-url="/admin/users" data-load="section" data-title="Gestión de Usuarios" class="post-card group no-underline flex flex-col justify-between h-full col-span-2 xl:col-span-3 hover:shadow-cyan-900/20">
            <div>
                <div class="flex items-start justify-between mb-2">
                    <div class="bg-white/10 p-3 rounded-xl group-hover:bg-white/20 transition-colors duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="9" cy="7" r="4" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:stroke-white transition-colors duration-300 mt-1"><path d="M9 6l6 6l-6 6" /></svg>
                </div>
                <p class="text-3xl font-bold text-white mb-1">{{ $totalUsers ?? '—' }}</p>
                <p class="text-white/70 text-sm mb-4">Total Usuarios</p>
            </div>

            <div class="border-t border-white/10 pt-3 grid grid-cols-5 gap-2">
                {{-- Profesores --}}
                <div class="flex flex-col items-center gap-1 text-center">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center shrink-0">
                         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#a5b4fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M22 9l-10 -4l-10 4l10 4l10 -4v6" /><path d="M6 10.6v5.4a6 6 0 0 0 12 0v-5.4" /></svg>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">{{ $countTeachers ?? 0 }}</p>
                        <p class="text-white/50 text-[10px] uppercase tracking-wider">Profesores</p>
                    </div>
                </div>

                {{-- Alumnos --}}
                <div class="flex flex-col items-center gap-1 text-center border-l border-white/5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6ee7b7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1" /><path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M17 10h2a2 2 0 0 1 2 2v1" /><path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M3 13v-1a2 2 0 0 1 2 -2h2" /></svg>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">{{ $countStudents ?? 0 }}</p>
                        <p class="text-white/50 text-[10px] uppercase tracking-wider">Alumnos</p>
                    </div>
                </div>

                {{-- Administradores --}}
                <div class="flex flex-col items-center gap-1 text-center border-l border-white/5">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l3 6l6 1l-4.5 4.5l1 6.5l-5.5 -3l-5.5 3l1 -6.5l-4.5 -4.5l6 -1z" /><circle cx="12" cy="10" r="2" /></svg>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">{{ $countAdmins ?? 0 }}</p>
                        <p class="text-white/50 text-[10px] uppercase tracking-wider">Administradores</p>
                    </div>
                </div>

                {{-- Centros Educativos --}}
                <div class="flex flex-col items-center gap-1 text-center border-l border-white/5">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M9 8l1 0" /><path d="M9 12l1 0" /><path d="M9 16l1 0" /><path d="M14 8l1 0" /><path d="M14 12l1 0" /><path d="M14 16l1 0" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" /></svg>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">{{ $countEI ?? 0 }}</p>
                        <p class="text-white/50 text-[10px] uppercase tracking-wider">Centros</p>
                    </div>
                </div>

                {{-- Otros --}}
                <div class="flex flex-col items-center gap-1 text-center border-l border-white/5">
                    <div class="w-8 h-8 rounded-lg bg-fuchsia-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#e879f9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="7" r="4" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">{{ $countOthers ?? 0 }}</p>
                        <p class="text-white/50 text-[10px] uppercase tracking-wider">Otros</p>
                    </div>
                </div>
            </div>
        </a>

        <a href="#" data-url="/admin/schools" data-load="section" data-title="Centros Educativos" class="post-card group no-underline">
            <div class="flex items-start justify-between mb-4">
                <div class="bg-white/10 p-3 rounded-xl group-hover:bg-white/20 transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M22 9l-10 -4l-10 4l10 4l10 -4v6" /><path d="M6 10.6v5.4a6 6 0 0 0 12 0v-5.4" /><path d="M12 20v-10" /></svg>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:stroke-white transition-colors duration-300 mt-1"><path d="M9 6l6 6l-6 6" /></svg>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $totalSchools ?? '—' }}</p>
            <p class="text-white/70 text-sm">Centros educativos</p>
        </a>

        <a href="#" data-url="/admin/events" data-load="section" data-title="Eventos" class="post-card group no-underline">
            <div class="flex items-start justify-between mb-4">
                <div class="bg-white/10 p-3 rounded-xl group-hover:bg-white/20 transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="4" y="5" width="16" height="16" rx="2" /><line x1="16" y1="3" x2="16" y2="7" /><line x1="8" y1="3" x2="8" y2="7" /><line x1="4" y1="11" x2="20" y2="11" /><rect x="8" y="15" width="2" height="2" /></svg>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:stroke-white transition-colors duration-300 mt-1"><path d="M9 6l6 6l-6 6" /></svg>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $totalEvents ?? '—' }}</p>
            <p class="text-white/70 text-sm">Eventos publicados</p>
        </a>

        <a href="#" data-url="/admin/questions" data-load="section" data-title="Preguntas" class="post-card group no-underline">
            <div class="flex items-start justify-between mb-4">
                <div class="bg-white/10 p-3 rounded-xl group-hover:bg-white/20 transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M14 18l-2 3l-2 -3h-3a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h10a3 3 0 0 1 3 3v4.5" /><path d="M19 22v.01" /><path d="M19 19a2.003 2.003 0 0 0 .914 -3.782a1.98 1.98 0 0 0 -2.414 .482" /></svg>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:stroke-white transition-colors duration-300 mt-1"><path d="M9 6l6 6l-6 6" /></svg>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $totalQuestions ?? '—' }}</p>
            <p class="text-white/70 text-sm">Preguntas en el foro</p>
        </a>

    </div>

    {{-- Bottom section --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- Últimos usuarios --}}
        <div class="xl:col-span-1 post-card">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-white font-semibold text-base">Últimos usuarios</h2>
                <a href="#" data-url="/admin/users" data-load="section" data-title="Gestión de Usuarios" class="text-white text-xs hover:underline">Ver todos</a>
            </div>
            <div class="flex flex-col gap-3">
                @forelse($latestUsers ?? [] as $user)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-white text-sm font-medium truncate">{{ $user->name }}</p>
                            <p class="text-white/70 text-xs truncate">{{ $user->email }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-white/70 text-sm">Sin datos aún.</p>
                @endforelse
            </div>
        </div>

        {{-- Últimas preguntas --}}
        <div class="xl:col-span-1 post-card">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-white font-semibold text-base">Últimas preguntas</h2>
                <a href="#" data-url="/admin/questions" data-load="section" data-title="Foro de Preguntas" class="text-white text-xs hover:underline">Ver todas</a>
            </div>
            <div class="flex flex-col gap-3">
                @forelse($latestQuestions ?? [] as $question)
                    <div class="border-b border-white/10 pb-3 last:border-0 last:pb-0">
                        <p class="text-white text-sm font-medium line-clamp-2 leading-snug">{{ $question->title }}</p>
                        <p class="text-white/70 text-xs mt-1">{{ \Carbon\Carbon::parse($question->created_at)->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-white/70 text-sm">Sin datos aún.</p>
                @endforelse
            </div>
        </div>

        {{-- Alertas --}}
        <div class="xl:col-span-1 post-card">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-white font-semibold text-base">Alertas</h2>
                @if(($pendingAiReviews ?? 0) > 0 || ($usersWithoutSchool ?? 0) > 0)
                    <span class="bg-red-500/20 text-red-400 text-xs font-semibold px-2 py-0.5 rounded-full">
                        {{ ($pendingAiReviews ?? 0) + ($usersWithoutSchool ?? 0) }} pendientes
                    </span>
                @endif
            </div>
            <div class="flex flex-col gap-3">

                <div class="flex items-start gap-3 p-3 rounded-xl {{ ($pendingAiReviews ?? 0) > 0 ? 'bg-amber-500/10 border border-amber-500/20' : 'bg-white/5' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ ($pendingAiReviews ?? 0) > 0 ? '#f59e0b' : 'rgba(255,255,255,0.4)' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.871l-8.106 -13.534a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                    <div>
                        <p class="text-sm font-medium {{ ($pendingAiReviews ?? 0) > 0 ? 'text-amber-400' : 'text-white/70' }}">Preguntas rechazadas por IA</p>
                        <p class="text-xs text-white/70 mt-0.5">{{ $pendingAiReviews ?? 0 }} pendientes de revisión manual</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-xl {{ ($usersWithoutSchool ?? 0) > 0 ? 'bg-white/5 border border-white/40/20' : 'bg-white/5' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="7" r="4" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 19h6" /><path d="M19 16v6" /></svg>
                    <div>
                        <p class="text-sm font-medium {{ ($usersWithoutSchool ?? 0) > 0 ? 'text-white' : 'text-white/70' }}">Alumnos sin centro asignado</p>
                        <p class="text-xs text-white/70 mt-0.5">{{ $usersWithoutSchool ?? 0 }} alumnos sin asignar</p>
                    </div>
                </div>

                <div class="mt-2">
                    <p class="text-white/70 text-xs font-medium uppercase tracking-widest mb-3">Top reputación</p>
                    <div class="flex flex-col gap-2">
                        @forelse($topUsers ?? [] as $index => $user)
                            <div class="flex items-center gap-2">
                                <span class="text-white text-xs font-bold w-4">#{{ $index + 1 }}</span>
                                <div class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-white text-[10px] font-bold shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="text-white text-sm truncate flex-1">{{ $user->name }}</span>
                                <span class="text-white text-xs font-semibold">{{ $user->reputation }} pts</span>
                            </div>
                        @empty
                            <p class="text-white/70 text-sm">Sin datos aún.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- Fila para Roles y futuras tarjetas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5 mt-5">
        {{-- Roles del sistema --}}
        <div class="col-span-1 xl:col-span-1">
            <a href="#" data-url="/admin/roles" data-load="modal" data-title="Roles del Sistema" class="btn-modal block no-underline group h-full">
                <div class="roles-banner h-full flex flex-wrap items-center justify-between gap-4 px-6 py-4 rounded-2xl border border-white/10 hover:border-white/20 transition-all duration-300 overflow-hidden relative">

                    {{-- Fondo decorativo sutil --}}
                    <div class="absolute inset-0 bg-gradient-to-r from-white/[0.04] via-white/[0.02] to-transparent pointer-events-none"></div>
                    <div class="absolute right-0 top-0 h-full w-64 bg-gradient-to-l from-white/[0.03] to-transparent pointer-events-none"></div>

                    {{-- Icono + texto --}}
                    <div class="flex items-center gap-4 relative">
                        <div class="w-10 h-10 rounded-xl border border-white/10 flex items-center justify-center shrink-0 transition-colors duration-300" style="background: rgba(255,255,255,0.07);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.65)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/>
                                <path d="M16 19h6"/><path d="M19 16v6"/>
                                <path d="M6 21v-2a4 4 0 0 1 4 -4h4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm leading-tight">Roles del sistema</p>
                            <p class="text-white/40 text-xs mt-0.5">Gestión de permisos</p>
                        </div>
                    </div>

                    {{-- Número (simplificado para formato tarjeta estrecho) --}}
                    <div class="flex items-center gap-4 relative ml-auto">
                        <div class="text-right">
                            <p class="text-2xl font-bold text-white leading-none">{{ $totalRoles ?? 0 }}</p>
                        </div>

                        {{-- CTA --}}
                        <div class="flex items-center gap-1.5 pl-3 border-l border-white/10 relative">
                            <span class="text-white/45 text-xs group-hover:text-white/75 transition-colors duration-200 hidden sm:inline-block">Ver</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:stroke-white/60 group-hover:translate-x-0.5 transition-all duration-200"><path d="M9 6l6 6l-6 6"/></svg>
                        </div>
                    </div>

                </div>
            </a>
        </div>
        {{-- Palabras Vetadas --}}
        <div class="col-span-1 xl:col-span-1">
            <a href="#" data-url="/admin/banned-words" data-load="modal" data-title="Palabras Vetadas" class="btn-modal block no-underline group h-full">
                <div class="roles-banner h-full flex flex-wrap items-center justify-between gap-4 px-6 py-4 rounded-2xl border border-white/10 hover:border-white/20 transition-all duration-300 overflow-hidden relative">

                    {{-- Fondo decorativo sutil --}}
                    <div class="absolute inset-0 bg-gradient-to-r from-red-500/[0.04] via-red-500/[0.02] to-transparent pointer-events-none"></div>
                    <div class="absolute right-0 top-0 h-full w-64 bg-gradient-to-l from-red-500/[0.03] to-transparent pointer-events-none"></div>

                    {{-- Icono + texto --}}
                    <div class="flex items-center gap-4 relative">
                        <div class="w-10 h-10 rounded-xl border border-white/10 flex items-center justify-center shrink-0 transition-colors duration-300" style="background: rgba(255,255,255,0.07);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.65)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 5v14" />
                                <path d="M5 12h14" />
                                <path d="M19 5l-14 14" />
                                <path d="M5 5l14 14" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm leading-tight">Palabras vetadas</p>
                            <p class="text-white/40 text-xs mt-0.5">Gestión de censura</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 relative ml-auto">
                        <div class="text-right">
                            <p class="text-2xl font-bold text-white leading-none">{{ count($bannedWords ?? []) }}</p>
                        </div>

                        {{-- CTA --}}
                        <div class="flex items-center gap-1.5 pl-3 border-l border-white/10 relative">
                            <span class="text-white/45 text-xs group-hover:text-white/75 transition-colors duration-200 hidden sm:inline-block">Ver</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:stroke-white/60 group-hover:translate-x-0.5 transition-all duration-200"><path d="M9 6l6 6l-6 6"/></svg>
                        </div>
                    </div>

                </div>
            </a>
        </div>
    </div>

</div>

<style>
    .post-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 20px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .post-card:hover {
        background: linear-gradient(135deg, rgba(54, 54, 54, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
        border-color: rgba(255, 255, 255, 0.4);
        box-shadow: 0 0 30px rgba(255,255,255,0.05);
    }

    .roles-banner {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
    }
</style>
@endsection



