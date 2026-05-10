<script setup>
defineProps({
    text: { type: String, required: true },
    icon: { type: String, default: 'plus' }, // plus, edit, delete, etc.
    loading: { type: Boolean, default: false }
})

defineEmits(['click'])
</script>

<template>
    <button 
        @click="$emit('click')"
        :disabled="loading"
        class="w-full md:w-auto bg-secondary-normal hover:bg-secondary-normal-hover text-white text-[11px] font-black uppercase tracking-[0.2em] px-10 py-4.5 rounded-[22px] transition-all active:scale-95 shadow-xl shadow-secondary-normal/10 flex items-center justify-center gap-3 group border border-white/5 shrink-0 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
    >
        <template v-if="!loading">
            <!-- Icono Plus (Default) -->
            <svg v-if="icon === 'plus'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" class="group-hover:rotate-90 transition-transform duration-500">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <!-- Icono Edit -->
            <svg v-else-if="icon === 'edit'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
        </template>
        
        <!-- Spinner Loading -->
        <div v-else class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
        
        <span>{{ text }}</span>
    </button>
</template>
