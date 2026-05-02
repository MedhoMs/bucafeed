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

// Standardized form state
const form = ref({
    title: '',
    content: '',
    image: null
})

// Reset form when modal opens
watch(() => props.activeModal, (val) => {
    if (!val) return
    
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

const MODAL_MAP = computed(() => ({
    question: {
        title: 'Nueva Pregunta',
        msg: 'Pregunta publicada con éxito',
        url: 'questions',
        fields: [
            { id: 'title', type: 'text', label: 'Título de la duda', placeholder: 'Ej: ¿Cómo configurar Docker?', required: true },
            { id: 'content', type: 'textarea', label: 'Explicación detallada', placeholder: 'Describe tu problema o duda aquí...', required: true },
            { id: 'image', type: 'file', label: 'Imagen de apoyo (opcional)', aspect: 'video' }
        ]
    },
    edit_question: {
        title: 'Editar Pregunta',
        msg: 'Pregunta actualizada correctamente',
        url: `questions/${props.question?.id}`,
        method: 'PUT',
        fields: [
            { id: 'title', type: 'text', label: 'Título de la duda', placeholder: 'Ej: ¿Cómo configurar Docker?', required: true },
            { id: 'content', type: 'textarea', label: 'Explicación detallada', placeholder: 'Describe tu problema o duda aquí...', required: true },
            { id: 'image', type: 'file', label: 'Nueva imagen (opcional)', aspect: 'video' }
        ]
    }
}))

const current = computed(() => MODAL_MAP.value[props.activeModal] || {})

async function handleAction() {
    if (!current.value.url || loading.value) return

    try {
        const formData = new FormData()
        formData.append('title', form.value.title)
        formData.append('content', form.value.content)
        formData.append('user_id', authUser.value?.id)
        
        if (form.value.image) {
            formData.append('image', form.value.image)
        }

        // Laravel style for PUT with FormData
        if (current.value.method === 'PUT') {
            formData.append('_method', 'PUT')
        }

        await post(current.value.url, formData)
        
        emit('toast', { msg: current.value.msg })
        emit('refresh')
        emit('close')
    } catch (e) {
        console.error("Error in ForumManagerCore:", e)
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
        >
            <GenericForm 
                v-model="form" 
                :fields="current.fields" 
                :loading="loading"
            />
        </BaseModal>
    </div>
</template>
