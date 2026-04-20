<script setup>
import { ref } from 'vue'
import BaseModal from '@/components/modals/BaseModal.vue'

const props = defineProps({
    activeModal: { type: String, default: null },
    group: { type: Object, default: null },
    teachers: { type: Array, default: () => [] },
    students: { type: Array, default: () => [] },
    cycles: { type: Array, default: () => [] },
    apiBase: { type: String, required: true },
    headers: { type: Object, required: true }
})

const emit = defineEmits(['close', 'refresh', 'toast'])

// Form states
const newGroup = ref({ name: '', cycle_id: null, educational_center_id: null })
const selectedStudentIds = ref([])
const selectedTutorId = ref(null)
const selectedSubject = ref({ tag_id: null, user_id: null })

const closeModal = () => emit('close')

async function handleAction() {
    console.log("Confirm button clicked");
    const config = {
        create: { 
            url: '/my-center/groups', method: 'POST', 
            body: { ...newGroup.value, educational_center_id: props.group?.educational_center_id || 1 },
            msg: 'Grupo creado' 
        },
        tutor: { 
            url: `/my-center/groups/${props.group?.id}/tutor`, method: 'POST', 
            body: { user_id: selectedTutorId.value },
            msg: 'Tutor asignado' 
        },
        students: { 
            url: `/my-center/groups/${props.group?.id}/students`, method: 'POST', 
            body: { student_ids: selectedStudentIds.value },
            msg: 'Alumnos asignados' 
        },
        subject: { 
            url: `/my-center/groups/${props.group?.id}/subjects`, method: 'POST', 
            body: { tag_id: selectedSubject.value.tag_id, user_id: selectedSubject.value.user_id },
            msg: 'Materia asignada' 
        }
    }[props.activeModal]

    if (!config) return;

    try {
        const res = await fetch(`${props.apiBase}${config.url}`, {
            method: config.method,
            headers: props.headers,
            body: JSON.stringify(config.body)
        })
        if (res.ok) {
            emit('toast', { msg: config.msg })
            emit('refresh')
            closeModal()
        }
    } catch (e) {
        emit('toast', { msg: 'Error de red', type: 'error' })
    }
}
</script>

<template>
    <div v-if="activeModal" class="center-modal-hub">
        <BaseModal 
            :show="!!activeModal" 
            @close="closeModal" 
            @confirm="handleAction"
            :title="`Gestión: ${activeModal?.toUpperCase()}`"
        >
            <div class="space-y-6">
                
                <!-- ASIGNAR MATERIA (Basado en la captura) -->
                <div v-if="activeModal === 'subject'" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-white/30 tracking-widest mb-2">Asignatura</label>
                        <select v-model="selectedSubject.tag_id" class="custom-select w-full bg-[#1a2332] text-white border border-white/10 rounded-xl px-4 py-3 text-sm outline-none">
                            <template v-for="c in cycles" :key="'g'+c.id">
                                <optgroup :label="c.name">
                                    <option v-for="tag in c.tags" :key="tag.id" :value="tag.id">{{ tag.name }}</option>
                                </optgroup>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-white/30 tracking-widest mb-2">Docente</label>
                        <select v-model="selectedSubject.user_id" class="custom-select w-full bg-[#1a2332] text-white border border-white/10 rounded-xl px-4 py-3 text-sm outline-none">
                            <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- (Otros modales omitidos aquí para brevedad, se pueden añadir según se necesiten) -->
                <div v-else class="text-white/40 text-xs italic p-4 bg-white/5 rounded-xl border border-dotted border-white/10">
                    Formulario para {{ activeModal }} disponible.
                </div>
            </div>
        </BaseModal>
    </div>
</template>

<style scoped>
.custom-select {
    color-scheme: dark;
}
.custom-select option {
    background-color: #1a2332 !important;
    color: white !important;
}
.custom-select optgroup {
    background-color: #0d141d !important;
    color: #406071 !important;
}
</style>
