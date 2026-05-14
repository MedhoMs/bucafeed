<script setup>
import NavBar from '@/components/NavBar/NavBar.vue';
import ButtonTemplate from '@/components/buttons/ButtonTemplate.vue';
import { useTranslations } from '@/composables/useTranslations'
import { user as authUser, token as authToken, login } from '@/stores/auth'
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import defaultLogo from '../assets/logo/logoTelamon.png';
import { useApi } from '@/composables/useApi';

const getImageUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    const base = import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace(/\/api$/, '') : 'http://localhost:8000';
    const cleanPath = path.startsWith('/') ? path : '/' + path;
    return `${base}${cleanPath}?t=${new Date().getTime()}`;
}

const { t } = useTranslations()
const route = useRoute()
const { post, get, loading: saving } = useApi()

const bannerFile = ref(null)
const profileFile = ref(null)

const triggerBannerUpload = () => {
    if (profileData.value.isOwner) document.getElementById('bannerInput').click()
}

const triggerProfileUpload = () => {
    if (profileData.value.isOwner) document.getElementById('profileInput').click()
}

const onFileChange = async (event, type) => {
    const file = event.target.files[0]
    if (!file) return

    try {
        const data = new FormData()
        // Usamos PATCH para actualizaciones parciales en Laravel via POST
        data.append('_method', 'PATCH')
        data.append(type, file)

        const response = await post(`users/${authUser.value.id}`, data)
        if (response) {
            // Actualizar localmente e inmediatamente para feedback instantáneo
            await loadProfile(authUser.value.id)

            // Si el usuario es el mismo, refrescar el estado global de auth
            if (authUser.value && authUser.value.id === response.id) {
                login(response, authToken.value);
            }
        }
    } catch (e) {
        console.error("Error al subir imagen:", e)
        alert(t.forum?.error || "Error al subir la imagen");
    } finally {
        // Limpiar el input para permitir subir el mismo archivo después
        event.target.value = '';
    }
}

// El usuario de perfil que se mapea a la vista
const profileData = ref({
    id: null,
    name: 'Cargando...',
    email: '...',
    role: 'Estudiante',
    roleCode: 'Student',
    seguidores: '0',
    siguiendo: '0',
    bannerUrl: 'https://estaticos-cdn.prensaiberica.es/clip/3bffd319-f839-4e57-9ccb-b95ec474f104_source-aspect-ratio_default_0.jpg',
    iconoUrl: defaultLogo,
    isOwner: false,
    isFollowing: false,
    description: ''
});

const activeTab = ref('');
const tabContent = ref([]);
const loadingContent = ref(false);
const tutors = ref([]);
const loadingTutors = ref(false);

const canViewTutors = computed(() => {
    if (!authUser.value) return false
    const role = authUser.value.role?.toLowerCase()
    return profileData.value.isOwner
        || ['teacher', 'ei', 'admin', 'profesor', 'institución educativa', 'administrador'].includes(role)
})

const fetchTutors = async (userId) => {
    if (!canViewTutors.value) return
    loadingTutors.value = true
    try {
        const data = await get(`users/${userId}/tutors`)
        if (data) tutors.value = data
    } catch (e) {
        console.error('Error fetching tutors:', e)
    } finally {
        loadingTutors.value = false
    }
}

const setTab = (tab) => {
    activeTab.value = tab;
    fetchTabContent(tab);
}

const fetchTabContent = async (tab) => {
    if (!profileData.value.id) return;
    loadingContent.value = true;
    tabContent.value = [];

    try {
        let endpoint = '';
        let params = {};

        switch (tab) {
            case 'questions':
                endpoint = 'questions';
                params = { user_id: profileData.value.id };
                break;
            case 'answers':
                endpoint = 'answers';
                params = { user_id: profileData.value.id };
                break;
            case 'events_participated':
                endpoint = 'events';
                params = { participant_id: profileData.value.id };
                break;
            case 'events_created':
                endpoint = 'events';
                params = { center_id: profileData.value.educational_center_id || profileData.value.id };
                break;
            case 'talks':
                endpoint = 'meetings';
                params = { teacher_id: profileData.value.id };
                break;
        }

        if (endpoint) {
            const query = new URLSearchParams(params).toString();
            const response = await get(`${endpoint}?${query}`);
            if (response && response.data) {
                tabContent.value = response.data;
            }
        }
    } catch (e) {

    } finally {
        loadingContent.value = false;
    }
}

