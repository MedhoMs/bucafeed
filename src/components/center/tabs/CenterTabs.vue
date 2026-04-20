<script setup>
import BaseTabs from '@/components/BaseTabs.vue'

defineProps({
    modelValue: { type: String, required: true }
})
defineEmits(['update:modelValue'])

const centerTabs = [
    { id: 'overview', label: 'Grupos',   icon: 'M3 21l18 0 M5 21v-14l8 -4v18 M19 21v-10l-6 -4' },
    { id: 'people',   label: 'Personas', icon: 'M12 12m-9 8v-1a6 6 0 0 1 12 0v1 M12 10a4 4 0 1 1 0 -8a4 4 0 0 1 0 8z' },
    { id: 'cycles',   label: 'Ciclos',   icon: 'M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2l0 -12 M16 3l0 4 M8 3l0 4' }
]
</script>

<template>
    <BaseTabs 
        :tabs="centerTabs"
        :modelValue="modelValue"
        @update:modelValue="$emit('update:modelValue', $event)"
    />
</template>
