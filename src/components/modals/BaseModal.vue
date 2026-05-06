<script setup>
defineProps({
    title: { type: String, required: true },
    confirmText: { type: String, default: 'Confirmar' },
    cancelText: { type: String, default: 'Cancelar' },
    isConfirmDisabled: { type: Boolean, default: false },
    hideFooter: { type: Boolean, default: false }
})
defineEmits(['close', 'confirm'])
</script>

<template>
    <div class="fixed inset-0 z-[100] bg-black/70 backdrop-blur-sm flex items-center justify-center px-4" @click.self="$emit('close')">
        <div class="bg-[#1a2332] border border-white/10 rounded-2xl p-7 w-full max-w-[460px] shadow-[0_25px_60px_rgba(0,0,0,0.5)]">
            <h3 class="text-xl font-black text-white/90 mb-6 uppercase tracking-tight">{{ title }}</h3>
            
            <div class="space-y-3">
                <slot></slot>
            </div>
            
            <div v-if="!hideFooter" class="flex justify-end gap-3 mt-8">
                <slot name="footer">
                    <button 
                        @click="$emit('close')" 
                        class="flex-1 py-3 px-5 rounded-xl text-xs font-black uppercase text-white/30 bg-white/5 border border-white/5 cursor-pointer hover:text-white hover:bg-white/10 transition-all"
                    >
                        {{ cancelText }}
                    </button>
                    <button 
                        @click="$emit('confirm')" 
                        :disabled="isConfirmDisabled"
                        class="flex-1 py-3 px-5 rounded-xl text-xs font-black uppercase text-white bg-[#406071] shadow-lg hover:shadow-[#406071]/20 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed transition-all border border-white/10"
                    >
                        {{ confirmText }}
                    </button>
                </slot>
            </div>
        </div>
    </div>
</template>
