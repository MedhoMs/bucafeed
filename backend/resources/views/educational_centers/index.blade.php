@extends('layouts.admin')

@section('content')
<x-admin.crud-index
    title="Gestión de Centros Educativos"
    description="Administra los centros educativos registrados en la plataforma."
    createUrl="{{ route('educational_centers.create') }}"
    createTitle="Crear Nuevo Centro"
    createText="Nuevo Centro"
    :models="$centers"
    :headers="[
        'ID' => 'hidden sm:table-cell',
        'Centro' => '',
        'Administrador' => 'hidden lg:table-cell',
        'Ciclos' => 'hidden md:table-cell',
        'Usuarios' => 'hidden sm:table-cell text-center'
    ]"
>
    <x-slot:filters>
        @if(!empty($locations))
            <x-admin.filter-dropdown 
                label="Ubicación" 
                name="location" 
                :options="$locations" 
                :selected="request('location')" 
            />
        @endif
        @if(!empty($types))
            <x-admin.filter-dropdown 
                label="Tipo" 
                name="type" 
                :options="$types" 
                :selected="request('type')" 
                align="right"
            />
        @endif
    </x-slot:filters>

    <x-slot:tbody>
        @foreach($centers as $center)
        @php
            $ciclosCount = $center->cycles->count();
            $ciclos = $ciclosCount > 0 
                ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-widest uppercase border" style="background-color: var(--admin-accent-2-bg); color: var(--admin-accent-2); border-color: var(--admin-accent-2-border);">' . $ciclosCount . ' ciclos</span>' 
                : '<span class="text-white/40 text-sm">Ninguno</span>';
            
            $users_buttons = '
                <div class="flex items-center justify-center gap-3">
                    <div class="flex flex-col items-center gap-1 group/stat">
                        <a href="#" 
                           data-url="'. route('educational_centers.list_users_modal', [$center->id, 'Student']) .'" 
                           data-load="modal" 
                           data-title="Alumnos de '. $center->name .'" 
                           class="btn-modal w-8 h-8 rounded-full border flex items-center justify-center transition-all hover:scale-110"
                           style="background-color: var(--admin-accent-1-bg); border-color: var(--admin-accent-1-border); color: var(--admin-accent-1);"
                           title="Ver Alumnos">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1" /></svg>
                        </a>
                        <span class="text-[9px] font-bold uppercase opacity-60" style="color: var(--admin-accent-1);">'. $center->students->count() .' Est.</span>
                    </div>
                    <div class="flex flex-col items-center gap-1 group/stat">
                        <a href="#" 
                           data-url="'. route('educational_centers.list_users_modal', [$center->id, 'Teacher']) .'" 
                           data-load="modal" 
                           data-title="Docentes de '. $center->name .'" 
                           class="btn-modal w-8 h-8 rounded-full border flex items-center justify-center transition-all hover:scale-110"
                           style="background-color: var(--admin-accent-2-bg); border-color: var(--admin-accent-2-border); color: var(--admin-accent-2);"
                           title="Ver Docentes">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9l-10 -4l-10 4l10 4l10 -4v6" /><path d="M6 10.6v5.4a6 6 0 0 0 12 0v-5.4" /></svg>
                        </a>
                        <span class="text-[9px] font-bold uppercase opacity-60" style="color: var(--admin-accent-2);">'. $center->teachers->count() .' Prof.</span>
                    </div>
                </div>';
            
            $admin = $center->adminUser ? $center->adminUser->name . ' ' . $center->adminUser->last_name : '<span class="text-white/40 italic">No Asignado</span>';

            $addBtn = '
                <a class="btn-modal p-2 text-white/60 hover:text-green-400 hover:bg-green-400/10 rounded-lg transition-colors cursor-pointer border border-transparent hover:border-green-500/30" 
                   title="Matricular Usuarios"
                   data-url="'. route("educational_centers.add_users", $center->id) .'"
                   data-title="Matricular Usuarios"
                   data-load="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M16 19h6" /><path d="M19 16v6" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4" /></svg>
                </a>
                <a class="btn-modal p-2 text-white/60 hover:text-yellow-400 hover:bg-yellow-400/10 rounded-lg transition-colors cursor-pointer border border-transparent hover:border-yellow-500/30" 
                   title="Gestionar Ciclos"
                   data-url="'. route("educational_centers.manage_cycles", $center->id) .'"
                   data-title="Gestión de Ciclos"
                   data-load="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19 4v16" /><path d="M9 4v16" /><path d="M14 20l2 -5h4l2 5" /><path d="M11 16h-5a2 2 0 0 0 -2 2v2" /><path d="M7 13a4 4 0 1 0 0 -8a4 4 0 0 0 0 8z" /></svg>
                </a>';

            $columns = [
                ['type' => 'actions', 'class' => 'text-left min-w-[150px]', 'showUrl' => route('educational_centers.profile_modal', $center->id), 'showTitle' => 'Perfil del Centro', 'editUrl' => route('educational_centers.edit', $center->id), 'deleteUrl' => route('educational_centers.destroy', $center->id), 'customHtml' => $addBtn],
                ['type' => 'text', 'value' => '#'.$center->id, 'class' => 'text-white/70 hidden sm:table-cell'],
                ['type' => 'avatar', 'image' => $center->icon, 'title' => $center->name, 'subtitle' => $center->location ?: 'Sin ubicación', 'fallback' => substr($center->name, 0, 1), 'imageSize' => 'w-10 h-10', 'shape' => 'rounded-xl'],
                ['type' => 'html', 'content' => $admin, 'class' => 'hidden lg:table-cell'],
                ['type' => 'html', 'content' => $ciclos, 'class' => 'hidden md:table-cell'],
                ['type' => 'html', 'content' => $users_buttons, 'class' => 'hidden sm:table-cell text-center']
            ];
        @endphp
        <x-admin.table.row-builder :columns="$columns" />
        @endforeach

        @if($centers->isEmpty())
            <x-admin.table.tr>
                <td colspan="6" class="px-6 py-12 text-center text-white/50">
                    No hay centros educativos registrados en la plataforma.
                </td>
            </x-admin.table.tr>
        @endif
    </x-slot:tbody>
</x-admin.crud-index>
@endsection
