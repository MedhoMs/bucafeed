<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import NavBar from '../../components/NavBar/NavBar.vue';
import SearchBar from '../../components/SearchBar.vue';
import Meeting from '../../components/Meeting.vue';
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
    <main class="flex min-h-screen justify-between lg:pl-75">
        <section class="flex flex-col text-white lg:w-375 w-87.5 mx-auto lg:mr-14 mb-4">
            <div class="flex justify-between mt-4">
                <SearchBar :meetings="availableMeetings" @update:filtered="filteredMeetings = $event" class="w-315" />

                <!-- Botón al estilo admin para crear -->
                <button v-if="canCreateMeeting" @click="openModal"
                    class="self-center bg-gradient-to-r from-[#326465] to-[#1d2e3e] hover:brightness-125 transition-all px-6 py-2 rounded-xl border border-white/10 shadow-lg font-bold text-sm lg:text-base flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg>
                    Nueva Charla
                </button>
            </div>

            <p class="text-2xl text-center pt-5 pb-7 lg:text-4xl font-bold shrink-0">Charlas Disponibles</p>

            <div v-if="filteredMeetings.length > 0" class="grid grid-cols-1 lg:grid-cols-4 gap-4 justify-items-center">
                <Meeting v-for="meeting in filteredMeetings" :key="meeting.id" :id="meeting.id" :name="meeting.name"
                    :teacher="meeting.teacher" :schedule="meeting.schedule" :group="meeting.group"
                    :description="meeting.description" @delete="deleteMeeting" />
            </div>

            <div v-else
                class="w-fit bg-[#2a4a5a] p-8 mx-auto my-auto rounded-[30px] shadow-xl border border-white/10 text-center">
                <h3 class="text-2xl font-bold text-white mb-2">No se han encontrado reuniones</h3>
                <p v-if="!user" class="text-white/60">Debes iniciar sesión para ver tus charlas.</p>
            </div>
        </section>

        <!-- Modal de Creación -->
        <div v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div
                class="bg-[#1e2e38] border border-white/10 w-full max-w-lg rounded-[30px] p-8 shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl lg:text-3xl font-extrabold text-white">Crear Nueva Charla</h2>
                    <button @click="closeModal" class="text-white/50 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M18 6l-12 12" />
                            <path d="M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveMeeting" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-1 ml-2">Título de la Charla</label>
                        <input v-model="newMeeting.name" type="text" placeholder="Ej: Dudas sobre PHP" required
                            class="w-full bg-[#2a4a5a] border border-white/10 rounded-2xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#326465] transition-all text-white">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-white/70 mb-1 ml-2">Profesor (Editable)</label>
                            <input v-model="newMeeting.teacher_name" type="text" placeholder="Tu nombre" required
                                class="w-full bg-[#2a4a5a] border border-white/10 rounded-2xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#326465] transition-all text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-white/70 mb-1 ml-2">Horario</label>
                            <input v-model="newMeeting.schedule" type="text" placeholder="Ej: 10:00" required
                                class="w-full bg-[#2a4a5a] border border-white/10 rounded-2xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#326465] transition-all text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-1 ml-2">Centro / Grupo</label>

                        <!-- Selector para Admin, input para el resto -->
                        <select v-if="user?.role === 'Admin'" v-model="newMeeting.educational_center_id" required
                            class="w-full bg-[#2a4a5a] border border-white/10 rounded-2xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#326465] transition-all text-white appearance-none">
                            <option v-for="center in centers" :key="center.id" :value="center.id">
                                {{ center.name }}
                            </option>
                        </select>

                        <input v-else :value="user?.institution_name" type="text" disabled
                            class="w-full bg-[#2a4a5a]/50 border border-white/10 rounded-2xl px-4 py-3 text-white/50 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-1 ml-2">Descripción</label>
                        <textarea v-model="newMeeting.description" rows="3"
                            placeholder="Describe brevemente de qué trata la charla..."
                            class="w-full bg-[#2a4a5a] border border-white/10 rounded-2xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#326465] transition-all text-white resize-none"></textarea>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="closeModal"
                            class="flex-1 bg-white/5 hover:bg-white/10 border border-white/10 text-white font-bold py-3 rounded-2xl transition-all">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 bg-gradient-to-r from-[#326465] to-[#1d2e3e] hover:brightness-125 text-white font-bold py-3 rounded-2xl shadow-lg transition-all">
                            Crear Charla
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</template>