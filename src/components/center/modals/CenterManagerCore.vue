<script setup>
import { ref, computed, watch } from 'vue'
import BaseModal from '@/components/modals/BaseModal.vue'

const props = defineProps({
    activeModal: { type: String, default: null },
    group: { type: Object, default: null },
    teachers: { type: Array, default: () => [] },
    students: { type: Array, default: () => [] },
    cycles: { type: Array, default: () => [] },
    center: { type: Object, default: () => ({ type: 'HE' }) },
    apiBase: { type: String, required: true },
    headers: { type: Object, required: true }
})

const emit = defineEmits(['close', 'refresh', 'toast'])


const labels = computed(() => {
    const isSchool = props.center?.type === 'PE'
    const isUni = props.center?.type === 'HE' && (props.center?.category?.toLowerCase() === 'university' || props.center?.category?.toLowerCase() === 'universidad')
    
    if (isSchool) return { cycle: 'Curso / Etapa', subject: 'Asignatura / Área' }
    if (isUni) return { cycle: 'Grado / Carrera', subject: 'Asignatura / Crédito' }
    return { cycle: 'Ciclo Formativo', subject: 'Asignatura / Módulo' }
})

// Estado único para todos los formularios
const form = ref({
    name: '',
    cycle_id: null,
    user_id: null,
    student_ids: [],
    tag_id: null
})

// Reiniciar form al cambiar de modal
watch(() => props.activeModal, () => {
    form.value = { name: '', cycle_id: null, user_id: null, student_ids: [], tag_id: null }
})

const MODAL_MAP = computed(() => ({
    create: {
        title: 'Crear Nuevo Grupo',
        msg: 'Grupo creado',
        url: '/my-center/groups',
        fields: [
            { id: 'name', type: 'text', label: 'Nombre Identificativo', placeholder: 'Ej: 1º DAW A o 2º Primaria B' },
            { id: 'cycle_id', type: 'select', label: labels.value.cycle, options: props.cycles }
        ]
    },
    tutor: {
        title: 'Asignar Tutor',
        msg: 'Tutor asignado',
        url: `/my-center/groups/${props.group?.id}/tutor`,
        fields: [
            { id: 'info', type: 'info', label: 'Grupo Seleccionado', value: props.group?.name },
            { id: 'user_id', type: 'select', label: 'Personal Docente', options: props.teachers.map(t => ({ id: t.id, name: `${t.name} ${t.last_name}` })) }
        ]
    },
    students: {
        title: 'Gestión de Alumnos',
        msg: 'Alumnos actualizados',
        url: `/my-center/groups/${props.group?.id}/students`,
        fields: [
            { id: 'student_ids', type: 'checklist', label: 'Censo de Alumnos', options: props.students }
        ]
    },
    subject: {
        title: 'Asignar Asignatura',
        msg: 'Docencia asignada',
        url: `/my-center/groups/${props.group?.id}/subjects`,
        fields: [
            { id: 'tag_id', type: 'select-grouped', label: labels.value.subject, groups: props.cycles },
            { id: 'user_id', type: 'select', label: 'Personal Docente', options: props.teachers.map(t => ({ id: t.id, name: `${t.name} ${t.last_name}` })) }
        ]
    }
}))

const current = computed(() => MODAL_MAP.value[props.activeModal] || {})

async function handleAction() {
    if (!current.value.url) return
    try {
        const res = await fetch(`${props.apiBase}${current.value.url}`, {
            method: 'POST',
            headers: props.headers,
            body: JSON.stringify(form.value)
        })
        if (res.ok) {
            emit('toast', { msg: current.value.msg })
            emit('refresh'); emit('close')
        }
    } catch (e) { emit('toast', { msg: 'Error de red', type: 'error' }) }
}
</script>

<template>
    <div v-if="activeModal">
        <BaseModal :show="!!activeModal" @close="$emit('close')" @confirm="handleAction" :title="current.title">
            <div class="space-y-6 pt-2 animate-in fade-in slide-in-from-bottom-3 duration-500">
                
                <div v-for="field in current.fields" :key="field.id" class="space-y-2">
                    <!-- LABEL COMÚN -->
                    <label v-if="field.type !== 'info'" class="block text-[10px] font-black uppercase text-white/20 tracking-[0.2em] ml-1">
                        {{ field.label }}
                        <span v-if="field.type === 'checklist'" class="text-[#406071] ml-2">{{ form.student_ids.length }} seleccionados</span>
                    </label>

                    <!-- INPUT TEXT -->
                    <input v-if="field.type === 'text'" v-model="form[field.id]" type="text" :placeholder="field.placeholder" 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:border-[#406071] outline-none transition-all shadow-inner">

                    <!-- SELECT SIMPLE -->
                    <select v-if="field.type === 'select'" v-model="form[field.id]" 
                        class="custom-select w-full bg-[#1a2332] text-white border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-[#406071] outline-none">
                        <option v-for="opt in field.options" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                    </select>

                    <!-- SELECT AGRUPADO (MATERIAS) -->
                    <select v-if="field.type === 'select-grouped'" v-model="form[field.id]" 
                        class="custom-select w-full bg-[#1a2332] text-white border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-[#406071] outline-none font-bold">
                        <optgroup v-for="g in field.groups" :key="g.id" :label="g.name" class="bg-[#0b1019] text-[#406071] uppercase text-[10px]">
                            <option v-for="tag in g.tags" :key="tag.id" :value="tag.id" class="text-white normal-case font-normal">{{ tag.name }}</option>
                        </optgroup>
                    </select>

                    <!-- INFO BOX -->
                    <div v-if="field.type === 'info'" class="p-4 bg-[#406071]/10 rounded-xl border border-[#406071]/20 group">
                        <p class="text-[10px] text-white/20 font-black uppercase tracking-widest mb-1 group-hover:text-[#406071] transition-colors">{{ field.label }}</p>
                        <p class="text-sm font-black text-white uppercase">{{ field.value }}</p>
                    </div>

                    <!-- CHECKLIST (ALUMNOS) -->
                    <div v-if="field.type === 'checklist'" class="max-h-72 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                        <label v-for="s in field.options" :key="s.id" 
                            :class="['flex items-center gap-3 p-4 rounded-xl border transition-all cursor-pointer group', form.student_ids.includes(s.id) ? 'bg-[#406071]/20 border-[#406071]/40' : 'bg-white/5 border-white/5 hover:bg-white/10']">
                            <input type="checkbox" :value="s.id" v-model="form.student_ids" class="w-4 h-4 accent-[#406071] rounded">
                            <div class="flex flex-col">
                                <span class="text-xs text-white/90 font-black uppercase tracking-tight group-hover:translate-x-1 transition-transform">{{ s.name }}</span>
                                <span class="text-[9px] text-white/20 font-bold tracking-wider">{{ s.email }}</span>
                            </div>
                        </label>
                    </div>
                </div>

            </div>
        </BaseModal>
    </div>
</template>

<style scoped>
.custom-select { color-scheme: dark; }
.custom-select option { background-color: #1a2332; color: white; padding: 12px; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #406071; border-radius: 10px; }
</style>
