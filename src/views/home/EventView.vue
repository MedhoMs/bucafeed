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
    import BaseModal from '@/components/modals/BaseModal.vue';
    import UnverifiedBanner from '@/components/common/UnverifiedBanner.vue';

    import { useTranslations } from '@/composables/useTranslations'
    import { useApi } from '@/composables/useApi';
    const { t } = useTranslations()
    const { get, del: apiDelete, loading: apiLoading } = useApi();

    // Importamos directamente las variables reactivas del auth.js
    import { user as authUser, token as authToken } from '@/stores/auth'

    const isUnverified = computed(() => authUser.value?.role === 'Student' && authUser.value?.is_verified === false)

    const rawEvents = ref([]);
    const events = ref([]);
    const router = useRouter();
    const loading = ref(false)
    const activeModal = ref(null)
    const showDeleteModal = ref(false)
    const eventToDelete = ref(null)
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
            showToast({ msg: t.value.events.loginRequired, type: 'error' });
            return;
        }
        // Block unverified students from joining
        if (isUnverified.value) {
            showToast({ msg: 'Tu cuenta está pendiente de verificación. No puedes unirte a eventos.', type: 'error' });
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
                showToast({ msg: event.joined ? t.value.events.joined : t.value.events.left });
            } else {
                const data = await res.json();
                showToast({ msg: data.message || 'Error', type: 'error' });
            }
        } catch (e) {
            showToast({ msg: t.value.events.networkError, type: 'error' });
        }
    }

    const goToDetails = (event) => {
        localStorage.setItem('selectedEvent', JSON.stringify(event));
        router.push({ name: 'event-details', params: { id: event.id } });
    }

    const confirmDelete = (event) => {
        eventToDelete.value = event;
        showDeleteModal.value = true;
    }

    const deleteEvent = async () => {
        if (!eventToDelete.value) return;
        try {
            await apiDelete(`events/${eventToDelete.value.id}`);
            showToast({ msg: t.value.events.deleted || 'Evento eliminado correctamente' });
            showDeleteModal.value = false;
            eventToDelete.value = null;
            fetchEvents(pagination.currentPage);
        } catch (error) {
            console.error('Error deleting event:', error);
            showToast({ msg: 'Error al eliminar el evento', type: 'error' });
        }
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
            </PageHeader>
    
            <section class="text-white w-full max-w-screen-2xl mx-auto px-6 lg:px-14 mb-20 flex-1 flex flex-col pt-6">
                <!-- Banner compacto superior si no está verificado -->
                <UnverifiedBanner 
                    v-if="isUnverified"
                    compact
                    message="Puedes ver los eventos disponibles, pero no podrás inscribirte hasta que tu centro verifique tu identidad."
                />

                <div id="mainBody" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 justify-items-center pb-20 flex-1">
                    <EventCard 
                        v-for="event in events" 
                        :key="event.id" 
                        :event="event" 
                        mode="public"
                        @details="goToDetails"
                        @delete="confirmDelete"
                    />
                </div>

                <!-- Paginación al final al centro -->
                <div v-if="events.length > 0" class="mt-auto py-10 flex justify-center w-full">
                    <Pagination 
                        :current-page="pagination.currentPage" 
                        :last-page="pagination.lastPage" 
                        @change="handlePageChange"
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

            <!-- Modal de Confirmación de Borrado -->
            <BaseModal 
                v-if="showDeleteModal" 
                :title="t.events.deleteTitle || '¿Eliminar evento?'" 
                :confirm-text="t.events.deleteConfirm || 'Eliminar'" 
                @close="showDeleteModal = false"
                @confirm="deleteEvent"
            >
                <p class="text-white/60 text-sm">
                    {{ t.events.deleteWarning || 'Esta acción no se puede deshacer. ¿Estás seguro de que quieres eliminar este evento?' }}
                </p>
            </BaseModal>
    
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