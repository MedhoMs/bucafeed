<script setup>
import { ref, computed } from 'vue'
import { useTranslations } from '@/composables/useTranslations'

const { t } = useTranslations()

const props = defineProps({
    modelValue: { type: [Object, File, String], default: null },
    label: { type: String, default: null },
    previewUrl: { type: String, default: null },
    aspect: { type: String, default: 'square' }, // square, video, banner
    variant: { type: String, default: 'classic' }, // classic, minimal
    showModerationWarning: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue'])

const isHovered = ref(false)
const fileInput = ref(null)

const handleFile = (event) => {
    const file = event.target.files[0]
    if (!file) return
    
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
    <div :class="variant === 'classic' ? 'space-y-3' : ''">
        <!-- Variant Classic (The dashed box) -->
        <div v-if="variant === 'classic'"
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
                class="absolute inset-0 w-full h-full object-contain transition-transform duration-700 group-hover:scale-110"
                :alt="t.validation?.imageUpload?.previewAlt || 'Vista previa'"
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
                    {{ (modelValue || previewUrl) ? (t.validation?.imageUpload?.changeImage || 'Cambiar Imagen') : (label || t.validation?.imageUpload?.selectImage || 'Seleccionar Imagen') }}
                </p>
            </div>
        </div>

        <!-- Variant Minimal (The icon button for answers) -->
        <div v-else class="flex items-center gap-2">
            <!-- Mini Preview -->
            <div v-if="displayUrl" class="relative w-10 h-10 rounded-lg overflow-hidden border border-white/20 animate-fade-in">
                <img :src="displayUrl" class="w-full h-full object-cover" />
                <button 
                    type="button"
                    @click.stop="emit('update:modelValue', null)" 
                    class="absolute top-0 right-0 bg-black/60 rounded-bl-lg p-0.5 hover:bg-red-500 transition-colors cursor-pointer"
                >
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <button
                type="button"
                @click="triggerInput"
                class="hover:text-emerald-400 transition-colors cursor-pointer" 
                :title="t.validation?.imageUpload?.uploadImage || 'Subir Imagen'"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                    <circle cx="9" cy="9" r="2"/>
                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                </svg>
            </button>
        </div>

        <input 
            ref="fileInput"
            type="file" 
            accept="image/*"
            class="hidden" 
            @change="handleFile"
        >

        <!-- Success Message -->
        <div v-if="variant === 'classic' && modelValue" class="flex items-center gap-2 text-[9px] font-black uppercase tracking-widest text-emerald-400 ml-2 animate-pulse">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            {{ t.validation?.imageUpload?.imageReady || 'Imagen Lista para subir' }}
        </div>

        <!-- Moderation Warning -->
        <div v-if="showModerationWarning && modelValue" class="mt-2 flex items-start gap-2 px-3 py-2 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-200/80 animate-in fade-in slide-in-from-top-1 duration-300">
            <svg class="mt-0.5 shrink-0 text-amber-500" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
            </svg>
            <p class="text-[10px] leading-tight font-medium">
                {{ t.validation?.imageUpload?.moderationWarning || 'Los administradores podrán borrar tu publicación si se detecta contenido inapropiado en la foto.' }}
            </p>
        </div>
    </div>
</template>

<style scoped>
</style>
