@props([
    'name',
    'label',
    'id' => null,
    'disabled' => '',
    'options' => [],
    'selectedValue' => '',
    'placeholder' => 'Seleccionar...',
    'required' => false,
])

@php
    $inputId = $id ?? 'id' . $name;
@endphp

<div>
    <label for="{{ $inputId }}" class="block text-sm font-medium text-white/70 mb-1">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>
    <select {{ $disabled }} {{ $required ? 'required' : '' }} name="{{ $name }}" id="{{ $inputId }}" 
        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200">
        @if($placeholder)
            <option value="" class="bg-[#1a202c]">{{ $placeholder }}</option>
        @endif
        @foreach($options as $val => $text)
            <option value="{{ $val }}" {{ (strtolower((string)old($name, $selectedValue)) == strtolower((string)$val)) ? 'selected' : '' }} class="bg-[#1a202c]">
                {{ $text }}
            </option>
        @endforeach
        {{ $slot }}
    </select>
    @error($name)
        <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
    @enderror
</div>
