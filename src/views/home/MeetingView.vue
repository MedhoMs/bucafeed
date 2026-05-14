<script setup>
import { ref, computed, watch, onMounted, reactive } from 'vue';
import { useRoute } from 'vue-router';
import NavBar from '../../components/NavBar/NavBar.vue';
import SearchBar from '../../components/SearchBar.vue';
import Meeting from '../../components/Meeting.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import PrimaryButton from '@/components/common/PrimaryButton.vue';
import BaseModal from '@/components/modals/BaseModal.vue';
import GenericForm from '@/components/common/forms/GenericForm.vue';
import Pagination from '../../components/common/Pagination.vue';
import { useTranslations } from '../../composables/useTranslations'
import { user } from '../../stores/auth';
import { useApi } from '../../composables/useApi';

const { t } = useTranslations()
const { get, post: apiPost, del: apiDelete, loading: apiLoading } = useApi();

const meetings = ref([]);
const centers = ref([]);
const groups = ref([]);
const userGroups = ref([]);

const pagination = reactive({
    currentPage: 1,
    lastPage: 1
});

const fetchCenters = async () => {
    const role = user.value?.role?.toLowerCase();
    if (role !== 'admin' && role !== 'administrador') return;
    try {
        const result = await get('educational-centers');
        centers.value = Array.isArray(result) ? result : (result.data || []);
    } catch (error) {
        console.error('Error fetching centers:', error);
    }
};

const fetchGroups = async () => {
    if (!user.value) return;
    const role = user.value.role?.toLowerCase();
    if (role !== 'admin' && role !== 'ei' && role !== 'teacher' && role !== 'profesor' && role !== 'administrador') return;
    try {
        const data = await get('my-center/groups');
        groups.value = data;
    } catch (error) {
        console.error('Error fetching groups:', error);
    }
};

// Get the groups the current user belongs to (for students)
const fetchUserGroups = async () => {
    if (!user.value) return;
    const role = user.value.role?.toLowerCase();
    if (role !== 'student' && role !== 'alumno' && role !== 'estudiante') return;
    try {
        const data = await get(`users/${user.value.id}`);
        if (data && data.groups_as_student) {
            userGroups.value = data.groups_as_student;
        }
    } catch (error) {
        console.error('Error fetching user groups:', error);
    }
};

const fetchMeetings = async (page = 1) => {
    try {
        let endpoint = `meetings?page=${page}`;

        const result = await get(endpoint);
        
        const dataArray = result.data || result;
        
        if (Array.isArray(dataArray)) {
            meetings.value = dataArray.map(m => ({
                id: m.id,
                name: m.name,
                teacher: m.teacher ? m.teacher.name : (m.teacher_name || t.value.meetings.unknown),
                teacher_id: m.teacher_id,
                group: m.group ? m.group.name : (m.educational_center ? m.educational_center.name : t.value.meetings.various),
                group_id: m.group_id,
                educational_center_id: m.educational_center_id,
                schedule: m.schedule,
                description: m.description
            }));
            
            pagination.currentPage = result.current_page || 1;
            pagination.lastPage = result.last_page || 1;
        }
    } catch (error) {
        console.error('Error fetching meetings:', error);
    }
};

const handlePageChange = (page) => {
    fetchMeetings(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
    fetchMeetings();
    fetchCenters();
    fetchGroups();
    fetchUserGroups();
});

// 1. Charlas disponibles para este usuario según su grupo
const availableMeetings = computed(() => {
    if (!user.value) return [];
    const role = user.value.role?.toLowerCase();
    if (role === 'admin' || role === 'administrador') return meetings.value;
    
    // Teachers/EI see meetings from their center
    if (role === 'teacher' || role === 'profesor' || role === 'ei') {
        return meetings.value.filter(m => 
            m.educational_center_id === user.value.educational_center_id
        );
    }
    
    // Students see meetings from their groups
    const userGroupIds = userGroups.value.map(g => g.id);
    return meetings.value.filter(m => {
        if (!m.group_id) {
            // General meetings (no group) - show to students of same institution
            return m.educational_center_id === user.value.educational_center_id;
        }
        return userGroupIds.includes(m.group_id);
    });
});

