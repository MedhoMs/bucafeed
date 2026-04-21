<script setup>
defineProps({
    tabs: { type: Array, required: true }, // Array de { id: string, label: string, icon?: string }
    modelValue: { type: String, required: true }
})
defineEmits(['update:modelValue'])
</script>

<template>
    <div class="flex gap-1 mb-8 bg-black/20 backdrop-blur-sm p-1 rounded-xl border border-white/5">
        <button 
            v-for="tab in tabs" 
            :key="tab.id" 
            @click="$emit('update:modelValue', tab.id)"
            :class="[
                'flex-1 flex items-center justify-center gap-2 py-3 px-3 text-xs font-black rounded-[10px] transition-all cursor-pointer uppercase tracking-tight',
                modelValue === tab.id 
                    ? 'bg-[#406071] text-white border border-white/10 shadow-lg' 
                    : 'text-white/30 hover:text-white hover:bg-white/5'
            ]"
        >
            <svg v-if="tab.icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-60">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path :d="tab.icon" />
            </svg>
            {{ tab.label }}
        </button>
    </div>
</template>
