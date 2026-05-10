<script setup>
import { ref, computed, watch } from 'vue'
import BaseModal from '@/components/modals/BaseModal.vue'
import GenericForm from '@/components/common/forms/GenericForm.vue'
import { useTranslations } from '@/composables/useTranslations'

const { t } = useTranslations()

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
    
    if (isSchool) return { cycle: t.value.manager.labels.schoolCycle, subject: t.value.manager.labels.schoolSubject }
    if (isUni) return { cycle: t.value.manager.labels.uniCycle, subject: t.value.manager.labels.uniSubject }
    return { cycle: t.value.manager.labels.cycle, subject: t.value.manager.labels.subject }
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
        title: t.value.manager.modals.create.title,
        msg: t.value.manager.modals.create.msg,
        url: '/my-center/groups',
        fields: [
            { id: 'name', type: 'text', label: t.value.manager.modals.create.nameLabel, placeholder: t.value.manager.modals.create.namePlaceholder, required: true },
            { id: 'cycle_id', type: 'select', label: labels.value.cycle, options: props.cycles, required: true }
        ]
    },
    tutor: {
        title: t.value.manager.modals.tutor.title,
        msg: t.value.manager.modals.tutor.msg,
        url: `/my-center/groups/${props.group?.id}/tutor`,
        method: 'PUT',
        fields: [
            { id: 'info', type: 'info', label: t.value.manager.modals.tutor.groupInfo, value: props.group?.name },
            { id: 'user_id', type: 'select', label: t.value.manager.modals.tutor.teacherLabel, options: props.teachers.map(t => ({ id: t.id, name: `${t.name} ${t.last_name}` })) }
        ]
    },
    students: {
        title: t.value.manager.modals.students.title,
        msg: t.value.manager.modals.students.msg,
        url: `/my-center/groups/${props.group?.id}/students`,
        fields: [
            { id: 'student_ids', type: 'checklist', label: t.value.manager.modals.students.censusLabel, options: props.students }
        ]
    },
    subject: {
        title: t.value.manager.modals.subject.title,
        msg: t.value.manager.modals.subject.msg,
        url: `/my-center/groups/${props.group?.id}/subjects`,
        fields: [
            { id: 'tag_id', type: 'select-grouped', label: labels.value.subject, groups: props.cycles },
            { id: 'user_id', type: 'select', label: t.value.manager.modals.subject.teacherLabel, options: props.teachers.map(t => ({ id: t.id, name: `${t.name} ${t.last_name}` })) }
        ]
    },
    event: {
        title: t.value.manager.modals.event.title,
        msg: t.value.manager.modals.event.msg,
        url: '/my-center/events',
        fields: [
            { id: 'title', type: 'text', label: t.value.manager.modals.event.titleLabel, placeholder: t.value.manager.modals.event.titlePlaceholder, required: true },
            { id: 'description', type: 'textarea', label: t.value.manager.modals.event.descLabel, placeholder: t.value.manager.modals.event.descPlaceholder, required: true , full: true},
            { id: 'location', type: 'text', label: t.value.manager.modals.event.locationLabel, placeholder: t.value.manager.modals.event.locationPlaceholder, required: true, full: false},
            { id: 'date', type: 'date', label: t.value.manager.modals.event.dateLabel, required: true , full: false},
            { id: 'start_time', type: 'time', label: t.value.manager.modals.event.startLabel, required: true, full: false },
            { id: 'end_time', type: 'time', label: t.value.manager.modals.event.endLabel, required: true, full: false },
            { id: 'image', type: 'file', label: t.value.manager.modals.event.imageLabel, aspect: 'video' }
        ]
    },
    edit_event: {
        title: t.value.manager.modals.editEvent.title,
        msg: t.value.manager.modals.editEvent.msg,
        url: `/my-center/events/${props.event?.id}`,
        method: 'PUT',
        fields: [
            { id: 'title', type: 'text', label: t.value.manager.modals.event.titleLabel, placeholder: t.value.manager.modals.event.titlePlaceholder, required: true },
            { id: 'description', type: 'textarea', label: t.value.manager.modals.event.descLabel, placeholder: t.value.manager.modals.event.descPlaceholder, required: true },
            { id: 'date', type: 'date', label: t.value.manager.modals.event.dateLabel, required: true },
            { id: 'start_time', type: 'time', label: t.value.manager.modals.event.startLabel, required: true, full: false },
            { id: 'end_time', type: 'time', label: t.value.manager.modals.event.endLabel, required: true, full: false },
            { id: 'location', type: 'text', label: t.value.manager.modals.event.locationLabel, placeholder: t.value.manager.modals.event.locationPlaceholder, required: true },
            { id: 'image', type: 'file', label: t.value.manager.modals.editEvent.imageLabel, aspect: 'video' }
        ]
    },
    enroll_users: {
        title: t.value.manager.modals.enrollUsers.title,
        msg: t.value.manager.modals.enrollUsers.msg,
        url: '/my-center/enroll-users'
    },
    enroll_cycles: {
        title: t.value.manager.modals.enrollCycles.title,
        msg: t.value.manager.modals.enrollCycles.msg,
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
                <div v-if="activeModal === 'enroll_users'" class="flex p-1 bg-white/5 rounded-xl border border-white/10 shadow-inner">
                    <button v-for="r in ['Student', 'Teacher']" :key="r" 
                        @click="activeEnrollTab = r"
                        :class="['flex-1 py-2 text-[10px] font-black uppercase tracking-[0.2em] rounded-lg transition-all flex items-center justify-center gap-2', activeEnrollTab === r ? 'bg-secondary-normal text-white shadow-lg' : 'text-white/20 hover:text-white/40']">
                        <span class="material-symbols-outlined !text-sm">{{ r === 'Student' ? 'person' : 'school' }}</span>
                        {{ r === 'Student' ? t.manager.modals.enrollUsers.tabs.student : t.manager.modals.enrollUsers.tabs.teacher }}
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-white/20">search</span>
                    <input v-model="searchQuery" 
                        :placeholder="activeModal === 'enroll_users' ? t.manager.modals.enrollUsers.searchPlaceholder : t.manager.modals.enrollCycles.searchPlaceholder"
                        class="w-full bg-white/5 border border-white/10 rounded-xl pl-11 pr-4 py-3 text-white text-sm focus:border-secondary-normal focus:ring-1 focus:ring-secondary-normal outline-none transition-all shadow-inner"
                    >
                    <div v-if="searchLoading" class="absolute right-4 top-1/2 -translate-y-1/2">
                        <span class="material-symbols-outlined animate-spin text-secondary-normal">progress_activity</span>
                    </div>
                </div>

                <!-- Results List -->
                <div class="min-h-[200px] max-h-72 overflow-y-auto space-y-2 pr-2 custom-scrollbar border border-white/5 p-2 rounded-xl bg-black/10 shadow-inner">
                    <div v-if="!searchLoading && searchResults.length === 0" class="flex flex-col items-center justify-center py-10 opacity-20">
                        <span class="material-symbols-outlined !text-5xl mb-2">search_off</span>
                        <p class="text-[10px] font-black uppercase tracking-widest">{{ t.manager.labels.noResults }}</p>
                    </div>
                    
                    <label v-for="item in searchResults" :key="item.id" 
                        :class="['flex items-center gap-3 p-4 rounded-xl border transition-all cursor-pointer group glass-card', selectedIds.includes(item.id) ? 'bg-brand-net/10 border-brand-net/30 ring-1 ring-brand-net/20' : 'bg-white/5 border-white/5 hover:bg-white/10', item.alreadyLinked ? 'opacity-50 cursor-not-allowed border-brand-net/20' : '']">
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
                                <span v-if="item.alreadyLinked" class="text-[9px] font-black text-brand-net bg-brand-net/10 px-2 py-0.5 rounded-full uppercase">{{ t.manager.labels.alreadyLinked }}</span>
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
                    {{ t.manager.labels.enrollHint }}
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
