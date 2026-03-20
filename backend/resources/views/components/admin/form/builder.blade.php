@props(['rows' => [], 'disabled' => ''])

@foreach($rows as $idx => $fields)
    @php
        $marginTop = $idx === 0 ? '' : 'mt-6';
        $cols = count($fields);
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-{{ $cols }} gap-6 {{ $marginTop }}">
        @foreach($fields as $field)
            @php
                $component = 'admin.form.' . ($field['component'] ?? 'input');
                $type = $field['type'] ?? 'text';
            @endphp
            
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
            />
        @endforeach
    </div>
@endforeach
