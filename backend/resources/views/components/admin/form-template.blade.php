@props(['fields' => [], 'disabled' => ''])

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @foreach($fields as $field)
        @php
            $type = $field['type'] ?? 'text';
            $component = 'admin.form.' . ($type === 'textarea' ? 'textarea' : ($type === 'select' || isset($field['options']) ? 'select' : 'input'));
            
            if (isset($field['component'])) {
                $component = 'admin.form.' . $field['component'];
            }
            
            $isFull = $field['full'] ?? false;
            $colSpan = $isFull ? 'md:col-span-2' : '';
        @endphp
        
        <div class="{{ $colSpan }}">
            @if(($field['type'] ?? '') === 'file' && !empty($field['previewUrl']))
                <div class="mb-2">
                    <img src="{{ $field['previewUrl'] }}" class="w-12 h-12 rounded-xl border border-white/10 object-cover bg-slate-800">
                </div>
            @endif
            <x-dynamic-component 
                :component="$component" 
                :name="$field['name'] ?? ''" 
                :label="$field['label'] ?? ''"
                :type="$type"
                :value="$field['value'] ?? ''"
                :placeholder="$field['placeholder'] ?? ''"
                :options="$field['options'] ?? []"
                :selectedValue="$field['selectedValue'] ?? ''"
                :rows="$field['rows'] ?? 4"
                :disabled="$disabled"
                :required="$field['required'] ?? false"
            />
        </div>
    @endforeach
</div>
