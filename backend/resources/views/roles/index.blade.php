@extends('layouts.admin')

@section('content')
<x-admin.crud-list 
    :createUrl="route('role.create')"
    createTitle="Crear Nuevo Rol"
    createText="Nuevo Rol"
    emptyText="No hay roles registrados en el sistema."
    :models="$roles"
    :hasItems="count($roles) > 0"
>
    <x-slot:filters>
        <x-admin.filter-dropdown 
            label="Filtrar por Código" 
            name="search" {{-- Reusando search para el código si es necesario, o cualquier otro campo --}}
            :options="$roles->pluck('code', 'code')->unique()->toArray()" 
            :selected="request('search')" 
        />
    </x-slot:filters>
    @foreach($roles as $role)
        <x-admin.list.item 
            :title="$role->name"
            :badge="$role->code ?? '-'"
            :editUrl="route('role.edit', $role->id)"
            editTitle="Editar Rol"
            :deleteUrl="route('role.destroy', $role->id)"
            deleteTitle="Eliminar Rol"
        />
    @endforeach
</x-admin.crud-list>
@endsection
