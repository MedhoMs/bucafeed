<script setup>
import { ref, onMounted, computed } from 'vue'
import { useTranslations } from '@/composables/useTranslations'
const { t } = useTranslations()
import { token } from '@/stores/auth'
import ManagementLayout from '@/components/layouts/ManagementLayout.vue'
import ManagementCard from '@/components/layouts/ManagementCard.vue'
import CenterTabs from '@/components/center/tabs/CenterTabs.vue'
import GroupsTab from '@/components/center/tabs/GroupsTab.vue'
import PeopleTab from '@/components/center/tabs/PeopleTab.vue'
import CyclesTab from '@/components/center/tabs/CyclesTab.vue'
import EventsTab from '@/components/center/tabs/EventsTab.vue'
import PublicationsTab from '@/components/center/tabs/PublicationsTab.vue'
import CenterManagerCore from '@/components/center/modals/CenterManagerCore.vue'

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
const headers = computed(() => ({
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token.value}`
}))

// Estado
const center = ref(null)
const groups = ref([])
const teachers = ref([])
const students = ref([])
const pendingStudents = ref([])
const pendingTeachers = ref([])
const admins = ref([])
const cycles = ref([])
const events = ref([])
const publications = ref([])
const loading = ref(true)
const activeTab = ref('overview')
const toast = ref({ show: false, msg: '', type: 'success' })

// UI State
const activeModal = ref(null)
const selectedGroup = ref(null)
const selectedEvent = ref(null)
const selectedPublication = ref(null)
const expandedGroup = ref(null)
const confirmingDelete = ref(null)

const showToast = ({ msg, type = 'success' }) => {
    // Intentar traducir si el mensaje parece una clave de i18n
    const translated = msg.split('.').reduce((obj, key) => obj?.[key], t.value) || msg;
    toast.value = { show: true, msg: translated, type }
    setTimeout(() => toast.value.show = false, 3000)
}

const openModal = (type, item = null) =>{
    if (type.includes('event')) {
        selectedEvent.value = item;
    } else if (type.includes('publication')) {
        selectedPublication.value = item;
    } else {
        selectedGroup.value = item;
    }
    activeModal.value = type; 
}

async function loadAll() {
    loading.value = true
    try {
        const fetchs = ['my-center', 'my-center/groups', 'my-center/teachers', 'my-center/students', 'my-center/students/pending', 'my-center/teachers/pending', 'my-center/admins', 'my-center/cycles', 'my-center/events', 'my-center/publications']
        const results = await Promise.all(fetchs.map(p => fetch(`${apiBase}/${p}`, { headers: headers.value }).then(r => r.json())));
        [center.value, groups.value, teachers.value, students.value, pendingStudents.value, pendingTeachers.value, admins.value, cycles.value, events.value, publications.value] = results;
    } catch (e) { showToast({ msg: t.value.manager?.messages?.serverError || 'Error de servidor', type: 'error' }) }
    loading.value = false
}

onMounted(loadAll)

async function verifyStudent(userId) {
    try {
        const res = await fetch(`${apiBase}/my-center/students/${userId}/verify`, {
            method: 'POST',
            headers: headers.value
        })
        const data = await res.json()
        if (res.ok) {
            showToast({ msg: t.value.manager?.messages?.studentVerified || 'Estudiante verificado correctamente' })
            await loadAll()
        } else {
            showToast({ msg: data.message || t.value.manager?.messages?.verifyError || 'Error al verificar', type: 'error' })
        }
    } catch (e) { showToast({ msg: t.value.manager?.messages?.serverError || 'Error de servidor', type: 'error' }) }
}

async function verifyTeacher(userId) {
    try {
        const res = await fetch(`${apiBase}/my-center/teachers/${userId}/verify`, {
            method: 'POST',
            headers: headers.value
        })
        const data = await res.json()
        if (res.ok) {
            showToast({ msg: t.value.manager?.messages?.teacherVerified || 'Profesor verificado correctamente' })
            await loadAll()
        } else {
            showToast({ msg: data.message || t.value.manager?.messages?.verifyError || 'Error al verificar', type: 'error' })
        }
    } catch (e) { showToast({ msg: t.value.manager?.messages?.serverError || 'Error de servidor', type: 'error' }) }
}

async function deleteItem(type, item, itemId = null) {
    const msgKey = {
        group: 'deleted',
        student: 'removed',
        subject: 'removed',
        event: 'eventDeleted',
        publication: 'publicationDeleted'
    }[type]
    const config = {
        group:   { url: `/groups/${item.id}`, clean: () => confirmingDelete.value = null },
        student: { url: `/groups/${item.id}/students/${itemId}` },
        subject: { url: `/groups/${item.id}/subjects/${itemId}` },
        event:   { url: `/events/${item.id}` },
        publication: { url: `/publications/${item.id}` }
    }[type]
    try {
        const res = await fetch(`${apiBase}/my-center${config.url}`, { method: 'DELETE', headers: headers.value })
        if (res.ok) {
            showToast({ msg: t.value.manager?.messages?.[msgKey] || msgKey })
            if (config.clean) config.clean()
            await loadAll()
        }
    } catch (e) { showToast({ msg: t.value.manager?.messages?.error || 'Error', type: 'error' }) }
}

const getTeacherName = (id) => {
    const foundTeacher = teachers.value.find(x => x.id === id)
    return foundTeacher ? `${foundTeacher.name} ${foundTeacher.last_name || ''}` : (t.value.manager?.messages?.noTeacher || 'Sin profesor')
}
</script>

<template>
    <ManagementLayout :loading="loading" :hasData="!!center" :toast="toast">
        <template v-if="center">
            <!-- Header -->
            <ManagementCard :hover="false" class="p-6 mb-8 text-white">
                <div class="flex items-center gap-5 mb-8">
                    <div class="w-16 h-16 rounded-2xl bg-[#406071]/50 border border-white/10 flex items-center justify-center text-3xl font-black shadow-lg">
                        {{ center.name?.charAt(0) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight">{{ center.name }}</h1>
                        <p class="text-white/40 text-sm font-bold uppercase tracking-wider">{{ center.location }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
                    <div v-for="stat in [
                        { label: t.manager?.stats?.groups || 'Grupos', value: center.groups_count },
                        { label: t.manager?.stats?.teachers || 'Profesores', value: center.teachers_count },
                        { label: t.manager?.stats?.students || 'Alumnos', value: center.students_count },
                        { label: t.manager?.stats?.publications || 'Publicaciones', value: center.publications_count },
                    ]" :key="stat.label" 
                         class="flex flex-col items-center border-b sm:border-b-0 sm:border-r border-white/5 last:border-0 pb-4 sm:pb-0">
                        <span class="text-xl sm:text-2xl font-black text-white/90">{{ stat.label }}</span>
                        <span class="text-[18px] text-white/30 uppercase tracking-[0.2em] font-black mt-1 sm:mt-2">{{ stat.value || 0 }}</span>
                    </div>
                </div>
            </ManagementCard>

            <CenterTabs v-model="activeTab" :center="center" />

            <!-- Dinamic Tab Content -->
            <GroupsTab 
                v-if="activeTab === 'overview'" 
                :groups="groups" 
                v-model:expandedGroup="expandedGroup"
                v-model:confirmingDelete="confirmingDelete"
                @openModal="openModal" 
                @deleteItem="deleteItem"
                @getTeacherName="getTeacherName"
            />

            <PeopleTab 
                v-else-if="activeTab === 'people'" 
                :admins="admins" 
                :teachers="teachers" 
                :students="students" 
                :pendingStudents="pendingStudents"
                :pendingTeachers="pendingTeachers"
                @openModal="openModal"
                @verifyStudent="verifyStudent"
                @verifyTeacher="verifyTeacher"
            />

            <CyclesTab 
                v-else-if="activeTab === 'cycles'" 
                :cycles="cycles" 
                :center="center"
                @openModal="openModal"
            />

            <EventsTab 
                v-else-if="activeTab === 'events'" 
                :events="events"
                @openModal="openModal"
                @deleteItem="deleteItem"
            />

            <PublicationsTab 
                v-else-if="activeTab === 'publications'" 
                :publications="publications"
                @openModal="openModal"
                @deleteItem="deleteItem"
            />
            
        </template>
        <CenterManagerCore 
            :activeModal="activeModal" 
            :group="selectedGroup" 
            :event="selectedEvent"
            :publication="selectedPublication"
            :teachers="teachers" 
            :students="students" 
            :cycles="cycles" 
            :center="center"
            :apiBase="apiBase" 
            :headers="headers" 
            @close="activeModal = null" 
            @refresh="loadAll" 
            @toast="showToast" 
        />
    </ManagementLayout>
</template>
