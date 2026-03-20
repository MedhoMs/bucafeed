@props([
    'name',
    'label',
    'id' => null,
    'value' => '',
    'placeholder' => '',
    'rows' => 4,
    'disabled' => '',
])

@php
    $inputId = $id ?? 'id' . $name;
@endphp

<div>
    <label for="{{ $inputId }}" class="block text-sm font-medium text-white/70 mb-1">{{ $label }}</label>
    <textarea {{ $disabled }} name="{{ $name }}" id="{{ $inputId }}" rows="{{ $rows }}"
        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
        placeholder="{{ $placeholder }}">{{ $value }}</textarea>
    @error($name)
        <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
    @enderror
</div>
