<script setup>
/**
 * ForumManagerCore.vue - Unified modal manager for Forum actions
 * Following the pattern of CenterManagerCore but for Forum-specific needs.
 */
import { ref, computed, watch } from 'vue'
import BaseModal from '@/components/modals/BaseModal.vue'
import GenericForm from '@/components/common/forms/GenericForm.vue'
import { useApi } from '@/composables/useApi'
import { user as authUser } from '@/stores/auth'

const props = defineProps({
    activeModal: { type: String, default: null },
    question: { type: Object, default: null }
})

const emit = defineEmits(['close', 'refresh', 'toast'])
const { post, loading } = useApi()

// ── Form state ────────────────────────────────────────────────────────────────
const form = ref({
    title: '',
    content: '',
    image: null
})

// ── Validation state ──────────────────────────────────────────────────────────
// null  → not validated yet
// true  → validated and approved
// false → validated and rejected
const validationStatus  = ref(null)
const validationLoading = ref(false)
const validationMessage = ref('')
const validationWords   = ref([])

// Track the exact content string that was validated so we can detect edits
const validatedContent  = ref('')

/**
 * Resets the validation whenever the user touches title or content,
 * so they can't validate one thing and then submit another.
 */
watch(
    () => [form.value.title, form.value.content],
    ([newTitle, newContent]) => {
        // Only invalidate if content changed after a successful validation
        if (validationStatus.value !== null && newContent !== validatedContent.value) {
            validationStatus.value  = null
            validationMessage.value = ''
            validationWords.value   = []
            validatedContent.value  = ''
        }
    }
)

// Reset everything when the modal opens or changes
watch(() => props.activeModal, (val) => {
    if (!val) return

    validationStatus.value  = null
    validationMessage.value = ''
    validationWords.value   = []
    validatedContent.value  = ''

    if (val === 'edit_question' && props.question) {
        form.value = {
            title: props.question.title,
            content: props.question.content,
            image: null
        }
    } else {
        form.value = { title: '', content: '', image: null }
    }
})

// ── Modal config map ──────────────────────────────────────────────────────────
const MODAL_MAP = computed(() => ({
    question: {
        title: 'Nueva Pregunta',
        msg: 'Pregunta publicada con éxito',
        url: 'questions',
        fields: [
            { id: 'title',   type: 'text',     label: 'Título de la duda',      placeholder: 'Ej: ¿Cómo configurar Docker?',       required: true },
            { id: 'content', type: 'textarea',  label: 'Explicación detallada',  placeholder: 'Describe tu problema o duda aquí...', required: true },
            { id: 'image',   type: 'file',      label: 'Imagen de apoyo (opcional)', aspect: 'video', showModerationWarning: true }
        ]
    },
    edit_question: {
        title: 'Editar Pregunta',
        msg: 'Pregunta actualizada correctamente',
        url: `questions/${props.question?.id}`,
        method: 'PUT',
        fields: [
            { id: 'title',   type: 'text',     label: 'Título de la duda',      placeholder: 'Ej: ¿Cómo configurar Docker?',       required: true },
            { id: 'content', type: 'textarea',  label: 'Explicación detallada',  placeholder: 'Describe tu problema o duda aquí...', required: true },
            { id: 'image',   type: 'file',      label: 'Nueva imagen (opcional)', aspect: 'video', showModerationWarning: true }
        ]
    }
}))

const current = computed(() => MODAL_MAP.value[props.activeModal] || {})

// Publish button is only enabled when validation passed
const canPublish = computed(() =>
    validationStatus.value === true && !validationLoading.value
)

// ── Validate content via backend proxy ────────────────────────────────────────
async function validateContent() {
    const text = (form.value.content || '').trim()

    if (!text) {
        emit('toast', { msg: 'Escribe el contenido antes de validarlo', type: 'warning' })
        return
    }

    validationLoading.value = true
    validationStatus.value  = null
    validationMessage.value = ''
    validationWords.value   = []

    try {
        // useApi's post() returns the response data directly
        const data = await post('validate-content', { 
            title: form.value.title,
            content: text 
        })

        if (data.status === 'success') {
            validationStatus.value  = data.es_apropiado
            validationMessage.value = data.motivo || ''
            validationWords.value   = data.palabras_detectadas || []

            if (data.es_apropiado) {
                // Remember exactly what was validated
                validatedContent.value = text
            }
        } else {
            validationStatus.value  = false
            validationMessage.value = data.message || 'No se pudo validar el contenido'
        }
    } catch (e) {
        validationStatus.value  = false
        validationMessage.value = e.message || 'Error al conectar con el validador. Inténtalo de nuevo.'
        console.error('Validation error:', e)
    } finally {
        validationLoading.value = false
    }
}

// ── Publish ───────────────────────────────────────────────────────────────────
async function handleAction() {
    if (!current.value.url || loading.value || validationLoading.value) return

    // 1. Automate validation if not done yet or content changed
    if (validationStatus.value !== true) {
        await validateContent()
        // If still not approved after validation, stop here
        if (validationStatus.value !== true) {
            return 
        }
    }

    try {
        const formData = new FormData()
        formData.append('title',   form.value.title)
        formData.append('content', form.value.content)
        formData.append('user_id', authUser.value?.id)

        if (form.value.image) {
            formData.append('image', form.value.image)
        }

        if (current.value.method === 'PUT') {
            formData.append('_method', 'PUT')
        }

        await post(current.value.url, formData)

        emit('toast', { msg: current.value.msg })
        emit('refresh')
        emit('close')
    } catch (e) {
        console.error('Error in ForumManagerCore:', e)
        emit('toast', { msg: e.message || 'Error al procesar la solicitud', type: 'error' })
    }
}
</script>

<template>
    <div v-if="activeModal">
        <BaseModal
            :show="!!activeModal"
            @close="$emit('close')"
            @confirm="handleAction"
            :title="current.title"
            :loading="loading || validationLoading"
            confirm-text="Publicar"
        >
            <GenericForm
                v-model="form"
                :fields="current.fields"
                :loading="loading || validationLoading"
            />

            <!-- ── Error Feedback (only if rejected) ──────────────────────── -->
            <div v-if="validationStatus === false" class="mt-4 flex items-start gap-2 rounded-xl px-4 py-3 text-sm bg-red-500/10 text-red-200 border border-red-500/20 shadow-lg animate-in fade-in slide-in-from-top-2 duration-300">
                <svg class="mt-0.5 shrink-0 text-red-500" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div class="space-y-1">
                    <p class="font-bold uppercase tracking-tight text-[11px]">{{ validationMessage || 'Contenido inapropiado' }}</p>
                    <p v-if="validationWords.length" class="opacity-70 uppercase text-[11px]">
                        Detectado: <span class="font-mono text-red-400 text-[12px]">{{ validationWords.join(', ') }}</span>
                    </p>
                </div>
            </div>
        </BaseModal>
    </div>
</template>