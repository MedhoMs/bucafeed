@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold text-white">{{ $question->title }}</h1>
            <x-admin.user-avatar :user="$question->user" size="w-8 h-8" class="mt-1" />
            <p class="text-white/40 text-xs mt-1">
                Publicado el {{ $question->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        @if(!request()->ajax())
            <a href="{{ route('question.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition shrink-0">
                Volver al listado
            </a>
        @endif
    </div>

    <div class="p-6 bg-[#1a1c23] border border-white/10 rounded-xl shadow-xl">
        @if($question->image)
            <div class="mb-6">
                <img src="{{ $question->image }}" alt="Imagen de la pregunta" class="max-w-full h-auto rounded-xl border border-white/10 shadow-lg">
            </div>
        @endif
        <div class="prose prose-invert max-w-none text-white/90">
            {!! nl2br(e($question->content)) !!}
        </div>
        <div class="mt-6 flex flex-wrap gap-2">
            @foreach($question->tags as $tag)
                <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 rounded-full text-xs">
                    #{{ $tag->name }}
                </span>
            @endforeach
        </div>
    </div>

    <!-- Answers Section -->
    <div class="space-y-4">
        <h2 class="text-xl font-semibold text-white">Respuestas ({{ $question->answers->count() }})</h2>
        
        @forelse($question->answers as $answer)
            <div class="p-5 {{ $answer->is_useful ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-white/5 border-white/10' }} border rounded-xl relative transition-all duration-300">
                <div class="flex items-start justify-between mb-4">
                    <x-admin.user-avatar :user="$answer->user" :showReputation="true" />
                    
                    <div class="flex flex-col items-end gap-2">
                        <span class="text-white/40 text-[10px]">{{ $answer->created_at->diffForHumans() }}</span>
                        @if($answer->is_useful)
                            <span class="px-2 py-1 bg-emerald-500 text-white text-[9px] font-black rounded-md shadow-lg uppercase tracking-wider animate-pulse">
                                Mejor Respuesta
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="text-white/80 text-sm leading-relaxed pl-1">
                    {!! nl2br(e($answer->content)) !!}
                </div>
                
                <div class="mt-3 flex items-center gap-4 text-xs text-white/40">
                    <span>Puntos: {{ $answer->reputation }}</span>
                </div>
            </div>
        @empty
            <p class="text-white/40 italic">Aún no hay respuestas para esta pregunta.</p>
        @endforelse
    </div>
</div>
@endsection
