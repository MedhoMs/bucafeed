<script setup>
import { computed } from 'vue'
import BaseTabs from '@/components/BaseTabs.vue'

const props = defineProps({
    modelValue: { type: String, required: true },
    center: { type: Object, default: () => ({ type: 'HE', category: '' }) }
})
defineEmits(['update:modelValue'])

const centerTabs = computed(() => {
    let cyclesLabel = 'Ciclos'
    const type = props.center?.type
    const category = props.center?.category?.toLowerCase() || ''

    if (type === 'PE') cyclesLabel = 'Cursos'
    if (type === 'HE' && (category === 'university' || category === 'universidad')) cyclesLabel = 'Grados'
    
    return [
        { id: 'overview', label: 'Grupos',   icon: 'M3 21l18 0 M5 21v-14l8 -4v18 M19 21v-10l-6 -4' },
        { id: 'people',   label: 'Personas', icon: 'M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2' },
        { id: 'cycles',   label: cyclesLabel, icon: 'M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2l0 -12 M16 3l0 4 M8 3l0 4' },
        { id: 'events',   label: 'Eventos',  icon: 'M12 2v4m0 16v-4m8-8h-4M4 12H2m18 0h-2M6.34 6.34l-1.41-1.41m12.72 0l-1.41 1.41M6.34 17.66l-1.41 1.41m12.72 0l-1.41-1.41' },
        { id: 'publications', label: 'Publicaciones', icon: 'M3 5a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z M7 7h10 M7 12h10 M7 17h10' }
    ]
})
</script>

<template>
    <BaseTabs 
        :tabs="centerTabs"
        :modelValue="modelValue"
        @update:modelValue="$emit('update:modelValue', $event)"
    />
</template>
