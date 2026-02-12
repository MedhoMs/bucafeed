<!--Componente de barra de navegacion-->
<script setup>
    import { onMounted, ref } from 'vue'

    import { useTranslations } from '../composables/useTranslations'
    const { t } = useTranslations() //Variable para llamar al archivo de traduccion
    import PostFormModal from '../components/PostFormModal.vue';
    import TranslateSelectorHome from '../components/TranslateSelectorHome.vue';

    const dbStatus = ref('loading') // loading, connected, error

    const checkDbConnection = async () => {
        try {
            const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
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

    const returnToView = () => {
        const backendBase = import.meta.env.VITE_BACKEND_URL || 'http://localhost:8000'
        window.location.href = `${backendBase}/prueba`
    }

    const showPostModal = ref(false)

    const togglePostModal = () => {
        showPostModal.value = !showPostModal.value
    }

    onMounted(() => {
        checkDbConnection()

        const prueba = document.getElementById('prueba');

        prueba.addEventListener('click', function () {
            returnToView();
        })

        let dots = document.getElementById("dots");
        let dotsPopup = document.getElementById("dots-popup");

        dots.addEventListener("click", function() {
            dotsPopup.classList.toggle("active");
        });

        document.addEventListener("click", function() {
            dotsPopup.classList.remove("active");
        })
    });
</script>

<template>
    <nav class="" id="principalNav">
        <div class="flex flex-row items-center gap-3 mb-4">
            <img class="w-[65px] h-[70px]" src="/logoTelamon.png" alt="Logo">
            <h1 class="font-bold text-[20px] text-white">{{ t.nav.title }}<span class="text-[#a0c4d4]">{{ t.nav.website }}</span></h1>
        </div>
        <router-link class="link" to="/home">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-home"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
            {{ t.nav.home }}
        </router-link>

        <router-link class="link" to="/explore">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
            {{ t.nav.explore }}
        </router-link>
        
        <router-link class="link" to="/notification">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-bell"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
            {{ t.nav.notification }}
        </router-link>    

        <a class="link" id="prueba">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-eye"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
            Prueba
        </a>
        
        <router-link class="link" to="/laravel">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-database"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6m-8 0a8 3 0 1 0 16 0a8 3 0 1 0 -16 0" /><path d="M4 6v6a8 3 0 0 0 16 0v-6" /><path d="M4 12v6a8 3 0 0 0 16 0v-6" /></svg>
            Prueba Laravel
            <span v-if="dbStatus === 'connected'" class="ml-auto w-3 h-3 bg-green-500 rounded-full" title="Conectado a DB"></span>
            <span v-else-if="dbStatus === 'error'" class="ml-auto w-3 h-3 bg-red-500 rounded-full" title="Error de conexiÃ³n"></span>
            <span v-else class="ml-auto w-3 h-3 bg-yellow-500 rounded-full animate-pulse" title="Cargando..."></span>
        </router-link>

        <router-link class="link" to="/question">
            <img src="../assets/question-icon.svg" width="28" height="28" alt="" class="w-7 h-7 invert brightness-0">
            {{ t.nav.question }}
        </router-link>
        
        <router-link class="link" to="/event">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-event"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2l0 -12" /><path d="M16 3l0 4" /><path d="M8 3l0 4" /><path d="M4 11l16 0" /><path d="M8 15h2v2h-2l0 -2" /></svg>
            {{ t.nav.event }}
        </router-link>
        
        <button class="link" id="post" @click="togglePostModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-circle-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M9 12h6" /><path d="M12 9v6" /></svg>
            {{ t.nav.post }}
        </button>

        <PostFormModal v-if="showPostModal" @close="showPostModal = false"/>

        <TranslateSelectorHome/>
                    
        <router-link class="link" id="profile" to="/profile">
            <img src="../assets/logo/logoTelamon.png" alt="" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
            {{ t.nav.profile }}
            <svg id="dots" @click.stop.prevent="toggleDotsPopup" class="rounded-xl hover:bg-[#43768f] transition-colors duration-200 cursor-pointer | icon icon-tabler icons-tabler-outline icon-tabler-dots-vertical" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /></svg>
        </router-link>
                                                                  <!--   PROVISIONAL   -->
        <div id="dots-popup" class="relative bottom-[935px]">
            <div class="absolute right-[40px] top-[720px] bg-[#1d2b38] rounded-xl">
                <router-link class="user-options" to="/"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M16 19h6" /><path d="M19 16v6" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4" /></svg>{{ t.nav.addAccount }}</router-link>
                <router-link id="logout" class="user-options" to="/login"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-logout-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 8v-2a2 2 0 0 1 2 -2h7a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2" /><path d="M15 12h-12l3 -3" /><path d="M6 15l-3 -3" /></svg>{{ t.nav.logout }}</router-link>
            </div>
        </div>                                                          
    </nav>
</template>

<style scoped>
    #principalNav {
        position: relative;
        width: 300px;
        display: flex;
        flex-direction: column;
        margin-top: 20px;
        padding-left: 40px;
        min-height: 90vh;
        z-index: 1;
    }

    .link {
        position: relative;
        display: flex;
        flex-direction: row;
        gap: 10px;
        margin-bottom: 20px;
        font-size: 17px;
        align-items: center;
        padding: 12px 16px;
        border-radius: 12px;
        margin-right: 16px;
        color: white;
        text-decoration: none;
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .link:hover {
        background-color: #2a4a5a;
        color: white;
        cursor: pointer;
    }

    .link.router-link-active {
        background-color: #2a4a5a;
        color: white;
        font-weight: 600;
    }

    .user-options {
        display: flex;
        flex-direction: row;
        gap: 10px;
        margin: 5px;
        font-size: 17px;
        align-items: center;
        padding: 12px 16px;
        border-radius: 12px;
        color: white;
        text-decoration: none;
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .user-options:hover {
        background-color: #2a4a5a;
        color: white;
    }

    .user-options.router-link-active {
        background-color: #2a4a5a;
        color: white;
        font-weight: 600;
    }

    #post {
        display: flex;
        background-color: #2a4a5a;
        justify-content: center;
        padding: 14px 20px;
        border-radius: 30px;
        color: white;
        font-weight: 600;
        margin-top: 8px;
        margin-bottom: 24px;
    }

    #post:hover {
        background-color: #3a5a6a;
        color: white;
    }

    #profile {
        position: relative;
        margin-top: auto;
        margin-bottom: 20px;
    }

    #dots {
        position: absolute;
        right: 16px;
        width: 24px;
        height: 24px;
        stroke: currentColor;
        z-index: 10;
    }

    #dots-popup {
        opacity: 0;
        transform: translateY(5px);
        pointer-events: none;
        transition: opacity 200ms ease, transform 200ms ease;
    }

    #dots-popup.active {
        opacity: 1;
        transform: translateY(0);
        pointer-events: all;
    }

    #logout:hover {
        background-color: rgb(239 68 68);
    }
</style>