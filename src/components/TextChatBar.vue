<script setup>
    import { ref, watch } from 'vue'
    import { useTranslations } from '@/composables/useTranslations'
    import EmojiPicker from './common/EmojiPicker.vue'
    import { useApi } from '@/composables/useApi'

    const { t } = useTranslations()
    const { post } = useApi()

    const message      = ref('')
    const fileInput    = ref(null)
    const selectedFile = ref(null)
    const showEmojiPicker = ref(false)

    // ── Validation state ──────────────────────────────────────────────────────
    // null → not yet validated | true → approved | false → rejected
    const validationStatus  = ref(null)
    const validationLoading = ref(false)
    const validationMessage = ref('')
    const validatedText     = ref('')   // the exact string that was validated

    const props = defineProps({
        isResponse: {
            type: Boolean,
            default: false
        }
    })

    const emit = defineEmits(['sendMessage', 'typing'])

    let typingTimer = null
    watch(message, (newVal, oldVal) => {
        if (validationStatus.value !== null && newVal !== validatedText.value) {
            validationStatus.value  = null
            validationMessage.value = ''
            validatedText.value     = ''
        }
        emit('typing')
        clearTimeout(typingTimer)
        typingTimer = setTimeout(() => emit('typing-stop'), 2000)
    })

    // ── File handling ─────────────────────────────────────────────────────────
    function triggerFileInput() {
        fileInput.value.click()
    }

    function onFileChange(e) {
        const file = e.target.files[0]
        if (!file) return

        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf']
        if (!validTypes.includes(file.type)) {
            alert('Solo se permiten imágenes y PDFs')
            fileInput.value.value = ''
            return
        }

        selectedFile.value = file
    }

    function onSelectEmoji(emoji) {
        message.value += emoji.i
    }

    // ── Validate text answer via backend ──────────────────────────────────────
    async function validateMessage() {
        const text = message.value.trim()

        if (!text) return

        validationLoading.value = true
        validationStatus.value  = null
        validationMessage.value = ''

        try {
            const data = await post('validate-content', { content: text })

            if (data.status === 'success') {
                validationStatus.value  = data.es_apropiado
                validationMessage.value = data.es_apropiado
                    ? ''
                    : (data.motivo || 'El contenido no es apropiado para el foro.')

                if (data.es_apropiado) {
                    validatedText.value = text
                }
            } else {
                validationStatus.value  = false
                validationMessage.value = data.message || 'No se pudo validar el mensaje.'
            }
        } catch (e) {
            validationStatus.value  = false
            validationMessage.value = e.message || 'Error al conectar con el validador.'
            console.error('Validation error:', e)
        } finally {
            validationLoading.value = false
        }
    }

    // ── Send ──────────────────────────────────────────────────────────────────
    async function sendMessage() {
        if (validationLoading.value) return

        // Files bypass text-validation (they're images / PDFs)
        if (selectedFile.value) {
            const fileUrl = URL.createObjectURL(selectedFile.value)
            const type    = selectedFile.value.type.startsWith('image/') ? 'image' : 'pdf'

            emit('sendMessage', {
                content:  fileUrl,
                type:     type,
                fileName: selectedFile.value.name
            })

            selectedFile.value    = null
            fileInput.value.value = ''
            return
        }

        const text = message.value.trim()
        if (!text) return

        // 1. Automate validation if isResponse and not yet done
        if (props.isResponse && validationStatus.value !== true) {
            await validateMessage()
            if (validationStatus.value !== true) {
                return
            }
        }

        emit('sendMessage', { content: text, type: 'text' })
        message.value           = ''
        validationStatus.value  = null
        validationMessage.value = ''
        validatedText.value     = ''
    }

    // Allow Enter to send
    async function onEnter() {
        if (validationLoading.value) return
        await sendMessage()
        showEmojiPicker.value = false
    }
</script>

<template>
    <div class="flex flex-col my-3 w-full gap-1">

        <!-- Validation feedback strip (only visible if rejected) -->
        <transition name="slide-fade">
            <div
                v-if="validationStatus === false"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-900/40 text-red-300 border border-red-700/50 shadow-lg"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ validationMessage }}</span>
            </div>
        </transition>

        <!-- Input bar -->
        <div class="flex items-center border border-white rounded-[20px] p-3.75 w-full relative">
            <input
                ref="fileInput"
                type="file"
                id="telamofile"
                name="telamofile"
                class="hidden"
                accept="image/*,application/pdf"
                @change="onFileChange"
            >
            <svg @click="triggerFileInput" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="hover:bg-[#152027] rounded-12 cursor-pointer icon icon-tabler icons-tabler-filled icon-tabler-plus shrink-0">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 4a1 1 0 0 1 1 1v6h6a1 1 0 0 1 0 2h-6v6a1 1 0 0 1 -2 0v-6h-6a1 1 0 0 1 0 -2h6v-6a1 1 0 0 1 1 -1"/>
            </svg>

            <input
                v-model="message"
                type="text"
                id="chatBar"
                class="mx-2 w-full outline-hidden text-base border-none bg-transparent flex-1 text-[#e7e9ea] placeholder-[#8b98a5] placeholder-font-normal"
                :placeholder="selectedFile ? `Archivo: ${selectedFile.name}` : (t.nav.search || 'Escribir un mensaje')"
                @keydown.enter="onEnter"
            />

            <div class="flex items-center gap-1 shrink-0 relative">
                <EmojiPicker
                    v-model:show="showEmojiPicker"
                    @select="onSelectEmoji"
                    customClass="right-0 bottom-full mb-2"
                />
                <button @click="showEmojiPicker = !showEmojiPicker" type="button" class="hover:text-white transition-colors cursor-pointer text-white/50 hover:bg-[#152027] rounded-lg p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M8 9h.01"/><path d="M16 9h.01"/><path d="M9 15c1 .667 2 1 3 1s2-.333 3-1"/>
                    </svg>
                </button>

                <!-- Send button -->
                <button
                    @click="sendMessage"
                    :disabled="validationLoading"
                    class="disabled:opacity-50 transition-opacity"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"
                        class="icon icon-tabler icons-tabler-filled icon-tabler-square-arrow-right shrink-0 transition-opacity cursor-pointer"
                    >
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M19 2a3 3 0 0 1 3 3v14a3 3 0 0 1 -3 3h-14a3 3 0 0 1 -3 -3v-14a3 3 0 0 1 3 -3zm-6.387 5.21a1 1 0 0 0 -1.32 .083l-.083 .094a1 1 0 0 0 .083 1.32l2.292 2.293h-5.585l-.117 .007a1 1 0 0 0 .117 1.993h5.585l-2.292 2.293l-.083 .094a1 1 0 0 0 1.497 1.32l4 -4l.073 -.082l.074 -.104l.052 -.098l.044 -.11l.03 -.112l.017 -.126l.003 -.075l-.007 -.118l-.029 -.148l-.035 -.105l-.054 -.113l-.071 -.111a1.008 1.008 0 0 0 -.097 -.112l-4 -4z"/>
                    </svg>
                </button>
            </div>
        </div>

    </div>
</template>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.2s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>