const toggleFollow = async () => {
    if (!profileData.value.id || profileData.value.isOwner) return;

    try {
        const response = await post(`users/${profileData.value.id}/follow`, {});
        if (response) {
            profileData.value.isFollowing = response.is_following;
            profileData.value.seguidores = response.followers_count;
        }
    } catch (e) {

    }
}

const loadProfile = async (id) => {
    if (!id) return;
    try {
        const data = await get(`users/${id}?t=${new Date().getTime()}`);
        if (data) {
            // Si es el perfil del usuario logueado, actualizamos el store global 
            if (authUser.value && authUser.value.id === data.id) {
                login(data, authToken.value);
            }

            profileData.value = {
                id: data.id,
                name: data.name + (data.last_name ? ' ' + data.last_name : ''),
                email: data.email,
                role: data.role_name || data.role,
                roleCode: data.role,
                educational_center_id: data.educational_center_id,
                seguidores: data.followers_count || 0,
                siguiendo: data.following_count || 0,
                bannerUrl: getImageUrl(data.banner) || 'https://estaticos-cdn.prensaiberica.es/clip/3bffd319-f839-4e57-9ccb-b95ec474f104_source-aspect-ratio_default_0.jpg',
                iconoUrl: getImageUrl(data.profile_picture) || defaultLogo,
                isOwner: authUser.value && authUser.value.id === data.id,
                isFollowing: data.is_following || false,
                description: data.description || ''
            };

            const role = data.role?.toLowerCase();
            if (role === 'student') {
                setTab('questions');
                fetchTutors(id);
            } else if (role === 'teacher') {
                setTab('talks');
            } else if (role === 'ei') {
                setTab('events_created');
            } else {
                activeTab.value = '';
                tabContent.value = [];
            }
        }
    } catch (e) {

    }
}

onMounted(() => {
    const id = route.params.id || authUser.value?.id;
    if (id) loadProfile(id);
})

watch(() => route.params.id, (newId) => {
    const id = newId || authUser.value?.id;
    if (id) loadProfile(id);
})

