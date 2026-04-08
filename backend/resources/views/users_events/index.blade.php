@extends('layouts.admin')

@section('content')
<x-admin.crud-index
    title="Gestión de Eventos"
    description="Administra los eventos de los centros educativos."
    createUrl="{{ route('users_events.create') }}"
    createTitle="Crear Nuevo Evento"
    createText="Nuevo Evento"
    :models="$events"
    :headers="[
        'ID' => 'hidden sm:table-cell',
        'Evento' => '',
        'Centro Educativo' => 'hidden md:table-cell',
        'Fecha / Horario' => 'hidden lg:table-cell',
        'Lugar' => 'hidden xl:table-cell',
        'Rol Target' => 'hidden lg:table-cell text-center'
    ]"
>
    <x-slot:filters>
        <x-admin.filter-dropdown 
            label="Centro" 
            name="center" 
            :options="$schools" 
            :selected="request('center')" 
        />
        <x-admin.filter-dropdown 
            label="Dirigido a" 
            name="role" 
            :options="array_merge(['' => 'Todos'], $roles_disponibles)" 
            :selected="request('role')" 
        />
    </x-slot:filters>

    <x-slot:tbody>
        @foreach($events as $event)
        @php
            $columns = [
                ['type' => 'actions', 'class' => 'text-left', 'showUrl' => route('users_events.show', $event->id), 'showTitle' => 'Consultar Evento', 'editUrl' => route('users_events.edit', $event->id), 'editTitle' => 'Editar Evento', 'deleteUrl' => route('users_events.destroy', $event->id), 'deleteTitle' => 'Eliminar Evento'],
                ['type' => 'text', 'value' => '#'.$event->id, 'class' => 'text-white/70 hidden sm:table-cell'],
                ['type' => 'avatar', 'image' => $event->image_url, 'title' => $event->title, 'subtitle' => $event->description ?: 'Sin descripción', 'fallback' => substr($event->title, 0, 1)],
                ['type' => 'text', 'value' => $event->educationalCenter->name ?? '-', 'class' => 'text-white/80 max-w-[150px] truncate hidden md:table-cell', 'title' => $event->educationalCenter->name ?? '-'],
                ['type' => 'date', 'date' => $event->date, 'startTime' => $event->start_time, 'endTime' => $event->end_time, 'class' => 'hidden lg:table-cell'],
                ['type' => 'text', 'value' => $event->location ?? '-', 'class' => 'text-white/70 max-w-[150px] truncate hidden xl:table-cell', 'title' => $event->location ?? '-'],
                ['type' => 'badge', 'text' => $event->target_role ? ($roles_disponibles[$event->target_role] ?? $event->target_role) : 'Todos', 'color' => $event->target_role ? 'emerald' : 'white', 'class' => 'text-center hidden lg:table-cell']
            ];
        @endphp
        <x-admin.table.row-builder :columns="$columns" />
        @endforeach
    </x-slot:tbody>
</x-admin.crud-index>
@endsection
