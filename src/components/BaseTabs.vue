<script setup>
defineProps({
    tabs: { type: Array, required: true }, // Array de { id: string, label: string, icon?: string }
    modelValue: { type: String, required: true }
})
defineEmits(['update:modelValue'])
</script>

<template>
    <div class="flex gap-1 mb-8 bg-black/20 p-1 rounded-xl border border-white/5 overflow-x-auto">
        <button 
            v-for="tab in tabs" 
            :key="tab.id" 
            @click="$emit('update:modelValue', tab.id)"
            :class="[
                'flex-1 flex items-center justify-center gap-3 py-4 px-6 text-sm font-black rounded-xl transition-all cursor-pointer uppercase tracking-wider whitespace-nowrap',
                modelValue === tab.id 
                    ? 'bg-[#406071] text-white border border-white/10 shadow-xl scale-102' 
                    : 'text-white/40 hover:text-white hover:bg-white/5'
            ]"
        >
            <svg v-if="tab.icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="opacity-80">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path :d="tab.icon" />
            </svg>
            {{ tab.label }}
        </button>
    </div>
</template>
