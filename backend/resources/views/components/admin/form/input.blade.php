@props([
    'name',
    'label',
    'id' => null,
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'disabled' => '',
])

@php
    $inputId = $id ?? 'id' . $name;
@endphp

<div>
    <label for="{{ $inputId }}" class="block text-sm font-medium text-white/70 mb-1">{{ $label }}</label>
    <input {{ $disabled }} @if($type !== 'file') value="{{ $value }}" @endif type="{{ $type }}" name="{{ $name }}" 
        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 [color-scheme:dark]
        @if($type === 'file')
            file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:tracking-widest file:uppercase
            file:bg-[var(--admin-accent-1-bg)] file:text-[var(--admin-accent-1)] hover:file:bg-[var(--admin-accent-1-border)] cursor-pointer text-white/50
        @endif" 
        id="{{ $inputId }}" placeholder="{{ $placeholder }}">
    @error($name)
        <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
    @enderror
</div>
