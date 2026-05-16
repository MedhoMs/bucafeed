<!--Componente de barra de navegacion-->
<script setup>
    import NavBarLinks from './NavBarLinks.vue'
    import { onMounted, onUnmounted, ref, computed } from 'vue'
    import { useTranslations } from '@/composables/useTranslations'
    import UserAvatar from '@/components/common/UserAvatar.vue'
    import defaultLogo from '@/assets/logo/logoTelamon.png'

    const getImageUrl = (path) => {
        if (!path) return null;
        if (path.startsWith('http')) return path;
        const base = import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace(/\/api$/, '') : 'http://localhost:8000';
        return `${base}${path.startsWith('/') ? '' : '/'}${path}`;
    }

    const { t } = useTranslations() //Variable para llamar al archivo de traduccion

    import { user, logout } from '@/stores/auth' //Import que contiene toda la info del usuario logueado
    import { unreadCount, fetchUnreadCount } from '@/stores/notifications'
    import { useRouter } from 'vue-router'
    import { useSocket } from '@/composables/useSocket'

    const { setupSocket } = useSocket()

    const router = useRouter()

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

    const showPopup = ref(false)

    const toggleDotsPopup = () => {
        showPopup.value = !showPopup.value
    }

    //Funcion para esconder el dropup del perfil
    function hidePopup() {
        if (showPopup.value === true) {
            showPopup.value = false
        }
    }

    //Un onmounted para que al hacer click en cualquier parte del documento, se active la funcion de esconder
    onMounted(() => {
        document.addEventListener("click", hidePopup);
    });

    //Y destruyo en onmounted para que no se duplique infinitamente una vez se haya llamado
    onUnmounted(() => {
        document.removeEventListener("click", hidePopup);
        if (unreadInterval) clearInterval(unreadInterval);
    });

    const menu = ref(false)
    const isDesktop = ref(window.innerWidth >= 1024)
 
    let unreadInterval = null

    onMounted(() => {
        checkDbConnection()
        setupSocket(user.value) // Iniciar socket global
        fetchUnreadCount()
        unreadInterval = setInterval(fetchUnreadCount, 30000)
        const handleResize = () => {
            isDesktop.value = window.innerWidth >= 1024
            if (isDesktop.value) menu.value = false
        }
        window.addEventListener('resize', handleResize)
    })
 
    function activeMenu() {
        menu.value = !menu.value
    }
 
    function closeMenu() {
        menu.value = false
    }

    const props = defineProps({
        hideHamburger: { type: Boolean, default: false }
    })

    const handleLogout = () => {
        logout()
        router.push('/login')
    }

    const handleNewAccount = () => {
        logout()
        router.push('/')
    }

    defineExpose({ activeMenu })

    const isUnverified = computed(() => user.value?.role === 'Student' && user.value?.is_verified === false)
</script>
 
<template>
    <!--Hamburger menu flotante premium-->
    <button v-show="!menu && !hideHamburger" @click="activeMenu()" 
        class="lg:hidden fixed top-5 left-6 z-[100] bg-accent-normal/80 backdrop-blur-md p-2 rounded-xl border border-white/10 text-white shadow-2xl active:scale-95 transition-all flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg>
    </button>
 
    <!--Overlay oscuro-->
    <Transition
