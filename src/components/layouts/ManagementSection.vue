<script setup>
defineProps({
    title: { type: String, required: true },
    addButtonText: { type: String, default: '' }
})
defineEmits(['add'])
</script>

<template>
    <div class="flex justify-between items-center mb-6 pl-1">
        <h2 class="text-xl font-black text-white/80 uppercase tracking-tight">{{ title }}</h2>
        <button 
            v-if="addButtonText" 
            @click="$emit('add')" 
            class="bg-[#406071]/30 backdrop-blur-md hover:bg-[#406071] border border-white/10 text-white px-5 py-2.5 rounded-xl text-xs font-black transition-all shadow-md flex items-center gap-2 uppercase tracking-tighter"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            {{ addButtonText }}
        </button>
    </div>
</template>
