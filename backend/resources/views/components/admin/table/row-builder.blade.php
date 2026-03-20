@props(['columns' => []])

<x-admin.table.tr {{ $attributes }}>
    @foreach($columns as $col)
        @php
            $type = $col['type'] ?? 'text';
            $class = $col['class'] ?? '';
            $titleValue = $col['titleAttr'] ?? (isset($col['title']) && $type === 'text' ? $col['title'] : null);
        @endphp

        @if($type === 'actions')
            <x-admin.table.td class="{{ $class }}">
                <x-admin.crud-actions 
                    :showUrl="$col['showUrl'] ?? null"
                    :showTitle="$col['showTitle'] ?? 'Consultar'"
                    :editUrl="$col['editUrl'] ?? null"
                    :editTitle="$col['editTitle'] ?? 'Editar'"
                    :deleteUrl="$col['deleteUrl'] ?? null"
                    :deleteTitle="$col['deleteTitle'] ?? 'Eliminar'"
                >
                    @isset($col['customHtml'])
                        {!! $col['customHtml'] !!}
                    @endisset
                </x-admin.crud-actions>
            </x-admin.table.td>
        @elseif($type === 'avatar')
            <x-admin.table.cell-avatar 
                :image="$col['image'] ?? null"
                :title="$col['title'] ?? ''"
                :subtitle="$col['subtitle'] ?? ''"
                :fallback="$col['fallback'] ?? '?'"
                :shape="$col['shape'] ?? 'rounded-xl'"
                :imageSize="$col['imageSize'] ?? 'w-10 h-10'"
                :modalUrl="$col['modalUrl'] ?? null"
                :modalTitle="$col['modalTitle'] ?? null"
                class="{{ $class }}"
            />
        @elseif($type === 'date')
            <x-admin.table.cell-date 
                :date="$col['date'] ?? null"
                :startTime="$col['startTime'] ?? null"
                :endTime="$col['endTime'] ?? null"
                class="{{ $class }}"
            />
        @elseif($type === 'badge')
            <x-admin.table.cell-badge 
                :text="$col['text'] ?? ''"
                :color="$col['color'] ?? 'purple'"
                class="{{ $class }}"
            />
        @elseif($type === 'html')
            <x-admin.table.td class="{{ $class }}" :title="$titleValue ?: null">
                {!! $col['content'] ?? '' !!}
            </x-admin.table.td>
        @else
            <x-admin.table.td class="{{ $class }}" :title="$titleValue ?: null">
                {{ $col['value'] ?? '-' }}
            </x-admin.table.td>
        @endif
    @endforeach
</x-admin.table.tr>
