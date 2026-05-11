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
        const data = await get(`users/${id}?t=${new Date().getTime()}`);
        if (data) {
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
                <section class="bg-black/10 text-white flex-1">
                    <div class="relative">
                        <div class="banner cursor-pointer group" @click="triggerBannerUpload" :title="profileData.isOwner ? t.profile.upload_banner : ''">
                            <img :src="profileData.bannerUrl" alt="banner" class="w-full h-75 object-cover transition-opacity group-hover:opacity-80" />
                            <div v-if="profileData.isOwner" class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                <div class="bg-black/50 p-3 rounded-full backdrop-blur-sm">
                                    <span class="material-symbols-outlined !text-2xl text-white">photo_camera</span>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-12.5 left-5 group cursor-pointer" @click="triggerProfileUpload" :title="profileData.isOwner ? t.profile.upload_profile : ''">
                            <img :src="profileData.iconoUrl" alt="icono" class="icono w-25 h-25 rounded-full border-4 border-background bg-background object-cover shadow-xl transition-opacity group-hover:opacity-80" :class="{'opacity-50 blur-sm': saving}"/>
                            <div v-if="profileData.isOwner && !saving" class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                <div class="bg-black/50 p-2 rounded-full backdrop-blur-sm">
                                    <span class="material-symbols-outlined text-white !text-lg">add_a_photo</span>
                                </div>
                            </div>
                            <div v-if="saving" class="absolute inset-0 flex items-center justify-center">
                                <div class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                            </div>
                        </div>
                    </div>
    
                    <!-- Botón de configuración -->
                    <div class="flex justify-end items-center p-4 mt-0 gap-3">
                        <ButtonTemplate v-if="!profileData.isOwner" :texto="t.profile.follow" :accion="() => console.log('seguir usuario')" />
                        <router-link v-if="profileData.isOwner" to="/settings" class="p-2 rounded-full hover:bg-white/10 transition-colors text-white/70 hover:text-white" :title="t.profile.settings_tooltip">
                            <span class="material-symbols-outlined">settings</span>
                        </router-link>
                    </div>
    
                    <!-- Información del perfil -->
                    <div class="px-5 pb-2.5 -mt-2">
                        <div class="flex items-center gap-2">
                            <h2 class="nombre text-2xl font-bold m-0 text-[#e7e9ea]">{{ profileData.name }}</h2>
                            <span class="material-symbols-outlined text-primary-normal !text-xl" title="Verificado">verified</span>
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
                        <span class="cursor-pointer px-3 py-2 rounded-sm transition-colors hover:bg-white/5 text-[#8b98a5]">{{ t.profile.posts }}</span>
                        <span class="cursor-pointer px-3 py-2 rounded-sm transition-colors hover:bg-white/5 text-[#8b98a5]">{{ t.profile.events }}</span>
                        <span class="cursor-pointer px-3 py-2 rounded-sm transition-colors hover:bg-white/5 text-[#8b98a5]">{{ t.profile.likes }}</span>
                    </div>
    
                    <!-- Contenido de publicaciones -->
                    <div class="publicaciones px-5 py-2.5">
                        <p class="text-[#8b98a5]">{{ t.profile.userContent }}</p>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Hidden Inputs for Upload -->
    <input 
        type="file" 
        id="bannerInput" 
        class="hidden" 
        accept="image/*" 
        @change="onFileChange($event, 'banner')"
    />
    <input 
        type="file" 
        id="profileInput" 
        class="hidden" 
        accept="image/*" 
        @change="onFileChange($event, 'profile_picture')"
    />
</template>

<style scoped>

</style>