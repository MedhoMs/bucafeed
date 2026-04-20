<script setup>
import { ref, onMounted, computed } from 'vue'
import { token } from '@/stores/auth'
import ManagementLayout from '@/components/layouts/ManagementLayout.vue'
import ManagementCard from '@/components/layouts/ManagementCard.vue'
import CenterTabs from '@/components/center/tabs/CenterTabs.vue'
import GroupsTab from '@/components/center/tabs/GroupsTab.vue'
import PeopleTab from '@/components/center/tabs/PeopleTab.vue'
import CyclesTab from '@/components/center/tabs/CyclesTab.vue'
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
const admins = ref([])
const cycles = ref([])
const loading = ref(true)
const activeTab = ref('overview')
const toast = ref({ show: false, msg: '', type: 'success' })

// UI State
const activeModal = ref(null)
const selectedGroup = ref(null)
const expandedGroup = ref(null)
const confirmingDelete = ref(null)

const showToast = ({ msg, type = 'success' }) => {
    toast.value = { show: true, msg, type }
    setTimeout(() => toast.value.show = false, 3000)
}

const openModal = (type, group = null) =>{
    selectedGroup.value = group;
    activeModal.value = type; 
}

async function loadAll() {
    loading.value = true
    try {
        const fetchs = ['my-center', 'my-center/groups', 'my-center/teachers', 'my-center/students', 'my-center/admins', 'my-center/cycles']
        const results = await Promise.all(fetchs.map(p => fetch(`${apiBase}/${p}`, { headers: headers.value }).then(r => r.json())));
        [center.value, groups.value, teachers.value, students.value, admins.value, cycles.value] = results;
    } catch (e) { showToast({ msg: 'Error de servidor', type: 'error' }) }
    loading.value = false
}

onMounted(loadAll)

async function deleteItem(type, group, itemId = null) {
    const config = {
        group:   { url: `/groups/${group.id}`, msg: 'Eliminado', clean: () => confirmingDelete.value = null },
        student: { url: `/groups/${group.id}/students/${itemId}`, msg: 'Quitado' },
        subject: { url: `/groups/${group.id}/subjects/${itemId}`, msg: 'Quitado' }
    }[type]
    try {
        const res = await fetch(`${apiBase}/my-center${config.url}`, { method: 'DELETE', headers: headers.value })
        if (res.ok) {
            showToast({ msg: config.msg })
            if (config.clean) config.clean()
            await loadAll()
        }
    } catch (e) { showToast({ msg: 'Error', type: 'error' }) }
}

const getTeacherName = (id) => {
    const t = teachers.value.find(x => x.id === id)
    return t ? `${t.name} ${t.last_name || ''}` : 'Sin profesor'
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
                <div class="grid grid-cols-3 gap-6">
                    <div v-for="(v, l, i) in {[center.students_count]:'Alumnos', [center.teachers_count]:'Profesores', [center.groups_count]:'Grupos'}" :key="l" 
                         class="flex flex-col items-center border-r border-white/5 last:border-0">
                        <span class="text-3xl font-black text-white/90">{{ v || 0 }}</span>
                        <span class="text-[10px] text-white/30 uppercase tracking-[0.2em] font-black mt-2">{{ l }}</span>
                    </div>
                </div>
            </ManagementCard>

            <CenterTabs v-model="activeTab" />

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
            />

            <CyclesTab 
                v-else-if="activeTab === 'cycles'" 
                :cycles="cycles" 
            />
            
        </template>
        <CenterManagerCore :activeModal="activeModal" :group="selectedGroup" :teachers="teachers" :students="students" :cycles="cycles" :apiBase="apiBase" :headers="headers" @close="activeModal = null" @refresh="loadAll" @toast="showToast" />
    </ManagementLayout>
</template>
