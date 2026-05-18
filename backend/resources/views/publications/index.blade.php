@extends('layouts.admin')

@section('content')
<x-admin.crud-index
    title="Gestión de Publicaciones"
    description="Administra los logros, artículos y noticias de los Centros Educativos."
    createUrl="{{ route('publication.create') }}"
    createTitle="Nueva Publicación"
    createText="Nueva Publicación"
    :models="$publications"
    :headers="[
        'ID' => '',
        'Título' => '',
        'Centro Educativo' => '',
        'Imagen' => 'text-center',
        'Fecha' => ''
    ]"
>
    <x-slot:tbody>
        @foreach($publications as $publication)
        @php
            $columns = [
                [
                    'type' => 'actions', 
                    'class' => 'text-left', 
                    'showUrl' => route('publication.show', $publication->id), 
                    'showTitle' => 'Ver Detalles', 
                    'deleteUrl' => route('publication.destroy', $publication->id), 
                    'deleteTitle' => 'Eliminar Publicación'
                ],
                ['type' => 'text', 'value' => '#'.$publication->id, 'class' => 'text-white/70'],
                ['type' => 'text', 'value' => $publication->title, 'class' => 'text-white font-medium max-w-50 truncate', 'title' => $publication->title],
                ['type' => 'text', 'value' => $publication->educationalCenter ? $publication->educationalCenter->name : 'N/A', 'class' => 'text-white/80 max-w-50 truncate'],
                [
                    'type' => 'badge', 
                    'text' => $publication->image ? 'Con Imagen' : 'Sin Imagen', 
                    'color' => $publication->image ? 'emerald' : 'gray', 
                    'class' => 'text-center'
                ],
                ['type' => 'text', 'value' => $publication->created_at->format('d/m/Y'), 'class' => 'text-white/70']
            ];
        @endphp
        <x-admin.table.row-builder :columns="$columns" />
        @endforeach
    </x-slot:tbody>
</x-admin.crud-index>
@endsection
