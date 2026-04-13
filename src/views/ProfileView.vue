<script setup>
import NavBar from '@/components/NavBar/NavBar.vue';
import ButtonTemplate from '@/components/buttons/ButtonTemplate.vue';
import { useTranslations } from '@/composables/useTranslations'
import { user as authUser } from '@/stores/auth'
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import defaultLogo from '../assets/logo/logoTelamon.png';

const { t } = useTranslations()
const route = useRoute()

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
            profileData.value = {
                name: data.name + (data.last_name ? ' ' + data.last_name : ''),
                email: data.email,
                role: data.role,
                seguidores: '12',
                siguiendo: '5',
                bannerUrl: data.banner_picture ? imgBase + data.banner_picture : 'https://estaticos-cdn.prensaiberica.es/clip/3bffd319-f839-4e57-9ccb-b95ec474f104_source-aspect-ratio_default_0.jpg',
                iconoUrl: data.profile_picture ? imgBase + data.profile_picture : defaultLogo,
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
        const imgBase = import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace(/\/api$/, '') : 'http://localhost:8000';
        profileData.value = {
            name: authUser.value.name,
            email: authUser.value.email,
            role: authUser.value.role,
            seguidores: '1M',
            siguiendo: '3',
            bannerUrl: authUser.value.banner_picture ? imgBase + authUser.value.banner_picture : 'https://estaticos-cdn.prensaiberica.es/clip/3bffd319-f839-4e57-9ccb-b95ec474f104_source-aspect-ratio_default_0.jpg',
            iconoUrl: authUser.value.profile_picture ? imgBase + authUser.value.profile_picture : defaultLogo,
            isOwner: true
        }
    }
})

// Por si se navega de un perfil a otro sin recargar
watch(() => route.params.id, (newId) => {
    if (newId) loadProfile(newId);
})
</script>

<template>
    <main class="flex flex-row justify-between min-h-screen">
        <NavBar />

        <!-- Sección central: Perfil -->
        <div class="w-[1580px] flex flex-col mr-4">

            <section class="bg-[#15202b80] text-white flex-1">

                <div class="relative">
                    <div class="banner">
                        <img :src="profileData.bannerUrl" alt="banner" class="w-full h-[300px] object-cover" />
                    </div>
                    <img :src="profileData.iconoUrl" alt="icono" class="icono absolute w-[100px] h-[100px] rounded-full border-4 border-[#15202b] bg-[#15202b] object-cover bottom-[-50px] left-5 shadow-xl"/>
                </div>

                <!-- Botón de editar perfil -->
                <div class="flex justify-end items-center p-4 mt-0">
                    <ButtonTemplate v-if="profileData.isOwner" :texto="t.profile.editprofile" :accion="() => console.log('editar perfil')" />
                    <ButtonTemplate v-else texto="Seguir" :accion="() => console.log('seguir usuario')" />
                </div>

                <!-- Información del perfil -->
                <div class="px-5 pb-2.5 -mt-2">
                    <div class="flex items-center gap-1">
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
                    <span class="cursor-pointer px-3 py-2 rounded transition-colors hover:bg-[#1e2936] text-[#8b98a5]">{{ t.profile.posts }}</span>
                    <span class="cursor-pointer px-3 py-2 rounded transition-colors hover:bg-[#1e2936] text-[#8b98a5]">{{ t.profile.photos }}</span>
                    <span class="cursor-pointer px-3 py-2 rounded transition-colors hover:bg-[#1e2936] text-[#8b98a5]">{{ t.profile.likes }}</span>
                </div>

                <!-- Contenido de publicaciones -->
                <div class="publicaciones px-5 py-2.5">
                    <p class="text-[#8b98a5]">{{ t.profile.userContent }}</p>
                </div>
            </section>
        </div>
    </main>
</template>

<style scoped>

</style>