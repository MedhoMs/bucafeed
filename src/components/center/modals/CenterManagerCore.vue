<script setup>
import { ref, computed, watch } from 'vue'
import BaseModal from '@/components/modals/BaseModal.vue'
import GenericForm from '@/components/common/forms/GenericForm.vue'

const props = defineProps({
    activeModal: { type: String, default: null },
    group: { type: Object, default: null },
    event: { type: Object, default: null }, // Nuevo prop para editar eventos
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
const form = ref({})

// Reiniciar form al cambiar de modal
watch(() => props.activeModal, (val) => {
    if(!val) return
    
    // Valores por defecto
    const defaults = { 
        name: '', cycle_id: null, user_id: null, student_ids: [], tag_id: null,
        title: '', description: '', location: '', date: '', start_time: '', end_time: '', target_role: null, image: null
    }

    // Si estamos editando un evento, precargar sus datos
    if (val === 'edit_event' && props.event) {
        form.value = { 
            ...defaults, 
            ...props.event,
            image: null // No cargamos la URL de la imagen en el campo de archivo
        }
    } else {
        form.value = defaults
    }
})

const MODAL_MAP = computed(() => ({
    create: {
        title: 'Crear Nuevo Grupo',
        msg: 'Grupo creado',
        url: '/my-center/groups',
        fields: [
            { id: 'name', type: 'text', label: 'Nombre Identificativo', placeholder: 'Ej: 1º DAW A o 2º Primaria B', required: true },
            { id: 'cycle_id', type: 'select', label: labels.value.cycle, options: props.cycles, required: true }
        ]
    },
    tutor: {
        title: 'Asignar Tutor',
        msg: 'Tutor asignado',
        url: `/my-center/groups/${props.group?.id}/tutor`,
        method: 'PUT',
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
    },
    event: {
        title: 'Crear Nuevo Evento',
        msg: 'Evento publicado con éxito',
        url: '/my-center/events',
        fields: [
            { id: 'title', type: 'text', label: 'Título del Evento', placeholder: 'Ej: Jornada Deportiva', required: true },
            { id: 'description', type: 'textarea', label: 'Descripción', placeholder: '¿De qué trata el evento?', required: true , full: true},
            { id: 'location', type: 'text', label: 'Ubicación específica', placeholder: 'Ej: Pabellón Sur', full: false},
            { id: 'date', type: 'date', label: 'Fecha', required: true , full: false},
            { id: 'start_time', type: 'time', label: 'Hora Inicio', required: true, full: false },
            { id: 'end_time', type: 'time', label: 'Hora Fin', required: true, full: false },
            { id: 'image', type: 'file', label: 'Imagen de portada', aspect: 'video' }
        ]
    },
    edit_event: {
        title: 'Editar Evento',
        msg: 'Evento actualizado correctamente',
        url: `/my-center/events/${props.event?.id}`,
        method: 'PUT',
        fields: [
            { id: 'title', type: 'text', label: 'Título del Evento', placeholder: 'Ej: Jornada Deportiva', required: true },
            { id: 'description', type: 'textarea', label: 'Descripción', placeholder: '¿De qué trata el evento?', required: true },
            { id: 'date', type: 'date', label: 'Fecha', required: true },
            { id: 'start_time', type: 'time', label: 'Hora Inicio', required: true, full: false },
            { id: 'end_time', type: 'time', label: 'Hora Fin', required: true, full: false },
            { id: 'location', type: 'text', label: 'Ubicación específica', placeholder: 'Ej: Pabellón Sur' },
            { id: 'image', type: 'file', label: 'Nueva imagen (opcional)', aspect: 'video' }
        ]
    },
    enroll_users: {
        title: 'Matricular Usuarios',
        msg: 'Usuarios matriculados con éxito',
        url: '/my-center/enroll-users'
    },
    enroll_cycles: {
        title: 'Vincular Ciclos Formativos',
        msg: 'Ciclos vinculados con éxito',
        url: '/my-center/enroll-cycles'
    }
}))

const current = computed(() => MODAL_MAP.value[props.activeModal] || {})
const isSubmitting = ref(false)
const searchQuery = ref('')
const searchResults = ref([])
const searchLoading = ref(false)
const selectedIds = ref([])
const activeEnrollTab = ref('Student') // Para usuarios

async function performSearch() {
    if (!props.activeModal?.includes('enroll')) return
    searchLoading.value = true
    try {
        if (props.activeModal === 'enroll_users') {
            const res = await fetch(`${props.apiBase}/my-center/search-users?q=${searchQuery.value}&role=${activeEnrollTab.value}`, { headers: props.headers })
            searchResults.value = await res.json()
        } else if (props.activeModal === 'enroll_cycles') {
            const baseUrl = props.apiBase.endsWith('/') ? props.apiBase.slice(0, -1) : props.apiBase
            const res = await fetch(`${baseUrl}/all-cycles`, { headers: props.headers })
            const data = await res.json()
            const all = Array.isArray(data) ? data : (data.data || [])
            
            console.log("Cycles fetched:", all.length, "Data sample:", all[0])
            
            const currentIds = (props.cycles || []).map(c => c.id)
            const normalize = (str) => {
                if (!str) return ''
                return str.toString().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim()
            }
            const query = normalize(searchQuery.value)
            
            searchResults.value = all.filter(c => {
                if (!query) return true
                const name = normalize(c.name)
                const area = normalize(c.area)
                const tags = (c.tags || [])
                const tagsMatch = tags.some(t => normalize(t.name).includes(query))
                
                const matched = name.includes(query) || area.includes(query) || tagsMatch
                if (matched) console.log("Match found:", c.name, "Tags:", tags.map(t => t.name))
                return matched
            }).map(c => ({
                ...c,
                alreadyLinked: currentIds.includes(c.id)
            }))
        }
    } catch (e) { 
        console.error("Search error:", e) 
        searchResults.value = []
    } finally {
        searchLoading.value = false
    }
}

// Observar cambios para buscar (Simplificado y Robusto)
watch(searchQuery, (newVal) => {
    console.log("Input changed:", newVal)
    if (props.activeModal?.includes('enroll')) {
        performSearch()
    }
})

watch(activeEnrollTab, () => {
    if (props.activeModal === 'enroll_users') {
        performSearch()
    }
})

watch(() => props.activeModal, (val) => {
    console.log("Modal opened:", val)
    if (val?.includes('enroll')) {
        searchQuery.value = ''
        selectedIds.value = []
        performSearch()
    }
})

async function handleAction() {
    if (!current.value.url || isSubmitting.value) return
    isSubmitting.value = true
    try {
        let body;
        let method = current.value.method || 'POST';
        let headers = { ...props.headers };

        if (props.activeModal?.includes('enroll')) {
            body = JSON.stringify({ ids: selectedIds.value, type: activeEnrollTab.value })
        } else {
            // Detectar si hay archivos para usar FormData
            const hasFiles = Object.values(form.value).some(v => v instanceof File);
            if (hasFiles) {
                body = new FormData();
                Object.keys(form.value).forEach(key => {
                    if (form.value[key] !== null) body.append(key, form.value[key]);
                });
                
                // Laravel fix for PUT with FormData
                if (method === 'PUT') {
                    method = 'POST';
                    body.append('_method', 'PUT');
                }
                
                delete headers['Content-Type']; // Dejar que el navegador ponga el boundary
            } else {
                body = JSON.stringify(form.value);
            }
        }

        const res = await fetch(`${props.apiBase}${current.value.url}`, {
            method: method,
            headers: headers,
            body: body
        })
        if (res.ok) {
            emit('toast', { msg: current.value.msg })
            emit('refresh'); emit('close')
        } else {
            if (res.status === 401) {
                const auth = await import('@/stores/auth');
                auth.logout();
                window.location.href = '/login';
                return;
            }
            const data = await res.json()
            emit('toast', { msg: data.message || 'Error en validación', type: 'error' })
        }
    } catch (e) { 
        emit('toast', { msg: 'Error de red', type: 'error' }) 
    } finally {
        isSubmitting.value = false
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
            :loading="isSubmitting"
        >
            <!-- Custom UI for Enrollment -->
            <div v-if="activeModal?.includes('enroll')" class="space-y-6">
                <!-- Tabs for Users -->
                <div v-if="activeModal === 'enroll_users'" class="flex p-1 bg-white/5 rounded-xl border border-white/10">
                    <button v-for="r in ['Student', 'Teacher']" :key="r" 
                        @click="activeEnrollTab = r"
                        :class="['flex-1 py-2 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all', activeEnrollTab === r ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-white/20 hover:text-white/40']">
                        {{ r === 'Student' ? 'Alumnos' : 'Profesores' }}
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="relative">
                    <input v-model="searchQuery" 
                        :placeholder="activeModal === 'enroll_users' ? 'Buscar por nombre o email...' : 'Buscar ciclo formativo...'"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:border-[#406071] outline-none transition-all shadow-inner"
                    >
                    <div v-if="searchLoading" class="absolute right-4 top-1/2 -translate-y-1/2">
                        <div class="w-4 h-4 border-2 border-[#406071]/30 border-t-[#406071] rounded-full animate-spin"></div>
                    </div>
                </div>

                <!-- Results List -->
                <div class="min-h-[200px] max-h-72 overflow-y-auto space-y-2 pr-2 custom-scrollbar border border-white/5 p-2 rounded-xl bg-black/10">
                    <p v-if="!searchLoading && searchResults.length === 0" class="text-center py-8 text-white/20 text-[10px] font-black uppercase tracking-widest">
                        No se encontraron resultados
                    </p>
                    
                    <label v-for="item in searchResults" :key="item.id" 
                        :class="['flex items-center gap-3 p-4 rounded-xl border transition-all cursor-pointer group', selectedIds.includes(item.id) ? 'bg-emerald-500/10 border-emerald-500/20' : 'bg-white/5 border-white/5 hover:bg-white/10', item.alreadyLinked ? 'opacity-50 cursor-not-allowed border-emerald-500/20' : '']">
                        <input type="checkbox" :value="item.id" 
                            :disabled="item.alreadyLinked"
                            :checked="selectedIds.includes(item.id) || item.alreadyLinked"
                            @change="(e) => {
                                if(item.alreadyLinked) return
                                if(e.target.checked) selectedIds.push(item.id)
                                else selectedIds.splice(selectedIds.indexOf(item.id), 1)
                            }"
                            class="w-4 h-4 accent-emerald-500 rounded">
                        <div class="flex flex-col flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-white/90 font-black uppercase tracking-tight group-hover:translate-x-1 transition-transform">
                                    {{ item.name || item.email }}
                                </span>
                                <span v-if="item.alreadyLinked" class="text-[9px] font-black text-emerald-500 bg-emerald-500/10 px-2 py-0.5 rounded-full uppercase">Ya vinculado</span>
                            </div>
                            <span class="text-[9px] text-white/20 font-bold tracking-wider">{{ item.area || item.email }}</span>
                            <!-- Mostrar tags si existen -->
                            <div v-if="item.tags && item.tags.length > 0" class="flex flex-wrap gap-1 mt-1">
                                <span v-for="t in item.tags.slice(0, 3)" :key="t.id" class="text-[8px] bg-white/5 text-white/40 px-1 rounded">{{ t.name }}</span>
                                <span v-if="item.tags.length > 3" class="text-[8px] text-white/20">+{{ item.tags.length - 3 }}</span>
                            </div>
                        </div>
                    </label>
                </div>
                
                <p class="text-[9px] text-white/20 font-black uppercase tracking-[0.2em] text-center italic">
                    Selecciona los elementos que deseas matricular en tu centro
                </p>
            </div>

            <GenericForm 
                v-else
                v-model="form" 
                :fields="current.fields" 
                :loading="isSubmitting"
            />
        </BaseModal>
    </div>
</template>

<style scoped>
</style>
