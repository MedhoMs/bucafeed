<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import NavBar from '../../components/NavBar/NavBar.vue';
import SearchBar from '../../components/SearchBar.vue';
import Meeting from '../../components/Meeting.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import PrimaryButton from '@/components/common/PrimaryButton.vue';
import BaseModal from '@/components/modals/BaseModal.vue';
import GenericForm from '@/components/common/forms/GenericForm.vue';
import { useTranslations } from '../../composables/useTranslations'
import { user } from '../../stores/auth';

const { t } = useTranslations()

const meetings = ref([]);
const centers = ref([]);

const fetchCenters = async () => {
    if (user.value?.role !== 'Admin') return;
    try {
        const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
        const response = await fetch(`${apiBase}/educational-centers`);
        if (response.ok) {
            centers.value = await response.json();
        }
    } catch (error) {
        console.error('Error fetching centers:', error);
    }
};

const fetchMeetings = async () => {
    try {
        const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
        let url = `${apiBase}/meetings`;
        if (user.value && user.value.role !== 'Admin') {
            url += `?institution_name=${encodeURIComponent(user.value.institution_name)}`;
        }

        const response = await fetch(url);
        if (response.ok) {
            const data = await response.json();
            meetings.value = data.map(m => ({
                id: m.id,
                name: m.name,
                teacher: m.teacher ? m.teacher.name : (m.teacher_name || 'Desconocido'),
                group: m.educational_center ? m.educational_center.name : 'Varios',
                schedule: m.schedule,
                description: m.description
            }));
        }
    } catch (error) {
        console.error('Error fetching meetings:', error);
    }
};

onMounted(() => {
    fetchMeetings();
    fetchCenters();
});

// 1. Charlas disponibles para este usuario según su centro
const availableMeetings = computed(() => {
    if (!user.value) return [];
    if (user.value.role === 'Admin') return meetings.value;
    return meetings.value.filter(m => m.group === user.value.institution_name);
});

// 2. Estado para los resultados filtrados por el buscador
const filteredMeetings = ref([]);

// Sincronizar resultados cuando cambien las disponibles
watch(availableMeetings, (newList) => {
    filteredMeetings.value = [...newList];
}, { immediate: true });

// --- Lógica del Modal de Creación ---
const showModal = ref(false);

const canCreateMeeting = computed(() => {
    if (!user.value) return false;
    const allowedRoles = ['Teacher', 'Admin', 'EI'];
    return allowedRoles.includes(user.value.role);
});

const newMeeting = ref({
    name: '',
    teacher_name: '', // Nombre libre (editable)
    educational_center_id: null,
    schedule: '',
    description: ''
});

const meetingFields = computed(() => {
    const baseFields = [
        { id: 'name', type: 'text', label: 'Título de la Charla', placeholder: 'Ej: Dudas sobre PHP', required: true },
        { id: 'teacher_name', type: 'text', label: 'Profesor (Editable)', placeholder: 'Tu nombre', required: true, full: false },
        { id: 'schedule', type: 'text', label: 'Horario', placeholder: 'Ej: 10:00', required: true, full: false },
    ];

    if (user.value?.role === 'Admin') {
        baseFields.push({
            id: 'educational_center_id',
            type: 'select',
            label: 'Centro Educativo',
            placeholder: 'Seleccionar centro...',
            required: true,
            options: centers.value.map(c => ({ id: c.id, name: c.name }))
        });
    } else {
        baseFields.push({
            id: 'info_center',
            type: 'info',
            label: 'Centro / Grupo',
            value: user.value?.institution_name || 'Mi Centro'
        });
    }

    baseFields.push({ id: 'description', type: 'textarea', label: 'Descripción', placeholder: 'Describe brevemente de qué trata la charla...', required: false });

    return baseFields;
});

const openModal = () => {
    if (canCreateMeeting.value) {
        // Inicializar con valores "obvios"
        newMeeting.value = {
            name: '',
            teacher_name: user.value?.name || '',
            educational_center_id: user.value?.educational_center_id || null,
            schedule: '',
            description: ''
        };
        showModal.value = true;
    }
};

const closeModal = () => {
    showModal.value = false;
};

const saveMeeting = async () => {
    // Validar campos requeridos
    if (newMeeting.value.name && newMeeting.value.schedule && newMeeting.value.educational_center_id) {
        try {
            const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
            const response = await fetch(`${apiBase}/meetings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: newMeeting.value.name,
                    teacher_id: user.value.id, // ID del creador
                    teacher_name: newMeeting.value.teacher_name, // Nombre que aparecerá
                    educational_center_id: newMeeting.value.educational_center_id,
                    schedule: newMeeting.value.schedule,
                    description: newMeeting.value.description
                })
            });

            if (response.ok) {
                await fetchMeetings();
                closeModal();
            } else {
                const errData = await response.json();
                console.error('Failed to save meeting:', errData);
            }
        } catch (error) {
            console.error('Error saving meeting:', error);
        }
    }
};

const deleteMeeting = async (id) => {
    try {
        const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
        const response = await fetch(`${apiBase}/meetings/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            // Recargar la lista
            await fetchMeetings();
        } else {
            console.error('Failed to delete meeting');
        }
    } catch (error) {
        console.error('Error deleting meeting:', error);
    }
};

</script>

<template>
    <NavBar></NavBar>
    <main class="flex flex-col min-h-screen lg:pl-80">
        <PageHeader title="Charlas Disponibles" subtitle="Conecta con profesores y compañeros en tiempo real.">
            <template #search>
                <SearchBar :meetings="availableMeetings" @update:filtered="filteredMeetings = $event" />
            </template>
            <template #actions>
                <PrimaryButton v-if="canCreateMeeting" text="Nueva Charla" icon="plus" @click="openModal" />
            </template>
        </PageHeader>

        <section class="text-white w-full max-w-screen-2xl mx-auto px-6 lg:px-14 mb-20">
            <div v-if="filteredMeetings.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 justify-items-center pb-20">
                <Meeting v-for="meeting in filteredMeetings" :key="meeting.id" :id="meeting.id" :name="meeting.name"
                    :teacher="meeting.teacher" :schedule="meeting.schedule" :group="meeting.group"
                    :description="meeting.description" @delete="deleteMeeting" />
            </div>

            <div v-else
                class="w-fit bg-[#2a4a5a] p-8 mx-auto my-auto rounded-3xl shadow-xl border border-white/10 text-center">
                <h3 class="text-2xl font-bold text-white mb-2">No se han encontrado reuniones</h3>
                <p v-if="!user" class="text-white/60">Debes iniciar sesión para ver tus charlas.</p>
            </div>
        </section>

        <!-- Modal de Creación -->
        <BaseModal v-if="showModal" title="Crear Nueva Charla" confirm-text="Crear Charla" @close="closeModal"
            @confirm="saveMeeting">
            <GenericForm v-model="newMeeting" :fields="meetingFields" />
        </BaseModal>
    </main>
</template>
