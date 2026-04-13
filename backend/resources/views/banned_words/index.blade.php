@extends('layouts.admin')

@section('content')
<x-admin.crud-list 
    createUrl="/admin/banned-words/create"
    createTitle="Añadir Palabra Vetada"
    createText="Añadir Palabra"
    emptyText="No hay palabras vetadas registradas en el sistema."
    :models="$bannedWords"
    :hasItems="count($bannedWords) > 0"
>
    @foreach($bannedWords as $word)
        <x-admin.list.item 
            :title="$word->word"
            titleClass="text-white font-medium group-hover:text-red-400 transition-colors"
            editUrl="/admin/banned-words/edit/{{ $word->id }}"
            editTitle="Editar Palabra Vetada"
            deleteUrl="/admin/banned-words/{{ $word->id }}"
            deleteTitle="Eliminar Palabra Vetada"
        />
    @endforeach
</x-admin.crud-list>
@endsection
