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
            { id: 'image',   type: 'file',      label: 'Imagen de apoyo (opcional)', aspect: 'video' }
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
            { id: 'image',   type: 'file',      label: 'Nueva imagen (opcional)', aspect: 'video' }
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
        const data = await post('validate-content', { content: text })

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
        validationMessage.value = 'Error al conectar con el validador. Inténtalo de nuevo.'
        console.error('Validation error:', e)
    } finally {
        validationLoading.value = false
    }
}

// ── Publish ───────────────────────────────────────────────────────────────────
async function handleAction() {
    if (!current.value.url || loading.value) return

    if (!canPublish.value) {
        emit('toast', { msg: 'Debes validar el contenido antes de publicar', type: 'error' })
        return
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
            :loading="loading"
            confirm-text="Publicar"
            :confirm-disabled="!canPublish"
        >
            <GenericForm
                v-model="form"
                :fields="current.fields"
                :loading="loading"
            />

            <!-- ── Content Validator ─────────────────────────────────────── -->
            <div class="mt-3 space-y-2">

                <!-- Validate button -->
                <button
                    type="button"
                    :disabled="validationLoading || !form.content?.trim()"
                    @click="validateContent"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="{
                        'bg-blue-600 hover:bg-blue-700 text-white':       validationStatus === null,
                        'bg-green-600 hover:bg-green-700 text-white':     validationStatus === true,
                        'bg-red-600   hover:bg-red-700   text-white':     validationStatus === false,
                    }"
                >
                    <!-- Spinner while loading -->
                    <svg v-if="validationLoading" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path  class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <!-- Shield icon (idle / rejected) -->
                    <svg v-else-if="validationStatus !== true" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <!-- Check icon (approved) -->
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>

                    <span>
                        {{ validationLoading
                            ? 'Validando...'
                            : validationStatus === true
                                ? 'Contenido aprobado'
                                : validationStatus === false
                                    ? 'Volver a validar'
                                    : 'Verificar contenido' }}
                    </span>
                </button>

                <!-- Feedback message (only shown after validation) -->
                <div
                    v-if="validationStatus !== null"
                    class="flex items-start gap-2 rounded-lg px-3 py-2 text-sm"
                    :class="{
                        'bg-green-50 text-green-800 border border-green-200': validationStatus === true,
                        'bg-red-50   text-red-800   border border-red-200':   validationStatus === false,
                    }"
                >
                    <!-- Approved -->
                    <template v-if="validationStatus === true">
                        <svg class="mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>Tu pregunta puede publicarse. Haz clic en <strong>Publicar</strong>.</span>
                    </template>

                    <!-- Rejected -->
                    <template v-else>
                        <svg class="mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <div>
                            <p>{{ validationMessage || 'El contenido no es apropiado para el foro.' }}</p>
                            <p v-if="validationWords.length" class="mt-1">
                                Palabras detectadas:
                                <span class="font-semibold">{{ validationWords.join(', ') }}</span>
                            </p>
                        </div>
                    </template>
                </div>

            </div>
            <!-- ─────────────────────────────────────────────────────────── -->

        </BaseModal>
    </div>
</template>