</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-16 lg:pt-0 flex flex-col min-h-screen">
            <div class="text-white w-full mx-auto flex flex-col">
                <section class="bg-black/10 text-white flex-1">
                    <div class="relative">
                        <div class="banner cursor-pointer group" @click="triggerBannerUpload"
                            :title="profileData.isOwner ? t.profile.upload_banner : ''">
                            <img :src="profileData.bannerUrl" alt="banner"
                                class="w-full h-75 object-cover transition-opacity group-hover:opacity-80" />
                            <div v-if="profileData.isOwner"
                                class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                <div class="bg-black/50 p-3 rounded-full backdrop-blur-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                        width="24px" fill="#FFFFFF">
                                        <path
                                            d="M480-260q75 0 127.5-52.5T660-440q0-75-52.5-127.5T480-620q-75 0-127.5 52.5T300-440q0 75 52.5 127.5T480-260Zm0-80q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29ZM160-120q-33 0-56.5-23.5T80-200v-480q0-33 23.5-56.5T160-760h126l74-80h240l74 80h126q33 0 56.5 23.5T880-680v480q0 33-23.5 56.5T800-120H160Zm0-80h640v-480H638l-73-80H395l-73 80H160v480Zm320-240Z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-12.5 left-5 group cursor-pointer" @click="triggerProfileUpload"
                            :title="profileData.isOwner ? t.profile.upload_profile : ''">
                            <img :src="profileData.iconoUrl" alt="icono"
                                class="icono w-25 h-25 rounded-full border-4 border-background bg-background object-cover shadow-xl transition-opacity group-hover:opacity-80"
                                :class="{ 'opacity-50 blur-sm': saving }" />
                            <div v-if="profileData.isOwner && !saving"
                                class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                <div class="bg-black/50 p-2 rounded-full backdrop-blur-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                        width="24px" fill="#FFFFFF">
                                        <path
                                            d="M440-440ZM120-120q-33 0-56.5-23.5T40-200v-480q0-33 23.5-56.5T120-760h126l74-80h240v80H355l-73 80H120v480h640v-360h80v360q0 33-23.5 56.5T760-120H120Zm640-560v-80h-80v-80h80v-80h80v80h80v80h-80v80h-80ZM440-260q75 0 127.5-52.5T620-440q0-75-52.5-127.5T440-620q-75 0-127.5 52.5T260-440q0 75 52.5 127.5T440-260Zm0-80q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29Z" />
                                    </svg>
                                </div>
                            </div>
                            <div v-if="saving" class="absolute inset-0 flex items-center justify-center">
                                <div class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de configuración -->
                    <div class="flex justify-end items-center p-4 mt-0 gap-3">
                        <ButtonTemplate v-if="!profileData.isOwner && profileData.roleCode?.toLowerCase() !== 'admin'"
                            :texto="profileData.isFollowing ? t.profile.unfollow : t.profile.follow"
                            :accion="toggleFollow" />
                        <router-link v-if="profileData.isOwner" to="/settings"
                            class="p-2 rounded-full hover:bg-white/10 transition-colors text-white/70 hover:text-white"
                            :title="t.profile.settings_tooltip">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                                fill="#FFFFFF">
                                <path
                                    d="m370-80-16-128q-13-5-24.5-12T307-235l-119 50L78-375l103-78q-1-7-1-13.5v-27q0-6.5 1-13.5L78-585l110-190 119 50q11-8 23-15t24-12l16-128h220l16 128q13 5 24.5 12t22.5 15l119-50 110 190-103 78q1 7 1 13.5v27q0 6.5-2 13.5l103 78-110 190-118-50q-11 8-23 15t-24 12L590-80H370Zm70-80h79l14-106q31-8 57.5-23.5T639-327l99 41 39-68-86-65q5-14 7-29.5t2-31.5q0-16-2-31.5t-7-29.5l86-65-39-68-99 42q-22-23-48.5-38.5T533-694l-13-106h-79l-14 106q-31 8-57.5 23.5T321-633l-99-41-39 68 86 64q-5 15-7 30t-2 32q0 16 2 31t7 30l-86 65 39 68 99-42q22 23 48.5 38.5T427-266l13 106Zm42-180q58 0 99-41t41-99q0-58-41-99t-99-41q-59 0-99.5 41T342-480q0 58 40.5 99t99.5 41Zm-2-140Z" />
                            </svg>
                        </router-link>
                    </div>

                    <!-- Información del perfil -->
                    <div class="px-5 pb-2.5 -mt-2">
                        <div class="flex items-center gap-2">
                            <h2 class="nombre text-2xl font-bold m-0 text-[#e7e9ea]">{{ profileData.name }}</h2>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                                fill="#009dff">
                                <path
                                    d="m344-60-76-128-144-32 14-148-98-112 98-112-14-148 144-32 76-128 136 58 136-58 76 128 144 32-14 148 98 112-98 112 14 148-144 32-76 128-136-58-136 58Zm34-102 102-44 104 44 56-96 110-26-10-112 74-84-74-86 10-112-110-24-58-96-102 44-104-44-56 96-110 24 10 112-74 86 74 84-10 114 110 24 58 96Zm102-318Zm-42 142 226-226-56-58-170 170-86-84-56 56 142 142Z" />
                            </svg>
                        </div>
                        <p class="nombre-usuario text-[#8b98a5] text-base my-0.5 mx-0">{{ profileData.email }}</p>
                        <p class="text-white/50 text-xs font-mono uppercase mt-1">{{ profileData.role }}</p>

                        <div v-if="profileData.roleCode?.toLowerCase() !== 'admin'"
                            class="seguidores text-[#8b98a5] text-sm mt-2">
                            <span class="numero font-bold text-[#e7e9ea]">{{ profileData.seguidores }}</span>
                            {{ t.profile.followers }}
                            <template v-if="profileData.roleCode?.toLowerCase() !== 'ei'">
                                ·
                                <span class="numero font-bold text-[#e7e9ea]">{{ profileData.siguiendo }}</span>
                                {{ t.profile.following }}
                            </template>
                        </div>

                        <!-- Tutores Legales (Solo visible para Profesores, Centros y el propio alumno) -->
                        <div v-if="canViewTutors && tutors.length > 0"
                            class="tutors text-[#8b98a5] text-sm mt-3 border-t border-white/5 pt-3">
                            <p class="text-white/40 text-[10px] font-black uppercase tracking-widest mb-2">{{
                                t.settings?.tutors?.title }}</p>
                            <div class="flex flex-wrap gap-2">
                                <div v-for="tutor in tutors" :key="tutor.id"
                                    class="flex items-center gap-2 bg-white/5 px-3 py-1.5 rounded-full border border-white/10 group/tutor hover:border-brand-net/30 transition-all">
                                    <div
                                        class="w-5 h-5 rounded-full bg-brand-net/10 flex items-center justify-center overflow-hidden border border-brand-net/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                            width="24px" fill="#FFFFFF">
                                            <path
                                                d="M609-389q-29-29-29-71t29-71q29-29 71-29t71 29q29 29 29 71t-29 71q-29 29-71 29t-71-29ZM480-160v-56q0-24 12.5-44.5T528-290q36-15 74.5-22.5T680-320q39 0 77.5 7.5T832-290q23 9 35.5 29.5T880-216v56H480ZM287-527q-47-47-47-113t47-113q47-47 113-47t113 47q47 47 47 113t-47 113q-47 47-113 47t-113-47Zm113-113ZM80-160v-112q0-34 17-62.5t47-43.5q60-30 124.5-46T400-440q35 0 70 6t70 14l-34 34-34 34q-18-5-36-6.5t-36-1.5q-58 0-113.5 14T180-306q-10 5-15 14t-5 20v32h240v80H80Zm320-80Zm56.5-343.5Q480-607 480-640t-23.5-56.5Q433-720 400-720t-56.5 23.5Q320-673 320-640t23.5 56.5Q367-560 400-560t56.5-23.5Z" />
                                        </svg>
                                    </div>
                                    <span class="text-white text-xs font-bold">{{ tutor.name }} {{ tutor.last_name
                                        }}</span>
                                </div>
                            </div>
                        </div>

                        <p v-if="profileData.description" class="text-[#e7e9ea] mt-3 text-sm italic">{{
                            profileData.description }}</p>
                    </div>

                    <!-- Pestañas dinámicas -->
                    <div v-if="profileData.roleCode?.toLowerCase() !== 'admin'"
                        class="pestanas flex justify-around border-t border-b border-[#2a4a5a] py-2.5 mt-2 overflow-x-auto">
                        <!-- Estudiante -->
                        <template v-if="profileData.roleCode?.toLowerCase() === 'student'">
                            <span @click="setTab('questions')"
                                :class="{ 'text-white border-b-2 border-white': activeTab === 'questions' }"
                                class="cursor-pointer px-3 py-2 rounded-sm transition-colors hover:bg-white/5 text-[#8b98a5]">{{
                                t.profile.questions }}</span>
                            <span @click="setTab('answers')"
                                :class="{ 'text-white border-b-2 border-white': activeTab === 'answers' }"
                                class="cursor-pointer px-3 py-2 rounded-sm transition-colors hover:bg-white/5 text-[#8b98a5]">{{
                                t.profile.answers }}</span>
                            <span @click="setTab('events_participated')"
                                :class="{ 'text-white border-b-2 border-white': activeTab === 'events_participated' }"
                                class="cursor-pointer px-3 py-2 rounded-sm transition-colors hover:bg-white/5 text-[#8b98a5]">{{
                                    t.profile.events_participated }}</span>
                        </template>

                        <!-- Profesor -->
                        <template v-if="profileData.roleCode?.toLowerCase() === 'teacher'">
                            <span @click="setTab('talks')"
                                :class="{ 'text-white border-b-2 border-white': activeTab === 'talks' }"
                                class="cursor-pointer px-3 py-2 rounded-sm transition-colors hover:bg-white/5 text-[#8b98a5]">{{
                                t.profile.talks }}</span>
                            <span @click="setTab('events_participated')"
                                :class="{ 'text-white border-b-2 border-white': activeTab === 'events_participated' }"
                                class="cursor-pointer px-3 py-2 rounded-sm transition-colors hover:bg-white/5 text-[#8b98a5]">{{
                                    t.profile.events_participated }}</span>
                        </template>

                        <!-- Centro Educativo -->
                        <template v-if="profileData.roleCode?.toLowerCase() === 'ei'">
                            <span @click="setTab('events_created')"
                                :class="{ 'text-white border-b-2 border-white': activeTab === 'events_created' }"
                                class="cursor-pointer px-3 py-2 rounded-sm transition-colors hover:bg-white/5 text-[#8b98a5]">{{
                                t.profile.events_created }}</span>
                        </template>
                    </div>

                    <!-- Contenido de publicaciones -->
                    <div class="min-h-dvh py-5 px-5 lg:px-10">
                        <div v-if="loadingContent" class="flex justify-center py-10">
                            <div
                                class="w-8 h-8 border-4 border-primary-normal/30 border-t-primary-normal rounded-full animate-spin">
                            </div>
                        </div>

                        <div v-else-if="tabContent.length > 0" class="flex flex-col gap-4">
                            <div v-for="item in tabContent" :key="item.id"
                                class="bg-white/5 p-4 rounded-xl border border-white/10 hover:bg-white/10 transition-colors">
                                <!-- Renderizado según tipo de contenido -->
                                <template v-if="activeTab === 'questions'">
                                    <router-link :to="'/question/' + item.id" class="block">
                                        <h3 class="text-lg font-bold text-white mb-1">{{ item.title }}</h3>
                                        <p class="text-[#8b98a5] line-clamp-2 text-sm">{{ item.content }}</p>
                                        <span class="text-primary-normal text-xs mt-2 inline-block">Ver pregunta
                                            original</span>
                                    </router-link>
                                </template>

                                <template v-else-if="activeTab === 'answers'">
                                    <router-link :to="'/question/' + item.question_id" class="block">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span
                                                class="bg-primary-normal/20 text-primary-normal text-[10px] px-2 py-0.5 rounded-full uppercase font-bold">Respuesta</span>
                                            <span class="text-[#8b98a5] text-xs">En: {{ item.question?.title }}</span>
                                        </div>
                                        <p class="text-[#e7e9ea] line-clamp-3 text-sm">{{ item.content }}</p>
                                        <span class="text-primary-normal text-xs mt-2 inline-block">Ir a la
                                            conversación</span>
                                    </router-link>
                                </template>

                                <template
                                    v-else-if="activeTab === 'events_participated' || activeTab === 'events_created'">
                                    <router-link :to="'/event-details/' + item.id" class="block">
                                        <div class="flex gap-4">
                                            <img v-if="item.image" :src="getImageUrl(item.image)"
                                                class="w-20 h-20 rounded-lg object-cover" />
                                            <div class="flex-1">
                                                <h3 class="text-lg font-bold text-white">{{ item.title }}</h3>
                                                <p class="text-[#8b98a5] text-xs">{{ item.date }} · {{ item.location }}
                                                </p>
                                                <p class="text-[#8b98a5] line-clamp-1 text-sm mt-1">{{ item.description
                                                    }}</p>
                                            </div>
                                        </div>
                                    </router-link>
                                </template>

                                <template v-else-if="activeTab === 'talks'">
                                    <router-link :to="'/meetingchat/' + item.id" class="block">
                                        <h3 class="text-lg font-bold text-white">{{ item.name }}</h3>
                                        <p class="text-[#8b98a5] text-sm">{{ item.schedule }}</p>
                                        <p class="text-[#8b98a5] line-clamp-2 text-sm mt-1">{{ item.description }}</p>
                                    </router-link>
                                </template>
                            </div>
                        </div>

                        <div v-else-if="activeTab" class="text-center py-10">
                            <p class="text-[#8b98a5]">{{ t.profile.noContent || 'No hay contenido disponible por el momento.' }}</p>
                        </div>

                        <div v-else-if="profileData.roleCode?.toLowerCase() === 'admin'" class="text-center py-10">
                            <p class="text-[#8b98a5]">Los administradores no tienen contenido público visible.</p>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Hidden Inputs for Upload -->
    <input type="file" id="bannerInput" class="hidden" accept="image/*" @change="onFileChange($event, 'banner')" />
    <input type="file" id="profileInput" class="hidden" accept="image/*"
        @change="onFileChange($event, 'profile_picture')" />
</template>

<style scoped></style>