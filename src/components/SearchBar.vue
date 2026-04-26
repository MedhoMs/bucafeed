<script setup>
import { ref, computed, watch } from 'vue'
import { useTranslations } from '@/composables/useTranslations'
const { t } = useTranslations()

const search = ref('')

const props = defineProps({
    meetings: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['update:filtered'])

const filteredMeetings = computed(() => {
    if (!search.value.trim()) return props.meetings
    return props.meetings.filter(m =>
        m.name.toLowerCase().includes(search.value.toLowerCase())
    )
})

// Emit whenever filteredMeetings changes
watch(filteredMeetings, (newValue) => {
    emit('update:filtered', newValue)
}, { immediate: true })
</script>



<template>
    <div class="group flex items-center bg-white/5 border border-white/10 rounded-[22px] px-5 py-3.5 transition-all focus-within:bg-white/10 focus-within:border-cyan-500/50 focus-within:ring-1 focus-within:ring-cyan-500/20">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-white/20 group-focus-within:text-cyan-400 transition-colors">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <circle cx="10" cy="10" r="7" />
            <path d="M21 21l-6 -6" />
        </svg>
        <input 
            v-model="search"
            type="text" 
            :placeholder="t.nav.search || 'Buscar contenido...'" 
            class="ml-4 w-full flex-1 outline-none border-none bg-transparent text-sm font-bold text-white placeholder:text-white/20 tracking-tight"
        >
    </div>
</template>