// 2. Estado para los resultados filtrados por el buscador
const filteredMeetings = ref([]);

// Sincronizar resultados cuando cambien las disponibles
watch(availableMeetings, (newList) => {
    filteredMeetings.value = [...newList];
}, { immediate: true });

// --- Lógica del Modal de Creación ---
const showModal = ref(false);
const showDeleteModal = ref(false);
const meetingToDelete = ref(null);
const toast = ref({ show: false, msg: '', type: 'success' });
const showToast = (msg, type = 'success') => {
    toast.value = { show: true, msg, type };
    setTimeout(() => { toast.value.show = false; }, 3000);
};

const canCreateMeeting = computed(() => {
    if (!user.value) return false;
    const role = user.value.role?.toLowerCase();
    const allowedRoles = ['teacher', 'ei', 'profesor'];
    return allowedRoles.includes(role);
});

const newMeeting = ref({
    name: '',
    teacher_name: '', // Nombre libre (editable)
    educational_center_id: null,
    group_id: null,
    schedule: '',
    description: ''
});

const meetingFields = computed(() => {
    const baseFields = [
        { id: 'name', type: 'text', label: t.value.meetings.form.title, placeholder: t.value.meetings.form.titlePlaceholder, required: true },
        { id: 'teacher_name', type: 'text', label: t.value.meetings.form.teacher, placeholder: t.value.meetings.form.teacherPlaceholder, required: true, full: false },
        { id: 'schedule', type: 'text', label: t.value.meetings.form.schedule, placeholder: t.value.meetings.form.schedulePlaceholder, required: true, full: false },
    ];

    if (user.value?.role?.toLowerCase().includes('admin')) {
        baseFields.push({
            id: 'educational_center_id',
            type: 'select',
            label: t.value.meetings.form.center,
            placeholder: t.value.meetings.form.centerPlaceholder,
            required: true,
            options: centers.value.map(c => ({ id: c.id, name: c.name }))
        });
    } else {
        baseFields.push({
            id: 'info_center',
            type: 'info',
            label: t.value.meetings.form.center,
            value: user.value?.institution_name || t.value.meetings.various
        });
    }

    // Group selector
    if (groups.value.length > 0) {
        baseFields.push({
            id: 'group_id',
            type: 'select',
            label: 'Grupo (opcional)',
            placeholder: 'Seleccionar grupo...',
            required: false,
            options: [{ id: '', name: '-- Todos los grupos --' }, ...groups.value.map(g => ({ id: g.id, name: g.name }))]
        });
    }

    baseFields.push({ id: 'description', type: 'textarea', label: t.value.meetings.form.description, placeholder: t.value.meetings.form.descriptionPlaceholder, required: false });

    return baseFields;
});

