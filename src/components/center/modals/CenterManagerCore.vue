<script setup>
import { ref, computed, watch } from 'vue'
import BaseModal from '@/components/modals/BaseModal.vue'
import GenericForm from '@/components/common/forms/GenericForm.vue'
import { useTranslations } from '@/composables/useTranslations'
import { user as authUser } from '@/stores/auth'

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
    if (!val) return

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
            { id: 'description', type: 'textarea', label: t.value.manager.modals.event.descLabel, placeholder: t.value.manager.modals.event.descPlaceholder, required: true, full: true },
            { id: 'location', type: 'text', label: t.value.manager.modals.event.locationLabel, placeholder: t.value.manager.modals.event.locationPlaceholder, required: true, full: false },
            { id: 'date', type: 'date', label: t.value.manager.modals.event.dateLabel, required: true, full: false },
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
    },
    meeting: {
        title: t.value.meetings.createTitle,
        msg: t.value.meetings.success,
        url: '/meetings',
        fields: [
            { id: 'name', type: 'text', label: t.value.meetings.form.title, placeholder: t.value.meetings.form.titlePlaceholder, required: true },
            { id: 'teacher_name', type: 'text', label: t.value.meetings.form.teacher, placeholder: t.value.meetings.form.teacherPlaceholder, required: true },
            { id: 'schedule', type: 'time', label: t.value.meetings.form.schedule, placeholder: t.value.meetings.form.schedulePlaceholder, required: true },
            { id: 'description', type: 'textarea', label: t.value.meetings.form.description, placeholder: t.value.meetings.form.descriptionPlaceholder, required: false, full: true },
        ]
    },
    user: {
        title: t.value.manager.modals.user.title,
        msg: t.value.manager.modals.user.msg,
        url: '/users',
        fields: [
            { id: 'name', type: 'text', label: t.value.manager.modals.user.nameLabel, placeholder: 'Juan', required: true },
            { id: 'last_name', type: 'text', label: t.value.manager.modals.user.lastNameLabel, placeholder: 'Pérez', required: true },
            { id: 'dni', type: 'text', label: t.value.manager.modals.user.dniLabel, placeholder: '12345678A', required: true },
            { id: 'email', type: 'email', label: t.value.manager.modals.user.emailLabel, placeholder: 'juan@ejemplo.com', required: true },
            { id: 'password', type: 'password', label: t.value.manager.modals.user.passLabel, placeholder: '********', required: true },
            { id: 'role', type: 'select', label: t.value.manager.modals.user.roleLabel, options: [{id: 'Student', name: 'Alumno'}, {id: 'Teacher', name: 'Profesor'}], required: true }
        ]
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

                return name.includes(query) || area.includes(query) || tagsMatch
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

watch(searchQuery, (newVal) => {
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
        } else if (props.activeModal === 'meeting') {
            body = JSON.stringify({
                ...form.value,
                group_id: props.group?.id,
                educational_center_id: props.center?.id,
                teacher_id: authUser.value?.id
            })
        } else if (props.activeModal === 'user') {
            body = JSON.stringify({
                ...form.value,
                educational_center_id: props.center?.id,
                institution_name: props.center?.name
            })
        } else {
            const hasFiles = Object.values(form.value).some(v => v instanceof File);
            if (hasFiles) {
                body = new FormData();
                Object.keys(form.value).forEach(key => {
                    if (form.value[key] !== null) body.append(key, form.value[key]);
                });

                if (method === 'PUT') {
                    method = 'POST';
                    body.append('_method', 'PUT');
                }

                delete headers['Content-Type'];
            } else {
                body = JSON.stringify(form.value);
            }
        }

        const res = await fetch(`${props.apiBase}${current.value.url}`, {
            method: method,
            headers: headers,
            body: body
        })
        if (res.status === 401) {
            const auth = await import('@/stores/auth');
            auth.logout();
            window.location.href = '/login';
            return;
        }
        if (!res.ok) {
            let errorMsg = 'Error en validación'
            try {
                const data = await res.json()
                errorMsg = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Error en servidor')
            } catch (e) {
                console.error("Error parsing response:", e)
            }
            emit('toast', { msg: errorMsg, type: 'error' })
        } else {
            const data = await res.json()
            emit('toast', { msg: current.value.msg || 'Acción completada' })
            emit('refresh')
            emit('close')
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
        <BaseModal :show="!!activeModal" @close="$emit('close')" @confirm="handleAction" :title="current.title"
            :loading="isSubmitting">
            <!-- Custom UI for Enrollment -->
            <div v-if="activeModal?.includes('enroll')" class="space-y-6">
                <!-- Tabs for Users -->
                <div v-if="activeModal === 'enroll_users'"
                    class="flex p-1 bg-white/5 rounded-xl border border-white/10 shadow-inner">
                    <button v-for="r in ['Student', 'Teacher']" :key="r" @click="activeEnrollTab = r"
                        :class="['flex-1 py-2 text-[10px] font-black uppercase tracking-[0.2em] rounded-lg transition-all flex items-center justify-center gap-2', activeEnrollTab === r ? 'bg-secondary-normal text-white shadow-lg' : 'text-white/20 hover:text-white/40']">
                        <svg v-if="r === 'Student'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-3.5 h-3.5 fill-current">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-3.5 h-3.5 fill-current">
                            <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                        </svg>
                        {{ r === 'Student' ? t.manager.modals.enrollUsers.tabs.student :
                            t.manager.modals.enrollUsers.tabs.teacher }}
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-white/20 fill-current">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                    <input v-model="searchQuery"
                        :placeholder="activeModal === 'enroll_users' ? t.manager.modals.enrollUsers.searchPlaceholder : t.manager.modals.enrollCycles.searchPlaceholder"
                        class="w-full bg-white/5 border border-white/10 rounded-xl pl-11 pr-4 py-3 text-white text-sm focus:border-secondary-normal focus:ring-1 focus:ring-secondary-normal outline-none transition-all shadow-inner">
                    <div v-if="searchLoading" class="absolute right-4 top-1/2 -translate-y-1/2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 animate-spin text-secondary-normal fill-current">
                            <path d="M12 2v2a8 8 0 1 0 8 8h2c0-5.52-4.48-10-10-10Z"/>
                        </svg>
                    </div>
                </div>

                <!-- Results List -->
                <div
                    class="min-h-[200px] max-h-72 overflow-y-auto space-y-2 pr-2 custom-scrollbar border border-white/5 p-2 rounded-xl bg-black/10 shadow-inner">
                    <div v-if="!searchLoading && searchResults.length === 0"
                        class="flex flex-col items-center justify-center py-10 opacity-20">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-12 h-12 mb-2 fill-current">
                            <path d="M2 2L1.39 3.41l2.36 2.36C3.24 6.67 3 7.8 3 9c0 3.87 3.13 7 7 7 1.2 0 2.33-.24 3.23-.73l6.36 6.36L21 20.22 2 2zm8 12c-2.76 0-5-2.24-5-5 0-.76.17-1.47.47-2.11l6.64 6.64c-.64.3-1.35.47-2.11.47zM20.3 15.7l-3.3-3.3c.63-.9.99-2 .99-3.2 0-3.87-3.13-7-7-7-1.2 0-2.3.36-3.2.99l-3.3-3.3 1.4-1.4 15.8 15.8-1.39 1.41zM10.89 5c2.24 0 4.11 1.87 4.11 4.11 0 .86-.27 1.66-.73 2.3l-5.68-5.68c.64-.46 1.44-.73 2.3-.73z"/>
                        </svg>
                        <p class="text-[10px] font-black uppercase tracking-widest">{{ t.manager.labels.noResults }}</p>
                    </div>

                    <label v-for="item in searchResults" :key="item.id"
                        :class="['flex items-center gap-3 p-4 rounded-xl border transition-all cursor-pointer group glass-card', selectedIds.includes(item.id) ? 'bg-brand-net/10 border-brand-net/30 ring-1 ring-brand-net/20' : 'bg-white/5 border-white/5 hover:bg-white/10', item.alreadyLinked ? 'opacity-50 cursor-not-allowed border-brand-net/20' : '']">
                        <input type="checkbox" :value="item.id" :disabled="item.alreadyLinked"
                            :checked="selectedIds.includes(item.id) || item.alreadyLinked" @change="(e) => {
                                if (item.alreadyLinked) return
                                if (e.target.checked) selectedIds.push(item.id)
                                else selectedIds.splice(selectedIds.indexOf(item.id), 1)
                            }" class="w-4 h-4 accent-emerald-500 rounded">
                        <div class="flex flex-col flex-1">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs text-white/90 font-black uppercase tracking-tight group-hover:translate-x-1 transition-transform">
                                    {{ item.name || item.email }}
                                </span>
                                <span v-if="item.alreadyLinked"
                                    class="text-[9px] font-black text-brand-net bg-brand-net/10 px-2 py-0.5 rounded-full uppercase">{{
                                        t.manager.labels.alreadyLinked }}</span>
                            </div>
                            <span class="text-[9px] text-white/20 font-bold tracking-wider">{{ item.area || item.email
                                }}</span>
                            <!-- Mostrar tags si existen -->
                            <div v-if="item.tags && item.tags.length > 0" class="flex flex-wrap gap-1 mt-1">
                                <span v-for="t in item.tags.slice(0, 3)" :key="t.id"
                                    class="text-[8px] bg-white/5 text-white/40 px-1 rounded">{{ t.name }}</span>
                                <span v-if="item.tags.length > 3" class="text-[8px] text-white/20">+{{ item.tags.length
                                    - 3 }}</span>
                            </div>
                        </div>
                    </label>
                </div>

                <p class="text-[9px] text-white/20 font-black uppercase tracking-[0.2em] text-center italic">
                    {{ t.manager.labels.enrollHint }}
                </p>
            </div>

            <GenericForm v-else v-model="form" :fields="current.fields" :loading="isSubmitting" />
        </BaseModal>
    </div>
</template>

<style scoped></style>