Enter
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="menu" @click="closeMenu()" class="lg:hidden fixed inset-0 z-40 bg-black/60"></div>
    </Transition>
 
    <!--Sidebar siempre visible en desktop, toggle en móvil-->
    <Transition
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="-translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-300 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="-translate-x-full">
        <nav v-if="menu || true" v-show="menu || isDesktop" class="fixed top-0 left-0 flex flex-col w-75 h-screen px-5 nav-sidebar shrink-0 overflow-y-auto overflow-x-hidden">
 
            <div class="flex flex-row items-center gap-3 mb-4 mt-4 shrink-0">
                <img class="w-16.25 h-17.5" src="@/assets/logo/logoTelamon.png" alt="Logo">
                <h1 class="font-bold text-xl text-white">
                    {{ t.nav.title }}<span class="text-[#a0c4d4]">{{ t.nav.website }}</span>
                </h1>
                <!--Botón X Material-->
                <button @click="closeMenu()" class="lg:hidden ml-auto p-1.5 rounded-lg hover:bg-white/10 text-white/70 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg>
                </button>
            </div>

 
            <section class="flex flex-col h-full">
                <NavBarLinks to="/home" :title=t.nav.home>
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                    </template>
                </NavBarLinks>

                <NavBarLinks to="/foro" :title=t.nav.foro>
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M21 14l-3 -3h-7a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1h9a1 1 0 0 1 1 1v10" /><path d="M14 15v2a1 1 0 0 1 -1 1h-7l-3 3v-10a1 1 0 0 1 1 -1h2" /></svg>
                    </template>
                </NavBarLinks>
 
                <NavBarLinks to="/explore" :title=t.nav.explore>
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                    </template>
                </NavBarLinks>
 
                <NavBarLinks to="/notification" :title="t.nav.notification">
                    <template #icon>
                        <div class="relative inline-flex">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
                            <span v-if="unreadCount > 0" class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow-lg">
                                {{ unreadCount > 99 ? '99+' : unreadCount }}
                            </span>
                        </div>
                    </template>
                </NavBarLinks>
 
                <NavBarLinks v-if="user?.role?.toLowerCase() === 'admin' || user?.role_name?.toLowerCase() === 'admin' || user?.role?.toLowerCase() === 'administrador'" to="/admin" :title="t.nav.adminPanel" backend>
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="6" cy="10" r="2"/><line x1="6" y1="4" x2="6" y2="8"/><line x1="6" y1="12" x2="6" y2="20"/><circle cx="12" cy="16" r="2"/><line x1="12" y1="4" x2="12" y2="14"/><line x1="12" y1="18" x2="12" y2="20"/><circle cx="18" cy="7" r="2"/><line x1="18" y1="4" x2="18" y2="5"/><line x1="18" y1="9" x2="18" y2="20"/></svg>
                    </template>
                    <span v-if="dbStatus === 'connected'" class="ml-auto w-3 h-3 bg-success-normal rounded-full" title="Conectado a DB"></span>
                    <span v-else-if="dbStatus === 'error'" class="ml-auto w-3 h-3 bg-error-normal rounded-full" title="Error de conexión"></span>
                    <span v-else class="ml-auto w-3 h-3 bg-warning-normal rounded-full animate-pulse" title="Cargando..."></span>
                </NavBarLinks>
 
                <NavBarLinks v-if="user?.role?.toLowerCase() === 'ei'" to="/mi-centro" :title="t.nav.myCenter">
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M5 21v-14l8 -4v18" /><path d="M19 21v-10l-6 -4" /><path d="M9 9l0 .01" /><path d="M9 12l0 .01" /><path d="M9 15l0 .01" /><path d="M9 18l0 .01" /></svg>
                    </template>
                </NavBarLinks>
 
                <NavBarLinks to="/meeting" :title=t.nav.meeting>
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12" /></svg>
                    </template>
                </NavBarLinks>

                <NavBarLinks to="/private-chat" :title=t.nav.privateChat>
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 -960 960 960" fill="#e3e3e3"><path d="M680-240v-80h200v80H680Zm-80-200v-80h280v80H600Zm-80-200v-80h360v80H520ZM235-515q-35-35-35-85t35-85q35-35 85-35t85 35q35 35 35 85t-35 85q-35 35-85 35t-85-35ZM80-240v-76q0-21 10-40t28-30q45-27 95.5-40.5T320-440q56 0 106.5 13.5T522-386q18 11 28 30t10 40v76H80Zm160-110q-39 10-74 30h308q-35-20-74-30t-80-10q-41 0-80 10Zm108.5-221.5Q360-583 360-600t-11.5-28.5Q337-640 320-640t-28.5 11.5Q280-617 280-600t11.5 28.5Q303-560 320-560t28.5-11.5ZM320-600Zm0 280Z"/></svg>
                    </template>
                </NavBarLinks>
 
                <NavBarLinks to="/event" :title=t.nav.event>
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2l0 -12" /><path d="M16 3l0 4" /><path d="M8 3l0 4" /><path d="M4 11l16 0" /><path d="M8 15h2v2h-2l0 -2" /></svg>
                    </template>
                </NavBarLinks>
 
 
                    <router-link
                        class="relative flex items-center gap-2.5 mb-5 mr-4 mt-auto rounded-xl text-base font-medium py-3 px-4 text-white no-underline transition-all duration-200 ease-in-out hover:bg-secondary-normal hover:cursor-pointer active:bg-secondary-normal active:font-bold"
                        id="profile" to="/profile">
                        <UserAvatar :user="user" size="w-10 h-10" class="border-2 shadow-xs" />
                        <div class="flex flex-col min-w-0">
                            <!-- Indicador de pendiente de verificación -->
                            <span v-if="isUnverified" class="text-amber-300">
                                <p class="truncate">{{ user ? user.name : 'Usuario' }}</p>
                            </span>
                            <p v-else class="truncate">{{ user ? user.name : 'Usuario' }}</p>
                        </div>
                        <svg id="dots" @click.stop.prevent="toggleDotsPopup" class="absolute right-4 w-6 h-6 z-10 rounded-xl hover:bg-secondary-normal-hover transition-colors duration-200 cursor-pointer" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /></svg>
                    </router-link>
 
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 translate-y-[5px]"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-2">
                    <div v-if="showPopup" class="absolute right-10 bottom-24 bg-[#1d2b38] rounded-xl shadow-lg">
                        <router-link class="flex gap-2.5 m-1 text-base items-center py-3 px-4 rounded-xl text-white no-underline transition-all duration-200 ease-in-out font-medium hover:bg-[#406071] active:font-semibold" :to="'/profile/' + user?.id">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                            {{ t.nav.profile }}
                        </router-link>
                        <router-link @click="handleNewAccount" class="flex gap-2.5 m-1 text-base items-center py-3 px-4 rounded-xl text-white no-underline transition-all duration-200 ease-in-out font-medium hover:bg-secondary-normal active:font-semibold" to="/">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M16 19h6" /><path d="M19 16v6" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4" /></svg>
                            {{ t.nav.addAccount }}
                        </router-link>
                        <div @click="handleLogout" class="flex cursor-pointer gap-2.5 m-1 text-base items-center py-3 px-4 rounded-xl text-white no-underline transition-all duration-200 ease-in-out font-medium hover:bg-error-normal active:font-semibold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 8v-2a2 2 0 0 1 2 -2h7a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2" /><path d="M15 12h-12l3 -3" /><path d="M6 15l-3 -3" /></svg>
                            {{ t.nav.logout }}
                        </div>
                    </div>
                </Transition>
            </section>
        </nav>
    </Transition>
</template>
 
<style scoped>

</style>