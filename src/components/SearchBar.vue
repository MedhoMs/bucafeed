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
    <div class="flex my-3 w-full">
        <div class="flex flex-row items-center bg-[#2a4a5a] rounded-[20px] p-[15px] min-w-full hover:bg-[#334d5e] focus-within:bg-[#334d5e] focus-within:outline focus-within:outline-1 focus-within:outline-[#1da1f2]">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-[#8b98a5]" ><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
            <input 
                v-model="search"
                type="text" 
                :placeholder="t.nav.search || 'Buscar'"
                class="ml-3 w-full outline-none text-base border-none bg-transparent flex-1 text-[#e7e9ea] placeholder-[#8b98a5]"
            />
        </div>
    </div>
</template>