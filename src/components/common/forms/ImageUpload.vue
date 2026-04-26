<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    modelValue: { type: String, default: null },
    label: { type: String, default: 'Seleccionar Imagen' },
    previewUrl: { type: String, default: null },
    aspect: { type: String, default: 'square' } // square, video, banner
})

const emit = defineEmits(['update:modelValue'])

const isHovered = ref(false)
const fileInput = ref(null)

const handleFile = (event) => {
    const file = event.target.files[0]
    if (!file) return
    
    // Generar preview local (rápido)
    const localUrl = URL.createObjectURL(file)
    emit('update:modelValue', file) // Emitir el objeto FILE real
}

const triggerInput = () => {
    fileInput.value.click()
}

const displayUrl = computed(() => {
    if (!props.modelValue) return props.previewUrl
    if (typeof props.modelValue === 'string') return props.modelValue
    if (props.modelValue instanceof File) return URL.createObjectURL(props.modelValue)
    return props.previewUrl
})
</script>

<template>
    <div class="space-y-3">
        <div 
            @click="triggerInput"
            @mouseenter="isHovered = true"
            @mouseleave="isHovered = false"
            :class="[
                'relative overflow-hidden rounded-2xl border-2 border-dashed transition-all cursor-pointer group',
                isHovered ? 'border-emerald-500/50 bg-emerald-500/5' : 'border-white/10 bg-white/5',
                aspect === 'square' ? 'aspect-square max-w-[200px]' : 
                aspect === 'video' ? 'aspect-video' : 'aspect-[21/9]'
            ]"
        >
            <!-- Preview -->
            <img v-if="displayUrl" 
                :src="displayUrl" 
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                alt="Vista previa"
            >
            
            <!-- Overlay -->
            <div :class="[
                'absolute inset-0 flex flex-col items-center justify-center gap-3 transition-all duration-300',
                (modelValue || previewUrl) ? 'opacity-0 group-hover:opacity-100 bg-black/60 backdrop-blur-sm' : 'opacity-100'
            ]">
                <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                </div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/60 group-hover:text-white">
                    {{ (modelValue || previewUrl) ? 'Cambiar Imagen' : label }}
                </p>
            </div>

            <input 
                ref="fileInput"
                type="file" 
                accept="image/*"
                class="hidden" 
                @change="handleFile"
            >
        </div>

        <!-- Success Message -->
        <div v-if="modelValue" class="flex items-center gap-2 text-[9px] font-black uppercase tracking-widest text-emerald-400 ml-2 animate-pulse">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            Imagen Lista para subir
        </div>
    </div>
</template>

<style scoped>
</style>
