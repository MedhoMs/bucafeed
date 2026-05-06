<script setup>
import NavBar from '@/components/NavBar/NavBar.vue';
import ButtonTemplate from '@/components/buttons/ButtonTemplate.vue';
import { useTranslations } from '@/composables/useTranslations'
import { user as authUser, token as authToken, login } from '@/stores/auth'
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import defaultLogo from '../assets/logo/logoTelamon.png';
import { useApi } from '@/composables/useApi';

const getImageUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    const base = import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace(/\/api$/, '') : 'http://localhost:8000';
    return `${base}${path.startsWith('/') ? '' : '/'}${path}`;
}

const { t } = useTranslations()
const route = useRoute()
const { post, loading: saving } = useApi()

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
        data.append('_method', 'PUT')
        data.append(type, file)

        const response = await post(`users/${authUser.value.id}`, data)
        if (response) {
            // Recargar datos para ver la nueva imagen
            await loadProfile(authUser.value.id)
        }
    } catch (e) {
        console.error("Error al subir imagen:", e)
    }
}

// El usuario de perfil que se mapea a la vista
const profileData = ref({
   name: 'Cargando...',
   email: '...',
   role: 'Estudiante',
   seguidores: '0',
   siguiendo: '0',
   bannerUrl: 'https://estaticos-cdn.prensaiberica.es/clip/3bffd319-f839-4e57-9ccb-b95ec474f104_source-aspect-ratio_default_0.jpg',
   iconoUrl: defaultLogo,
   isOwner: false
});

const loadProfile = async (id) => {
    try {
        const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
        const imgBase = import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace(/\/api$/, '') : 'http://localhost:8000';
        
        const response = await fetch(`${apiBase}/users/${id}`);
        if (response.ok) {
            const data = await response.json();

            // Si es el perfil del usuario logueado, actualizamos el store global 
            if (authUser.value && authUser.value.id === data.id) {
                login(data, authToken.value);
            }

            profileData.value = {
                name: data.name + (data.last_name ? ' ' + data.last_name : ''),
                email: data.email,
                role: data.role,
                seguidores: '12',
                siguiendo: '5',
                bannerUrl: getImageUrl(data.banner) || 'https://estaticos-cdn.prensaiberica.es/clip/3bffd319-f839-4e57-9ccb-b95ec474f104_source-aspect-ratio_default_0.jpg',
                iconoUrl: getImageUrl(data.profile_picture) || defaultLogo,
                isOwner: authUser.value && authUser.value.id === data.id
            };
        }
    } catch (e) {
        console.error("Error cargando el perfil", e);
    }
}

onMounted(() => {
    if (route.params.id) {
        loadProfile(route.params.id);
    } else if (authUser.value) {
        // Forzamos la carga desde la API para tener los datos frescos de la BD
        loadProfile(authUser.value.id);
    }
})

watch(() => route.params.id, (newId) => {
    if (newId) loadProfile(newId);
})

