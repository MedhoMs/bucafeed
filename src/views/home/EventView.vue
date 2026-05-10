<script setup>
    import { ref, onMounted, computed, reactive } from 'vue';
    import { useRouter } from 'vue-router';
    import NavBar from '@/components/NavBar/NavBar.vue';
    import SearchBar from '@/components/SearchBar.vue';
    import CenterManagerCore from '@/components/center/modals/CenterManagerCore.vue'
    import EventCard from '@/components/events/EventCard.vue'
    import PageHeader from '@/components/common/PageHeader.vue';
    import Pagination from '@/components/common/Pagination.vue';
    import PrimaryButton from '@/components/common/PrimaryButton.vue';

    import { useTranslations } from '@/composables/useTranslations'
    import { useApi } from '@/composables/useApi';
    const { t } = useTranslations()
    const { get, loading: apiLoading } = useApi();

    // Importamos directamente las variables reactivas del auth.js
    import { user as authUser, token as authToken } from '@/stores/auth'

    const rawEvents = ref([]);
    const events = ref([]);
    const router = useRouter();
    const loading = ref(false)
    const activeModal = ref(null)
    const toast = ref({ show: false, msg: '', type: 'success' })
    const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

    const pagination = reactive({
        currentPage: 1,
        lastPage: 1
    });

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

    const fetchEvents = async (page = 1) => {
        try {
            const result = await get(`events?page=${page}`);
            
            if (result.data && Array.isArray(result.data)) {
                rawEvents.value = result.data;
                events.value = [...result.data];
                pagination.currentPage = result.current_page;
                pagination.lastPage = result.last_page;
            } else {
                const data = result.data || result;
                rawEvents.value = data;
                events.value = [...data];
                pagination.currentPage = 1;
                pagination.lastPage = 1;
            }
        } catch (error) {
            console.error('Error fetching events:', error);
        }
    }

    const handlePageChange = (page) => {
        fetchEvents(page);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    onMounted(fetchEvents);

    const handleJoin = async (event) => {
        if (!token.value) {
            showToast({ msg: t.events.loginRequired, type: 'error' });
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
                showToast({ msg: event.joined ? t.events.joined : t.events.left });
            } else {
                const data = await res.json();
                showToast({ msg: data.message || 'Error', type: 'error' });
            }
        } catch (e) {
            showToast({ msg: t.events.networkError, type: 'error' });
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
                :title="t.events.title" 
                :subtitle="t.events.subtitle"
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
                    <PrimaryButton 
                        v-if="canCreate" 
                        :text="t.events.newEvent" 
                        icon="plus" 
                        @click="activeModal = 'event'"
                    />
                </template>
                <template #bottom>
                    <Pagination 
                        v-if="events.length > 0"
                        :current-page="pagination.currentPage" 
                        :last-page="pagination.lastPage" 
                        @change="handlePageChange"
                    />
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
                :class="['fixed top-6 left-1/2 -translate-x-1/2 z-[200] px-6 py-3 rounded-xl shadow-2xl font-black uppercase tracking-widest text-xs transition-all duration-300 border border-white/10', 
                         toast.type === 'error' ? 'bg-error-normal text-white' : 'bg-secondary-normal text-white']">
                {{ toast.msg }}
            </div>
        </main>
    </div>
</template>

<style scoped>
</style>