@props([
    'name',
    'label',
    'id' => null,
    'disabled' => '',
    'options' => [],
    'selectedValue' => '',
    'placeholder' => 'Seleccionar...',
    'required' => false,
    'multiple' => false,
])

@php
    $inputId = $id ?? 'id' . str_replace(['[', ']'], '', $name);
    $realName = $multiple ? $name . '[]' : $name;
    $selectedValues = is_array($selectedValue) ? $selectedValue : ($selectedValue ? [$selectedValue] : []);

    // Si viene de redicción por error de validación (old)
    $oldValues = old($name);
    if ($oldValues !== null) {
        $selectedValues = is_array($oldValues) ? $oldValues : [$oldValues];
    }
@endphp

<div>
    <label for="{{ $inputId }}" class="block text-sm font-medium text-white/70 mb-1">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>
    <select {{ $disabled }} {{ $required ? 'required' : '' }} {{ $multiple ? 'multiple' : '' }}
        name="{{ $realName }}" id="{{ $inputId }}"
        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 scheme-dark {{ $multiple ? 'min-h-30' : '' }}">

        @if($placeholder && !$multiple)
            <option value="" class="bg-[#1a202c]">{{ $placeholder }}</option>
        @endif

        @foreach($options as $val => $text)
            @php
                $isSelected = in_array((string)$val, array_map('strval', $selectedValues));
            @endphp
            <option value="{{ $val }}" {{ $isSelected ? 'selected' : '' }} class="bg-[#1a202c] p-2">
                {{ $text }}
            </option>
        @endforeach
        {{ $slot }}
    </select>
    @error($name)
        <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
    @enderror
</div>