</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-16 lg:pt-0 flex flex-col min-h-screen">
            <div class="text-white w-full mx-auto flex flex-col">
                <section class="bg-[#15202b80] text-white flex-1">
                    <div class="relative">
                        <div class="banner cursor-pointer group" @click="triggerBannerUpload" :title="profileData.isOwner ? t.profile.upload_banner : ''">
                            <img :src="profileData.bannerUrl" alt="banner" class="w-full h-75 object-cover transition-opacity group-hover:opacity-80" />
                            <div v-if="profileData.isOwner" class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                <div class="bg-black/50 p-3 rounded-full backdrop-blur-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-12.5 left-5 group cursor-pointer" @click="triggerProfileUpload" :title="profileData.isOwner ? t.profile.upload_profile : ''">
                            <img :src="profileData.iconoUrl" alt="icono" class="icono w-25 h-25 rounded-full border-4 border-[#15202b] bg-[#15202b] object-cover shadow-xl transition-opacity group-hover:opacity-80"/>
                            <div v-if="profileData.isOwner" class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                <div class="bg-black/50 p-2 rounded-full backdrop-blur-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- Botón de configuración -->
                    <div class="flex justify-end items-center p-4 mt-0 gap-3">
                        <ButtonTemplate v-if="!profileData.isOwner" :texto="t.profile.follow" :accion="() => console.log('seguir usuario')" />
                        <router-link v-if="profileData.isOwner" to="/settings" class="p-2 rounded-full hover:bg-white/10 transition-colors text-white/70 hover:text-white" :title="t.profile.settings_tooltip">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                        </router-link>
                    </div>
    
                    <!-- Información del perfil -->
                    <div class="px-5 pb-2.5 -mt-2">
                        <div class="flex items-center gap-2">
                            <h2 class="nombre text-2xl font-bold m-0 text-[#e7e9ea]">{{ profileData.name }}</h2>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#179cf0" class="icon icon-tabler icons-tabler-filled icon-tabler-rosette-discount-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12.01 2.011a3.2 3.2 0 0 1 2.113 .797l.154 .145l.698 .698a1.2 1.2 0 0 0 .71 .341l.135 .008h1a3.2 3.2 0 0 1 3.195 3.018l.005 .182v1c0 .27 .092 .533 .258 .743l.09 .1l.697 .698a3.2 3.2 0 0 1 .147 4.382l-.145 .154l-.698 .698a1.2 1.2 0 0 0 -.341 .71l-.008 .135v1a3.2 3.2 0 0 1 -3.018 3.195l-.182 .005h-1a1.2 1.2 0 0 0 -.743 .258l-.1 .09l-.698 .697a3.2 3.2 0 0 1 -4.382 .147l-.154 -.145l-.698 -.698a1.2 1.2 0 0 0 -.71 -.341l-.135 -.008h-1a3.2 3.2 0 0 1 -3.195 -3.018l-.005 -.182v-1a1.2 1.2 0 0 0 -.258 -.743l-.09 -.1l-.697 -.698a3.2 3.2 0 0 1 -.147 -4.382l.145 -.154l.698 -.698a1.2 1.2 0 0 0 .341 -.71l.008 -.135v-1l.005 -.182a3.2 3.2 0 0 1 3.013 -3.013l.182 -.005h1a1.2 1.2 0 0 0 .743 -.258l.1 -.09l.698 -.697a3.2 3.2 0 0 1 2.269 -.944zm3.697 7.282a1 1 0 0 0 -1.414 0l-3.293 3.292l-1.293 -1.292l-.094 -.083a1 1 0 0 0 -1.32 1.497l2 2l.094 .083a1 1 0 0 0 1.32 -.083l4 -4l.083 -.094a1 1 0 0 0 -.083 -1.32z" /></svg>
                        </div>
                        <p class="nombre-usuario text-[#8b98a5] text-base my-0.5 mx-0">{{ profileData.email }}</p>
                        <p class="text-white/50 text-xs font-mono uppercase mt-1">{{ profileData.role }}</p>
                        <p class="seguidores text-[#8b98a5] text-sm mt-2">
                            <span class="numero font-bold text-[#e7e9ea]">{{ profileData.seguidores }}</span> 
                            {{ t.profile.followers }} · 
                            <span class="numero font-bold text-[#e7e9ea]">{{ profileData.siguiendo }}</span>
                            {{ t.profile.following }}
                        </p>
                    </div>
    
                    <!-- Pestañas -->
                    <div class="pestanas flex justify-around border-t border-b border-[#2a4a5a] py-2.5 mt-2">
                        <span class="cursor-pointer px-3 py-2 rounded-sm transition-colors hover:bg-[#1e2936] text-[#8b98a5]">{{ t.profile.posts }}</span>
                        <span class="cursor-pointer px-3 py-2 rounded-sm transition-colors hover:bg-[#1e2936] text-[#8b98a5]">{{ t.profile.events }}</span>
                        <span class="cursor-pointer px-3 py-2 rounded-sm transition-colors hover:bg-[#1e2936] text-[#8b98a5]">{{ t.profile.likes }}</span>
                    </div>
    
                    <!-- Contenido de publicaciones -->
                    <div class="publicaciones px-5 py-2.5">
                        <p class="text-[#8b98a5]">{{ t.profile.userContent }}</p>
                    </div>
                </section>
            </div>
        </main>
    </div>
</template>

<style scoped>

</style>