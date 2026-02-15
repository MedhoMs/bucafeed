<!--Componente de barra de navegacion-->
<script setup>
    import NavBarLinks from '../components/NavBarLinks.vue';

    import { onMounted, ref } from 'vue'

    import { useTranslations } from '../composables/useTranslations'
    const { t } = useTranslations() //Variable para llamar al archivo de traduccion

    const dbStatus = ref('loading') // loading, connected, error

    const checkDbConnection = async () => {
        try {
            const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8001/api'
            const response = await fetch(`${apiBase}/test-connection`)
            const data = await response.json()
            if (data.status === 'success' && data.database.includes('correctamente')) {
                dbStatus.value = 'connected'
            } else {
                dbStatus.value = 'error'
            }
        } catch (error) {
            dbStatus.value = 'error'
        }
    }

    const showPopup = ref(false)

    const toggleDotsPopup = () => {
        showPopup.value = !showPopup.value
    }

    onMounted(() => {
        checkDbConnection()
    });
</script>

<template>
    <nav class="sticky top-0 w-[300px] flex flex-col pl-5 h-[100vh] z-[1]" id="principalNav">
        <div class="flex flex-row items-center gap-3 mb-4">
            <img class="w-[65px] h-[70px]" src="../assets/logo/logoTelamon.png" alt="Logo">
            <h1 class="font-bold text-[20px] text-white">{{ t.nav.title }}<span class="text-[#a0c4d4]">{{ t.nav.website }}</span></h1>
        </div>

        <NavBarLinks to="/home" :title=t.nav.home>
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-home"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
            </template>
        </NavBarLinks>

        <NavBarLinks to="/explore" :title=t.nav.explore>
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
            </template>
        </NavBarLinks>

        <NavBarLinks to="/notification" :title=t.nav.notification>
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-bell"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
            </template>
        </NavBarLinks>  

        <NavBarLinks to="/prueba" title="Laravel" backend>
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-database"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6m-8 0a8 3 0 1 0 16 0a8 3 0 1 0 -16 0" /><path d="M4 6v6a8 3 0 0 0 16 0v-6" /><path d="M4 12v6a8 3 0 0 0 16 0v-6" /></svg>
            </template>
            <span v-if="dbStatus === 'connected'" class="ml-auto w-3 h-3 bg-green-500 rounded-full" title="Conectado a DB"></span>
            <span v-else-if="dbStatus === 'error'" class="ml-auto w-3 h-3 bg-red-500 rounded-full" title="Error de conexión"></span>
            <span v-else class="ml-auto w-3 h-3 bg-yellow-500 rounded-full animate-pulse" title="Cargando..."></span>
        </NavBarLinks>

        <NavBarLinks to="/usuarios" title="Usuarios" backend>
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-database"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6m-8 0a8 3 0 1 0 16 0a8 3 0 1 0 -16 0" /><path d="M4 6v6a8 3 0 0 0 16 0v-6" /><path d="M4 12v6a8 3 0 0 0 16 0v-6" /></svg>
            </template>
            <span v-if="dbStatus === 'connected'" class="ml-auto w-3 h-3 bg-green-500 rounded-full" title="Conectado a DB"></span>
            <span v-else-if="dbStatus === 'error'" class="ml-auto w-3 h-3 bg-red-500 rounded-full" title="Error de conexión"></span>
            <span v-else class="ml-auto w-3 h-3 bg-yellow-500 rounded-full animate-pulse" title="Cargando..."></span>
        </NavBarLinks>

        <NavBarLinks to="/meeting" :title=t.nav.meeting>
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-message"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12" /></svg>
            </template>
        </NavBarLinks>

        <NavBarLinks to="/event" :title=t.nav.event>
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-event"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2l0 -12" /><path d="M16 3l0 4" /><path d="M8 3l0 4" /><path d="M4 11l16 0" /><path d="M8 15h2v2h-2l0 -2" /></svg>
            </template>
        </NavBarLinks>

        <router-link class="relative flex items-center gap-2.5 mb-5 mr-4 mt-auto rounded-xl text-[17px] font-medium py-3 px-4 text-white no-underline transition-all duration-200 ease-in-out hover:bg-[#406071] hover:cursor-pointer active:bg-[#406071] active:font-bold" id="profile" to="/profile">
            <img src="../assets/logo/logoTelamon.png" alt="" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
            {{ t.nav.profile }}
            <svg id="dots" @click.stop.prevent="toggleDotsPopup" class="absolute right-4 w-6 h-6 z-10 rounded-xl hover:bg-[#447c9a] transition-colors duration-200 cursor-pointer" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /></svg>
        </router-link>

        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-[5px]"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2">
            <div v-if="showPopup" @click.stop="showPopup = false" class="absolute right-10 bottom-24 bg-[#1d2b38] rounded-xl shadow-lg">
                <router-link class="flex gap-2.5 m-1 text-[17px] items-center py-3 px-4 rounded-xl text-white no-underline transition-all duration-200 ease-in-out font-medium hover:bg-[#406071] active:font-semibold" to="/">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M16 19h6" /><path d="M19 16v6" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4" /></svg>
                    {{ t.nav.addAccount }}
                </router-link>
                <router-link class="flex gap-2.5 m-1 text-[17px] items-center py-3 px-4 rounded-xl text-white no-underline transition-all duration-200 ease-in-out font-medium hover:bg-[#ef4444] active:font-semibold" to="/login">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 8v-2a2 2 0 0 1 2 -2h7a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2" /><path d="M15 12h-12l3 -3" /><path d="M6 15l-3 -3" /></svg>
                    {{ t.nav.logout }}
                </router-link>
            </div>
        </Transition>
    </nav>
</template>

<style scoped>
    #principalNav {
        background: linear-gradient(180deg, #1f5252 0%, #0f2828 100%);
        box-shadow: 5px 0px 20px rgba(0, 0, 0, 0.6);
    }
</style>