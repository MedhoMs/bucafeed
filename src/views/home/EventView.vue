<script setup>
    import { ref, onMounted, computed } from 'vue';
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    import CenterManagerCore from '../../components/center/modals/CenterManagerCore.vue'
    import EventCard from '../../components/events/EventCard.vue'
    
    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations()

    // Importamos directamente las variables reactivas del auth.js
    import { user as authUser, token as authToken } from '@/stores/auth'

    import PrimaryButton from '../../components/common/PrimaryButton.vue';

    const events = ref([]);
    const loading = ref(false)
    const activeModal = ref(null)
    const toast = ref({ show: false, msg: '', type: 'success' })
    const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

    const showToast = ({ msg, type = 'success' }) => {
        // Intentar traducir si el mensaje parece una clave de i18n
        const translated = msg.split('.').reduce((obj, key) => obj?.[key], t.value) || msg;
        toast.value = { show: true, msg: translated, type }
        setTimeout(() => toast.value.show = false, 3000)
    }
    
    // Usamos las variables importadas directamente
    const token = computed(() => authToken.value || localStorage.getItem('token'))

    const canCreate = computed(() => {
        return ['Admin', 'EI'].includes(authUser.value?.role);
    });

    const headers = computed(() => ({
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token.value}`
    }))

    const fetchEvents = async () => {
        try {
            const response = await fetch(`${apiBase}/events`);
            if (response.ok) {
                events.value = await response.json();
            }
        } catch (error) {
            console.error('Error fetching events:', error);
        }
    }

    onMounted(fetchEvents);

    const handleJoin = async (event) => {
        if (!token.value) {
            alert('Debes iniciar sesión para unirte a eventos');
            return;
        }
        try {
            const res = await fetch(`${apiBase}/events/${event.id}/join`, {
                method: 'POST',
                headers: headers.value
            });
            if (res.ok) {
                const data = await res.json();
                // Actualizar el evento localmente
                event.joined = data.joined;
                event.participants_count = data.count;
                showToast({ msg: event.joined ? 'Te has unido al evento' : 'Has abandonado el evento' });
            } else {
                const data = await res.json();
                showToast({ msg: data.message || 'Error', type: 'error' });
            }
        } catch (e) {
            showToast({ msg: 'Error de red', type: 'error' });
        }
    }
</script>

<template>
    <NavBar></NavBar>
    <main class="flex min-h-screen lg:pl-75">
        <section class="text-white w-full max-w-screen-2xl mx-auto px-6 lg:px-14 mb-20">
            <!-- Buscador largo y botón alineados al área de contenido -->
            <div class="flex flex-col md:flex-row items-center gap-4 md:gap-6 mt-8 md:mt-12 w-full mb-8 md:mb-14">
                <div class="flex-grow w-full max-w-3xl">
                    <SearchBar @update:filtered="events = $event"></SearchBar>
                </div>
                
                <!-- Botón Estilo Premium '+ NUEVO EVENTO' -->
                <PrimaryButton 
                    v-if="canCreate" 
                    text="Nuevo Evento" 
                    icon="plus"
                    @click="activeModal = 'event'" 
                />
            </div>

            <div id="mainBody" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 justify-items-center pb-20">
                <EventCard 
                    v-for="event in events" 
                    :key="event.id" 
                    :event="event" 
                    mode="public"
                    @join="handleJoin"
                />
            </div>
        </section>

        <!-- Modal de Creación-->
        <CenterManagerCore 
            v-if="activeModal"
            activeModal="event" 
            :apiBase="apiBase" 
            :headers="headers" 
            @close="activeModal = null" 
            @refresh="fetchEvents"
            @toast="showToast"
        />

        <!-- Toast Notification -->
        <div v-if="toast.show" 
            :class="['fixed top-6 left-1/2 -translate-x-1/2 z-[200] px-6 py-3 rounded-xl shadow-2xl font-semibold text-sm transition-all duration-300', 
                     toast.type === 'error' ? 'bg-red-500 text-white' : 'bg-emerald-500 text-white']">
            {{ toast.msg }}
        </div>
    </main>
</template>

<style scoped>

</style>
