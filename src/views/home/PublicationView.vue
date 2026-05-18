<script setup>
/**
 * PublicationView.vue - Vista de Publicaciones para los usuarios de TelamoNet
 */
import { ref, onMounted, computed, reactive } from 'vue';
import { useRouter } from 'vue-router';
import NavBar from '@/components/NavBar/NavBar.vue';
import SearchBar from '@/components/SearchBar.vue';
import CenterManagerCore from '@/components/center/modals/CenterManagerCore.vue'
import PublicationCard from '@/components/publications/PublicationCard.vue'
import PageHeader from '@/components/common/PageHeader.vue';
import Pagination from '@/components/common/Pagination.vue';
import PrimaryButton from '@/components/common/PrimaryButton.vue';
import BaseModal from '@/components/modals/BaseModal.vue';
import UnverifiedBanner from '@/components/common/UnverifiedBanner.vue';

import { useTranslations } from '@/composables/useTranslations'
import { useApi } from '@/composables/useApi';
const { t } = useTranslations()
const router = useRouter();
const { get, del: apiDelete } = useApi();

import { user as authUser, token as authToken } from '@/stores/auth'

const isUnverified = computed(() => ['Student', 'Teacher'].includes(authUser.value?.role) && authUser.value?.is_verified === false)

const rawPublications = ref([]);
const publications = ref([]);
const loading = ref(false)
const activeModal = ref(null)
const showDeleteModal = ref(false)
const publicationToDelete = ref(null)
const selectedPublication = ref(null)
const toast = ref({ show: false, msg: '', type: 'success' })
const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const pagination = reactive({
    currentPage: 1,
    lastPage: 1
});

const showToast = ({ msg, type = 'success' }) => {
    const translated = msg.split('.').reduce((obj, key) => obj?.[key], t.value) || msg;
    toast.value = { show: true, msg: translated, type }
    setTimeout(() => toast.value.show = false, 3000)
}

const token = computed(() => authToken.value || localStorage.getItem('token'))

const canCreate = computed(() => {
    return ['Admin', 'EI'].includes(authUser.value?.role);
});

const headers = computed(() => ({
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token.value}`
}))

const fetchPublications = async (page = 1) => {
    loading.value = true;
    try {
        const result = await get(`publications?page=${page}`);
        
        if (result.data && Array.isArray(result.data)) {
            rawPublications.value = result.data;
            publications.value = [...result.data];
            pagination.currentPage = result.current_page;
            pagination.lastPage = result.last_page;
        } else {
            const data = result.data || result;
            rawPublications.value = data;
            publications.value = [...data];
            pagination.currentPage = 1;
            pagination.lastPage = 1;
        }
    } catch (error) {
        console.error('Error fetching publications:', error);
    } finally {
        loading.value = false;
    }
}

const handlePageChange = (page) => {
    fetchPublications(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(fetchPublications);

const goToDetails = (pub) => {
    router.push({ name: 'publication-details', params: { id: pub.id } });
}

const confirmDelete = (pub) => {
    publicationToDelete.value = pub;
    showDeleteModal.value = true;
}

const deletePublication = async () => {
    if (!publicationToDelete.value) return;
    try {
        // Enviar petición delete al endpoint público si tenemos permisos de admin o global, o usar apiDelete
        await apiDelete(`publications/${publicationToDelete.value.id}`);
        showToast({ msg: t.value.publications?.deleted || 'Publicación eliminada correctamente' });
        showDeleteModal.value = false;
        publicationToDelete.value = null;
        fetchPublications(pagination.currentPage);
    } catch (error) {
        console.error('Error deleting publication:', error);
        showToast({ msg: t.value.publications?.deleteError || 'Error al eliminar la publicación', type: 'error' });
    }
}
</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-20 lg:pt-0 flex flex-col min-h-screen">
            <PageHeader 
                :title="t.publications?.title || 'Publicaciones'" 
                :subtitle="t.publications?.subtitle || 'Entérate de las últimas novedades y noticias de los centros educativos.'"
            >
                <template #search>
                    <SearchBar 
                        :items="rawPublications" 
                        filterField="title"
                        @update:filtered="publications = $event"
                        class="w-full"
                    />
                </template>
                <template #actions>
                    <PrimaryButton class="cursor-pointer" 
                        v-if="canCreate" 
                        :text="t.publications?.newPublication || 'Nueva Publicación'" 
                        icon="plus" 
                        @click="activeModal = 'publication'"
                    />
                </template>
            </PageHeader>
    
            <section class="text-white w-full max-w-screen-2xl mx-auto px-6 lg:px-14 mb-20 flex-1 flex flex-col pt-6">
                <!-- Banner compacto superior si no está verificado -->
                <UnverifiedBanner 
                    v-if="isUnverified"
                    compact
                    :message="t.publications?.exploreUnverifiedBanner || 'Puedes ver las publicaciones, pero no podrás publicar hasta que tu centro verifique tu identidad.'"
                />

                <div v-if="loading" class="flex items-center justify-center py-20 flex-1">
                    <div class="w-10 h-10 border-4 border-cyan-500 border-t-transparent rounded-full animate-spin"></div>
                </div>

                <div v-else-if="publications.length > 0" id="mainBody" class="flex flex-col gap-6 w-full pb-20 flex-1">
                    <PublicationCard 
                        v-for="pub in publications" 
                        :key="pub.id" 
                        :publication="pub" 
                        mode="public"
                        @details="goToDetails"
                        @delete="confirmDelete"
                    />
                </div>

                <!-- Estado vacío -->
                <div v-else class="text-center py-20 bg-white/5 rounded-3xl border border-dashed border-white/10 flex-1 flex flex-col justify-center">
                    <div class="w-16 h-16 bg-[#406071]/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-white/5">
                         <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#406071" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line><line x1="3" y1="15" x2="21" y2="15"></line></svg>
                    </div>
                    <p class="text-white/20 text-[10px] font-black uppercase tracking-[0.2em]">No hay publicaciones activas</p>
                </div>
 
                <!-- Paginación al final al centro -->
                <div v-if="publications.length > 0 && !loading" class="mt-auto py-10 flex justify-center w-full">
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
                activeModal="publication" 
                :apiBase="apiBase" 
                :headers="headers" 
                @close="activeModal = null" 
                @refresh="fetchPublications"
                @toast="showToast"
            />

            <!-- Modal de Confirmación de Borrado -->
            <BaseModal 
                v-if="showDeleteModal" 
                :title="t.publications?.deleteTitle || '¿Eliminar publicación?'" 
                :confirm-text="t.publications?.deleteConfirm || 'Eliminar'" 
                @close="showDeleteModal = false"
                @confirm="deletePublication"
            >
                <p class="text-white/60 text-sm">
                    {{ t.publications?.deleteWarning || 'Esta acción no se puede deshacer. ¿Estás seguro de que quieres eliminar esta publicación?' }}
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
