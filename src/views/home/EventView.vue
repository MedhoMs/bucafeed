<script setup>
    import { ref, onMounted, computed } from 'vue';
    import { useRouter } from 'vue-router';
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    import CenterManagerCore from '../../components/center/modals/CenterManagerCore.vue'
    import EventCard from '../../components/events/EventCard.vue'
    import PageHeader from '@/components/common/PageHeader.vue';

    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations()

    // Importamos directamente las variables reactivas del auth.js
    import { user as authUser, token as authToken } from '@/stores/auth'

    const rawEvents = ref([]);
    const events = ref([]);
    const router = useRouter();
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
                const data = await response.json();
                rawEvents.value = data;
                events.value = [...data];
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

    const goToDetails = (event) => {
        localStorage.setItem('selectedEvent', JSON.stringify(event));
        router.push({ name: 'event-details', params: { id: event.id } });
    }
</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-20 lg:pt-0 flex flex-col min-h-screen">
            <PageHeader 
                title="Eventos Académicos" 
                subtitle="Participa en las actividades y charlas de tu centro."
            >
                <template #search>
                    <SearchBar 
                        :items="rawEvents" 
                        filterField="title"
                        @update:filtered="events = $event"
                        class="w-full"
                    />
                </template>
                <template #actions>
                    <button v-if="canCreate" 
                        @click="activeModal = 'event'"
                        class="w-full md:w-auto bg-[#406071] hover:bg-[#507a8f] text-white text-[11px] font-black uppercase tracking-[0.2em] px-10 py-4.5 rounded-[22px] transition-all cursor-pointer active:scale-95 shadow-xl shadow-cyan-900/10 flex items-center justify-center gap-3 group border border-white/5 shrink-0">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" class="group-hover:rotate-90 transition-transform duration-500"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Nuevo Evento</span>
                    </button>
                </template>
            </PageHeader>
    
            <section class="text-white w-full max-w-screen-2xl mx-auto px-6 lg:px-14 mb-20">
    
                <div id="mainBody" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 justify-items-center pb-20">
                    <EventCard 
                        v-for="event in events" 
                        :key="event.id" 
                        :event="event" 
                        mode="public"
                        @details="goToDetails"
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
    </div>
</template>

<style scoped>
</style>