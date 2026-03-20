@props([
    'text' => '',
    'color' => 'purple',
])

@php
    $styles = [
        'purple' => 'background-color: var(--admin-badge-purple-bg); color: var(--admin-badge-purple-text); border-color: var(--admin-badge-purple-border);',
        'emerald' => 'background-color: var(--admin-badge-emerald-bg); color: var(--admin-badge-emerald-text); border-color: var(--admin-badge-emerald-border);',
        'blue' => 'background-color: var(--admin-badge-blue-bg); color: var(--admin-badge-blue-text); border-color: var(--admin-badge-blue-border);',
        'red' => 'background-color: var(--admin-badge-red-bg); color: var(--admin-badge-red-text); border-color: var(--admin-badge-red-border);',
        'yellow' => 'background-color: var(--admin-badge-yellow-bg); color: var(--admin-badge-yellow-text); border-color: var(--admin-badge-yellow-border);',
        'white' => 'background-color: var(--admin-badge-white-bg); color: var(--admin-badge-white-text); border-color: var(--admin-badge-white-border);',
    ];
    $style = $styles[$color] ?? $styles['purple'];
@endphp

<x-admin.table.td {{ $attributes }}>
    <span 
        class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] sm:text-xs font-bold tracking-widest uppercase border transition-all hover:scale-105"
        style="{{ $style }}"
    >
        {{ $text }}
    </span>
</x-admin.table.td>
