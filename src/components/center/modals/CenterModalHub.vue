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

async function handleAction(type) {
    const config = {
        create: { 
            url: '/my-center/groups', method: 'POST', 
            body: { ...newGroup.value, educational_center_id: props.group?.educational_center_id || 1 }, // Default if not found
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
    }[type]

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
    <div v-if="activeModal">
        <BaseModal 
            :show="!!activeModal" 
            @close="closeModal" 
            :title="activeModal === 'create' ? 'Crear Nuevo Grupo' : 'Configurar Grupo'"
            :hideFooter="true"
        >
            <div class="space-y-6">
                
                <!-- CREAR GRUPO -->
                <div v-if="activeModal === 'create'" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-white/30 tracking-widest mb-2">Nombre del Grupo</label>
                        <input v-model="newGroup.name" type="text" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:border-[#406071] outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-white/30 tracking-widest mb-2">Ciclo Formativo</label>
                        <select v-model="newGroup.cycle_id" class="w-full bg-[#1a2332] border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:border-[#406071] outline-none">
                            <option v-for="c in cycles" :key="c.id" :value="c.id" class="bg-[#1a2332] text-white">{{ c.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- ASIGNAR TUTOR -->
                <div v-if="activeModal === 'tutor'" class="space-y-4">
                    <p class="text-xs text-white/40 font-bold uppercase tracking-tight">Selecciona el tutor para <b>{{ group?.name }}</b></p>
                    <select v-model="selectedTutorId" class="w-full bg-[#1a2332] border border-white/10 rounded-xl px-4 py-3 text-white text-sm outline-none">
                        <option v-for="t in teachers" :key="t.id" :value="t.id" class="bg-[#1a2332] text-white">{{ t.name }} {{ t.last_name }}</option>
                    </select>
                </div>

                <!-- ASIGNAR ALUMNOS -->
                <div v-if="activeModal === 'students'" class="space-y-4">
                    <p class="text-xs text-white/40 font-bold uppercase tracking-tight">Selecciona alumnos para <b>{{ group?.name }}</b></p>
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                        <label v-for="s in students" :key="s.id" class="flex items-center gap-3 p-3 bg-white/5 rounded-xl border border-white/5 cursor-pointer hover:bg-white/10 transition-all">
                            <input type="checkbox" :value="s.id" v-model="selectedStudentIds" class="w-4 h-4 accent-[#406071]">
                            <span class="text-sm text-white/80 font-black uppercase tracking-tight">{{ s.name }}</span>
                        </label>
                    </div>
                </div>

                <!-- ASIGNAR MATERIA -->
                <div v-if="activeModal === 'subject'" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-white/30 tracking-widest mb-2">Materia</label>
                        <select v-model="selectedSubject.tag_id" class="w-full bg-[#1a2332] border border-white/10 rounded-xl px-4 py-3 text-white text-sm outline-none">
                            <template v-for="c in cycles" :key="'g'+c.id">
                                <optgroup :label="c.name" class="bg-[#1a2332] text-[#406071] font-black text-[10px] uppercase">
                                    <option v-for="tag in c.tags" :key="tag.id" :value="tag.id" class="bg-[#1a2332] text-white">{{ tag.name }}</option>
                                </optgroup>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-white/30 tracking-widest mb-2">Profesor</label>
                        <select v-model="selectedSubject.user_id" class="w-full bg-[#1a2332] border border-white/10 rounded-xl px-4 py-3 text-white text-sm outline-none">
                            <option v-for="t in teachers" :key="t.id" :value="t.id" class="bg-[#1a2332] text-white">{{ t.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button @click="closeModal" class="flex-1 py-3 text-xs font-black uppercase text-white/30 hover:text-white transition-all bg-white/5 rounded-xl border border-white/5">Cancelar</button>
                    <button @click="handleAction(activeModal)" class="flex-1 bg-[#406071] py-3 rounded-xl text-xs font-black uppercase text-white shadow-lg hover:shadow-[#406071]/20 transition-all border border-white/10">
                        Confirmar
                    </button>
                </div>
            </div>
        </BaseModal>
    </div>
</template>