const openModal = () => {
    // Inicializar con valores "obvios"
    newMeeting.value = {
        name: '',
        teacher_name: user.value?.name || '',
        educational_center_id: user.value?.educational_center_id || null,
        group_id: null,
        schedule: '',
        description: ''
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const saveMeeting = async () => {
    // Validar campos requeridos
    const centerId = newMeeting.value.educational_center_id || user.value?.educational_center_id;
    
    if (newMeeting.value.name && newMeeting.value.schedule && centerId) {
        try {
            const payload = {
                name: newMeeting.value.name,
                teacher_id: user.value.id,
                teacher_name: newMeeting.value.teacher_name,
                educational_center_id: centerId ? Number(centerId) : null,
                group_id: newMeeting.value.group_id ? Number(newMeeting.value.group_id) : null,
                schedule: newMeeting.value.schedule,
                description: newMeeting.value.description
            };

            const response = await apiPost('meetings', payload);
            
            if (response) {
                showToast(t.value.meetings.success || 'Charla creada correctamente', 'success')
                await fetchMeetings();
                closeModal();
            }
        } catch (error) {
            console.error('Error saving meeting:', error);
            showToast('Error al crear la charla', 'error')
        }
    }
};

const confirmDelete = (id) => {
    meetingToDelete.value = id;
    showDeleteModal.value = true;
};

const deleteMeeting = async () => {
    if (!meetingToDelete.value) return;
    
    try {
        // Cerrar modal inmediatamente para mejor UX
        const id = meetingToDelete.value;
        showDeleteModal.value = false;
        meetingToDelete.value = null;

        await apiDelete(`meetings/${id}`);
        showToast('Charla eliminada', 'success')
        
        // Recargar la lista para sincronizar con el servidor
        await fetchMeetings();
    } catch (error) {
        console.error('Error deleting meeting:', error);
        showToast('Error al eliminar', 'error')
        // Si falló, volver a cargar la lista por si acaso
        await fetchMeetings();
    }
};

</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>

        <div v-if="toast.show" :class="['fixed top-6 left-1/2 -translate-x-1/2 z-[200] px-6 py-3 rounded-xl shadow-2xl font-semibold text-sm', toast.type === 'error' ? 'bg-red-500 text-white' : 'bg-emerald-500 text-white']">
            {{ toast.msg }}
        </div>

        <main class="lg:pl-75 pt-20 lg:pt-0 flex flex-col min-h-screen">
            <PageHeader :title="t.meetings.title" :subtitle="t.meetings.subtitle">
                <template #search>
                    <SearchBar :items="availableMeetings" @update:filtered="filteredMeetings = $event" />
                </template>
                <template #actions>
                    <PrimaryButton v-if="canCreateMeeting" :text="t.meetings.newMeeting" icon="plus" @click="openModal" />
                </template>

            </PageHeader>
    
            <section 
                :class="[
                    'text-white w-full max-w-screen-2xl mx-auto px-6 lg:px-14 flex flex-col flex-1',
                    filteredMeetings.length === 0 ? 'justify-center pb-32' : 'mb-20'
                ]"
            >
                <div v-if="filteredMeetings.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pb-10 flex-1">
                    <Meeting v-for="meeting in filteredMeetings" :key="meeting.id" :id="meeting.id" :name="meeting.name"
                        :teacher="meeting.teacher" :teacher_id="meeting.teacher_id" :schedule="meeting.schedule" :group="meeting.group"
                        :group_id="meeting.group_id" :description="meeting.description" @delete="confirmDelete" />
                </div>

                <div v-if="pagination.lastPage > 1" class="mt-12 mb-10 flex justify-center w-full">
                    <Pagination
                        :current-page="pagination.currentPage"
                        :last-page="pagination.lastPage"
                        @change="handlePageChange"
                    />
                </div>


    
                <div v-if="filteredMeetings.length === 0"
                    class="w-fit bg-white/5 backdrop-blur-md p-10 mx-auto rounded-3xl shadow-xl border border-white/10 text-center">
                    <h3 class="text-2xl font-bold text-white mb-2">{{ t.meetings.noMeetings || 'No se han encontrado reuniones' }}</h3>
                    <p v-if="!user" class="text-white/60">{{ t.meetings.loginRequired || 'Debes iniciar sesión para ver tus charlas.' }}</p>
                </div>
            </section>
    
            <!-- Modal de Creación -->
            <BaseModal v-if="showModal" :title="t.meetings.createTitle" :confirm-text="t.meetings.createConfirm" @close="closeModal"
                @confirm="saveMeeting">
                <GenericForm v-model="newMeeting" :fields="meetingFields" />
            </BaseModal>

            <!-- Modal de Confirmación de Borrado -->
            <BaseModal v-if="showDeleteModal" title="¿Eliminar charla?" confirm-text="Eliminar" @close="showDeleteModal = false"
                @confirm="deleteMeeting">
                <p class="text-white/60 text-sm">Esta acción no se puede deshacer. ¿Estás seguro de que quieres eliminar esta charla?</p>
            </BaseModal>
        </main>
    </div>
</template>
