@extends('layouts.admin')

@section('content')
<x-admin.crud-index
    title="Gestión de Usuarios"
    description="Administra los usuarios registrados en la plataforma."
    createUrl="{{ route('user.create') }}"
    createTitle="Crear Nuevo Usuario"
    createText="Nuevo Usuario"
    :models="$users"
    :headers="[
        'ID' => 'hidden sm:table-cell',
        'Usuario' => '',
        'DNI/NIE' => 'hidden lg:table-cell',
        'Rol' => 'hidden md:table-cell',
        'Reputación' => 'hidden sm:table-cell',
        'Nivel / Institución' => 'hidden xl:table-cell',
        'Cursando / Impartiendo' => 'hidden lg:table-cell',
        'Registro' => 'hidden lg:table-cell'
    ]"
>
    <x-slot:filters>
        <x-admin.filter-dropdown 
            label="Rol" 
            name="role" 
            :options="$roles_disponibles" 
            :selected="request('role')" 
        />
        <x-admin.filter-dropdown 
            label="Nivel" 
            name="level" 
            :options="$niveles_disponibles" 
            :selected="request('level')" 
        />
        <x-admin.filter-dropdown 
            label="Centro" 
            name="institution" 
            :options="$instituciones_existentes" 
            :selected="request('institution')" 
        />
        <x-admin.filter-dropdown 
            label="Área/Ciclo" 
            name="cycle" 
            :options="$ciclos_disponibles" 
            :selected="request('cycle')" 
            align="right"
        />
    </x-slot:filters>

    <x-slot:tbody>
        @foreach($users as $user)
        @php
            $columns = [
                ['type' => 'actions', 'class' => 'text-left', 'showUrl' => route('user.show', $user->id), 'showTitle' => 'Consultar Usuario', 'editUrl' => route('user.edit', $user->id), 'editTitle' => 'Editar Usuario', 'deleteUrl' => route('user.destroy', $user->id), 'deleteTitle' => 'Eliminar Usuario'],
                ['type' => 'text', 'value' => '#'.$user->id, 'class' => 'text-white/70 hidden sm:table-cell'],
                ['type' => 'avatar', 'image' => $user->profile_picture, 'title' => $user->name . ' ' . $user->last_name, 'subtitle' => $user->email, 'shape' => 'rounded-full', 'imageSize' => 'w-9 h-9', 'modalUrl' => route('user.profile_modal', $user->id), 'modalTitle' => 'Perfil de ' . $user->name, 'fallback' => '<img src="' . asset('logoTelamon.png') . '" alt="Avatar" class="w-full h-full object-cover opacity-60">'],
                ['type' => 'text', 'value' => $user->dni ?? '-', 'class' => 'text-white/70 hidden lg:table-cell'],
                ['type' => 'badge', 'text' => $roles_disponibles[$user->role] ?? $user->role, 'color' => 'purple', 'class' => 'hidden md:table-cell'],
                ['type' => 'html', 'content' => '<div class="flex items-center gap-1 text-yellow-500"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-coins"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 14c0 1.657 2.686 3 6 3s6 -1.343 6 -3s-2.686 -3 -6 -3s-6 1.343 -6 3z" /><path d="M9 14v4c0 1.656 2.686 3 6 3s6 -1.344 6 -3v-4" /><path d="M3 6c0 1.072 1.144 2.062 3 2.598s4.144 .536 6 0c1.856 -.536 3 -1.526 3 -2.598c0 -1.072 -1.144 -2.062 -3 -2.598s-4.144 -.536 -6 0c-1.856 .536 -3 1.526 -3 2.598z" /><path d="M3 6v10c0 .888 .772 1.45 2 2" /><path d="M3 11c0 .888 .772 1.45 2 2" /></svg> <span class="font-bold text-xs">'.($user->reputation ?? '0').'</span></div>', 'class' => 'hidden sm:table-cell'],
                ['type' => 'html', 'content' => '<div class="flex flex-col"><span class="text-xs text-wrap">'.($niveles_disponibles[$user->education_level] ?? $user->education_level ?? '-').'</span><span class="text-[10px] text-white/40">'.($user->institution_name ?? '-').'</span></div>', 'class' => 'hidden xl:table-cell'],
                ['type' => 'html', 'content' => (function($user) {
                    if ($user->role === 'Student') {
                        $cycleName = $user->student && $user->student->cycle ? $user->student->cycle->name : ($user->groupsAsStudent->first() ? ($user->groupsAsStudent->first()->cycle->name ?? 'N/A') : 'N/A');
                        $course = $user->student ? $user->student->course . 'º' : '';
                        return '<div class="flex flex-col"><span class="text-xs text-indigo-400 font-bold uppercase tracking-tighter shrink-0">' . ($cycleName !== 'N/A' ? 'Estudiando ' . $cycleName : '-') . '</span><span class="text-[10px] text-white/40">' . ($course ? $course . ' curso' : '') . '</span></div>';
                    } elseif ($user->role === 'Teacher') {
                        $subjects = collect();
                        foreach($user->groupsAsTeacher as $group) {
                            $subjects = $subjects->merge(
                                $group->subjectsWithTeachers->filter(function($tag) use ($user) {
                                    return $tag->pivot && $tag->pivot->user_id == $user->id;
                                })->pluck('name')
                            );
                        }
                        $subjects = $subjects->unique();
                        $count = $subjects->count();
                        $list = $subjects->take(2)->implode(', ');
                        if ($count > 2) $list .= '...';
                        return '<div class="flex flex-col"><span class="text-xs text-emerald-400 font-bold uppercase tracking-tighter shrink-0">' . ($count > 0 ? 'Impartiendo' : '-') . '</span><span class="text-[10px] text-white/40">' . ($list ?: ($user->groupsAsTeacher->count() > 0 ? 'Varios grupos' : '-')) . '</span></div>';
                    } else {
                        return '<span class="text-white/20 text-xs">-</span>';
                    }
                })($user), 'class' => 'hidden lg:table-cell'],
                ['type' => 'text', 'value' => $user->created_at->format('Y-m-d'), 'class' => 'text-white/70 text-xs hidden lg:table-cell']
            ];
        @endphp
        <x-admin.table.row-builder :columns="$columns" />
        @endforeach
    </x-slot:tbody>
</x-admin.crud-index>
@endsection
