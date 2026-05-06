<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    import UserAvatar from '../../components/common/UserAvatar.vue';
    import ForumManagerCore from '../../components/forum/ForumManagerCore.vue';
    import PageHeader from '@/components/common/PageHeader.vue';
    import PrimaryButton from '@/components/common/PrimaryButton.vue';
    import { ref, onMounted} from 'vue';
    import { user } from '@/stores/auth';
    import { useRouter } from 'vue-router';
    import { useApi } from '../../composables/useApi';

    import { useTranslations } from '../../composables/useTranslations'

    const { t } = useTranslations()
    const router = useRouter();
    const { get, del, loading: apiLoading } = useApi();
    
    const rawQuestions = ref([]);
    const questions = ref([]);
    const activeModal = ref(null); // 'question' | null
    const toast = ref({ show: false, msg: '', type: 'success' });

    const showToast = ({ msg, type = 'success' }) => {
        toast.value = { show: true, msg, type }
        setTimeout(() => toast.value.show = false, 3000)
    }

    const deleteQuestion = async (id) => {
        questions.value = questions.value.filter(q => q.id != id);
        rawQuestions.value = rawQuestions.value.filter(q => q.id != id);
        try {
            await del(`questions/${id}`);
            showToast({ msg: 'Pregunta eliminada' });
        } catch (e) {
            console.error("Error en servidor al eliminar:", e);
            showToast({ msg: 'Error al eliminar', type: 'error' });
        }
    };

    const loadQuestions = async () => {
        try {
            const result = await get('questions');
            const data = result.data || result;
            rawQuestions.value = data;
            questions.value = [...data];
        } catch (e) {
            console.error("Error cargando preguntas:", e);
        }
    };

    const getImageUrl = (path) => {
        if (!path) return null;
        if (path.startsWith('http')) return path;
        const baseSrc = import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace(/\/api$/, '') : 'http://localhost:8000';
        return baseSrc + (path.startsWith('/') ? path : '/' + path);
    };

    onMounted(loadQuestions);

</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-20 lg:pt-0 flex flex-col min-h-screen">
            <PageHeader 
                title="Foro de la Comunidad" 
                subtitle="Resuelve tus dudas y ayuda a otros estudiantes."
            >
                <template #search>
                    <SearchBar 
                        :items="rawQuestions" 
                        filterField="title"
                        @update:filtered="questions = $event"
                        class="w-full"
                    />
                </template>
                <template #actions>
                    <PrimaryButton 
                        text="Nueva Pregunta"
                        icon="plus"
                        @click="activeModal = 'question'"
                    />
                </template>
            </PageHeader>
    
            <section class="text-white w-full px-6 lg:px-14 mb-20">
                <div id="mainBody" class="flex flex-col gap-6 w-full">
                    <div v-if="apiLoading && questions.length === 0" class="text-white/40 italic py-10">Cargando preguntas del servidor...</div>
    
                    <div v-for="q in questions" :key="q.id" class="post-card text-left w-full">
                        <div class="flex gap-4 items-center mb-4">
                            <UserAvatar :user="q.user" class="shrink-0 shadow-lg bg-[#15202b]" />
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-white">
                                    {{ q.user?.name ?? 'Usuario' }} <span v-if="q.user?.last_name">{{ q.user.last_name }}</span>
                                </span>
                                <span class="text-xs text-white/40 uppercase tracking-widest">{{ q.user?.role_name || q.user?.role || 'Estudiante' }}</span>
                            </div>
                        </div>
                        <h2 class="post-title text-white">{{ q.title }}</h2>
                        <p class="text-white/80 mt-2 text-sm line-clamp-3 leading-relaxed">{{ q.content }}</p>
                        
                        <!-- Preview de Imagen -->
                        <div v-if="q.image" class="mt-4 rounded-xl overflow-hidden border border-white/5 bg-black/40 aspect-video w-full max-w-lg flex items-center justify-center">
                            <img :src="getImageUrl(q.image)" alt="Preview" class="w-full h-full object-contain" />
                        </div>
                        <div class="post-footer mt-4 flex items-center justify-between">
                            <router-link :to="'/question/' + q.id" class="responses-badge bg-white/5 hover:bg-white/10 border border-white/10 transition-all hover:scale-105 active:scale-95 px-4 py-2 rounded-xl cursor-pointer flex items-center gap-2 select-none group" title="Ver hilo completo">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white/60 group-hover:text-[#406071] transition-colors"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                                <span class="response-count text-sm text-white/80 font-bold">{{ q.answers ? q.answers.length : 0 }} Respuestas</span>
                                <span class="text-xs text-[#179cf0] font-black uppercase ml-2 opacity-0 group-hover:opacity-100 transition-opacity">Ver Hilo</span>
                            </router-link>
    
                            <button 
                                v-if="user?.role?.toLowerCase() === 'admin'"
                                @click.stop="deleteQuestion(q.id)"
                                class="p-2.5 rounded-xl bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-all active:scale-90"
                                title="Eliminar pregunta (Admin)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal Unificado para el Foro -->
    <ForumManagerCore 
        :activeModal="activeModal" 
        @close="activeModal = null" 
        @refresh="loadQuestions" 
        @toast="showToast"
    />

    <!-- Toast Notification -->
    <div v-if="toast.show" 
        :class="['fixed top-6 left-1/2 -translate-x-1/2 z-toast px-6 py-3 rounded-xl shadow-2xl font-semibold text-sm transition-all duration-300', 
                 toast.type === 'error' ? 'bg-red-500 text-white' : 'bg-emerald-500 text-white']">
        {{ toast.msg }}
    </div>
</template>

<style scoped>
    .post-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 20px;
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
    }

    .post-card:hover {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0.06) 100%);
    }

    .post-title {
        font-size: 20px;
        font-weight: bold;
    }
</style>
