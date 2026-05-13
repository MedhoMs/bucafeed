@props([
    'name',
    'label',
    'options' => [],
    'selectedValue' => [],
    'disabled' => '',
    'required' => false,
    'data' => []
])

@php
    $selectedValues = is_array($selectedValue) ? $selectedValue : ($selectedValue ? [$selectedValue] : []);

    // Si viene de redicción por error de validación (old)
    $oldValues = old($name);
    if ($oldValues !== null) {
        $selectedValues = is_array($oldValues) ? $oldValues : [$oldValues];
    }
@endphp

<div class="space-y-3">
    <label class="block text-sm font-medium text-white/70">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>

    <div id="checkbox-container-{{ $name }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 p-4 bg-white/5 border border-white/10 rounded-2xl max-h-50 overflow-y-auto custom-scroll">
        @foreach($options as $val => $text)
            <label class="flex items-center gap-3 cursor-pointer group p-1 select-none">
                <div class="relative flex items-center shrink-0">
                    <input type="checkbox" name="{{ $name }}[]" value="{{ $val }}"
                        {{ in_array((string)$val, array_map('strval', $selectedValues)) ? 'checked' : '' }}
                        {{ $disabled }}
                        class="peer w-5 h-5 opacity-0 absolute cursor-pointer">
                    <div class="w-5 h-5 border-2 border-white/10 rounded-lg bg-white/5 peer-checked:bg-indigo-500 peer-checked:border-indigo-500 transition-all flex items-center justify-center shadow-inner">
                        <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <span class="text-sm text-white/60 group-hover:text-white truncate transition-colors">{{ $text }}</span>
            </label>
        @endforeach
    </div>
    @error($name)
        <span class="text-red-400 text-xs mt-1 block font-medium">{{ $message }}</span>
    @enderror
</div>
