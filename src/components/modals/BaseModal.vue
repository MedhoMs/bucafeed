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
    <div class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm flex items-center justify-center px-4" @click.self="$emit('close')">
        <div class="bg-[#0f2828] border border-white/10 rounded-3xl p-8 w-full max-w-[480px] shadow-[0_25px_80px_rgba(0,0,0,0.8)]">
            <h3 class="text-2xl font-black text-white mb-8 uppercase tracking-tighter">{{ title }}</h3>
            
            <div class="space-y-4">
                <slot></slot>
            </div>
            
            <div v-if="!hideFooter" class="flex justify-end gap-4 mt-10">
                <slot name="footer">
                    <button 
                        @click="$emit('close')" 
                        class="flex-1 py-4 px-6 rounded-2xl text-xs font-black uppercase text-white/40 bg-white/5 border border-white/5 cursor-pointer hover:text-white hover:bg-white/10 transition-all"
                    >
                        {{ cancelText }}
                    </button>
                    <button 
                        @click="$emit('confirm')" 
                        :disabled="isConfirmDisabled"
                        class="w-full md:w-auto bg-accent-normal hover:bg-accent-normal-hover text-white text-[11px] font-black uppercase tracking-[0.2em] px-10 py-4.5 rounded-[22px] transition-all active:scale-95 shadow-xl shadow-accent-normal/20 flex items-center justify-center gap-3 group border border-white/5 shrink-0 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        {{ confirmText }}
                    </button>
                </slot>
            </div>
        </div>
    </div>
</template>
