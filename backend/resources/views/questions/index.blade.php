@extends('layouts.admin')

@section('content')
<x-admin.crud-index
    title="Gestión de Preguntas"
    description="Administra las consultas académicas de los estudiantes."
    createUrl="{{ route('question.create') }}"
    createTitle="Nueva Pregunta"
    createText="Nueva Pregunta"
    :models="$questions"
    :headers="[
        'ID' => '',
        'Pregunta' => '',
        'Autor' => '',
        'Respuestas' => 'text-center',
        'Etiquetas' => '',
        'Fecha' => ''
    ]"
>
    <x-slot:filters>
        <x-admin.filter-dropdown
            label="Etiqueta"
            name="tag"
            :options="array_merge(['' => 'Todas'], $tags_disponibles)"
            :selected="request('tag')"
        />
    </x-slot:filters>

    <x-slot:tbody>
        @foreach($questions as $question)
        @php
            $columns = [
                ['type' => 'actions', 'class' => 'text-left', 'showUrl' => route('question.show', $question->id), 'showTitle' => 'Ver Detalles', 'deleteUrl' => route('question.destroy', $question->id), 'deleteTitle' => 'Eliminar Pregunta'],
                ['type' => 'text', 'value' => '#'.$question->id, 'class' => 'text-white/70'],
                ['type' => 'text', 'value' => $question->title, 'class' => 'text-white font-medium max-w-50 truncate', 'title' => $question->title],
                ['type' => 'html', 'content' => view('components.admin.user-avatar', ['user' => $question->user, 'size' => 'w-7 h-7', 'showName' => true, 'showReputation' => false])->render(), 'class' => 'text-white/80'],
                ['type' => 'badge', 'text' => $question->answers_count . ' respuestas', 'color' => $question->answers_count > 0 ? 'indigo' : 'gray', 'class' => 'text-center'],
                ['type' => 'text', 'value' => $question->tags->pluck('name')->implode(', ') ?: 'Sin etiquetas', 'class' => 'text-white/60 text-xs italic max-w-37.5 truncate'],
                ['type' => 'text', 'value' => $question->created_at->format('d/m/Y'), 'class' => 'text-white/70']
            ];
        @endphp
        <x-admin.table.row-builder :columns="$columns" />
        @endforeach
    </x-slot:tbody>
</x-admin.crud-index>
@endsection
