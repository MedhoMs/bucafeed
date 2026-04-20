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
    <div class="fixed inset-0 z-[100] bg-black/70 flex items-center justify-center backdrop-blur-sm" @click.self="$emit('close')">
        <div class="bg-[#1a2332] border border-white/10 rounded-2xl p-7 w-[90%] max-w-[460px] shadow-[0_25px_60px_rgba(0,0,0,0.5)]">
            <h3 class="text-lg font-bold mb-4">{{ title }}</h3>
            
            <div class="space-y-3">
                <slot></slot>
            </div>
            
            <div v-if="!hideFooter" class="flex justify-end gap-2 mt-5">
                <slot name="footer">
                    <button @click="$emit('close')" class="py-2 px-5 rounded-lg text-[13px] font-semibold text-white/50 bg-white/5 cursor-pointer hover:text-white hover:bg-white/10">
                        {{ cancelText }}
                    </button>
                    <button 
                        @click="$emit('confirm')" 
                        :disabled="isConfirmDisabled"
                        class="py-2 px-5 rounded-lg text-[13px] font-semibold text-white bg-emerald-500 cursor-pointer hover:bg-emerald-600 disabled:opacity-40 disabled:cursor-not-allowed">
                        {{ confirmText }}
                    </button>
                </slot>
            </div>
        </div>
    </div>
</template>
