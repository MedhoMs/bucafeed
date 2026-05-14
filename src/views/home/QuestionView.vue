<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';

    import SideBar from '../../components/SideBar.vue';
    import UserAvatar from '../../components/common/UserAvatar.vue';
    import EmojiPicker from '../../components/common/EmojiPicker.vue';

    import BaseModal from '@/components/modals/BaseModal.vue';
    import PageHeader from '@/components/common/PageHeader.vue';
    import Pagination from '../../components/common/Pagination.vue';
    import TextChatBar from '@/components/TextChatBar.vue';
    import { ref, onMounted, watch, reactive } from 'vue';
    import { useRoute, useRouter } from 'vue-router';
    import { user } from '@/stores/auth';

    import { useTranslations } from '../../composables/useTranslations'
    import { useApi } from '../../composables/useApi';
    const { t } = useTranslations()
    const { get, post, del, loading: apiLoading } = useApi();
    const router = useRouter();

    const getImageUrl = (path) => {
        if (!path) return null;
        if (path.startsWith('http')) return path;
        const baseSrc = import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace(/\/api$/, '') : 'http://localhost:8000';
        return baseSrc + (path.startsWith('/') ? path : '/' + path);
    };

    const textareaRef = ref(null);
    
    const route = useRoute();
    const question = ref(null);
    const answers = ref([]);
    const pagination = reactive({
        currentPage: 1,
        lastPage: 1,
        total: 0
    });
    const loading = ref(true);
    const loadingAnswers = ref(false);
    const submitting = ref(false);
    const isExpanded = ref(false);
    const newAnswer = ref("");
    const newImage = ref(null);
    const showConfirmModal = ref(false);
    const idToDelete = ref(null);
    const typeToDelete = ref(null);
    const showEmojiPicker = ref(false);

    const triggerDelete = (type, id) => {
        typeToDelete.value = type;
        idToDelete.value = id;
        showConfirmModal.value = true;
    };

    const confirmDelete = async () => {
        if (!idToDelete.value || !typeToDelete.value) return;
        await handleDelete(typeToDelete.value, idToDelete.value);
        showConfirmModal.value = false;
        idToDelete.value = null;
        typeToDelete.value = null;
    };

    const onSelectEmoji = (emoji) => {
        newAnswer.value += emoji.i;
    };

    watch(newAnswer, () => {
        const el = textareaRef.value;
        if (el) {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        }
    });

    const loadQuestion = async (id) => {
        try {
            const data = await get(`questions/${id}`);
            question.value = data.question || data;
            // Initially load the first page of answers
            await loadAnswers(id, 1);
        } catch (e) {
            console.error("Error cargando pregunta", e);
        } finally {
            loading.value = false;
        }
    };

    const loadAnswers = async (questionId, page = 1) => {
        loadingAnswers.value = true;
        try {
            const result = await get(`answers?question_id=${questionId}&page=${page}`);
            
            if (result.data && Array.isArray(result.data)) {
                answers.value = result.data;
                pagination.currentPage = result.current_page;
                pagination.lastPage = result.last_page;
                pagination.total = result.total;
            } else {
                // Fallback
                answers.value = result.data || result || [];
                pagination.currentPage = 1;
                pagination.lastPage = 1;
                pagination.total = answers.value.length;
            }
        } catch (e) {
            console.error("Error cargando respuestas", e);
        } finally {
            loadingAnswers.value = false;
        }
    };

    const handlePageChange = (page) => {
        if (question.value) {
            loadAnswers(question.value.id, page);
            // Scroll to the answers section
            const answersHeading = document.getElementById('answers-title');
            if (answersHeading) {
                answersHeading.scrollIntoView({ behavior: 'smooth' });
            }
        }
    };

    const handleDelete = async (type, id) => {
        if (type === 'answer') {
            answers.value = answers.value.filter(a => a.id != id);
            pagination.total--;
        }

        try {
            const endpoint = type === 'question' ? `questions/${id}` : `answers/${id}`;
            await del(endpoint);
            
            if (type === 'question') {
                router.push('/foro');
            }
        } catch (e) {
            console.error(`Error en servidor al eliminar ${type}:`, e);
        }
    };

    const handleSendMessage = async (msgObj) => {
        if (submitting.value) return;
        
        submitting.value = true;
        try {
            const formData = new FormData();
            formData.append('question_id', question.value.id);
            formData.append('user_id', user.value?.id);
            
            if (msgObj.type === 'text') {
                formData.append('content', msgObj.content);
            } else {
                // Fetch the blob from the object URL created by TextChatBar
                const response = await fetch(msgObj.content);
                const blob = await response.blob();
                formData.append('image', blob, msgObj.fileName || 'upload');
            }

            await post('answers', formData);

            // Reload the first page of answers to show the new one
            await loadAnswers(question.value.id, 1); 
        } catch (e) {
            console.error("Error al enviar respuesta", e);
        } finally {
            submitting.value = false;
        }
    };

    onMounted(() => {
        if (route.params.id) {
            loadQuestion(route.params.id);
        } else {
            loading.value = false;
        }
    });

    watch(() => route.params.id, (newId) => {
        if (newId) loadQuestion(newId);
    });
</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-20 lg:pt-8 flex flex-col min-h-screen relative">
            <section class="text-white w-full px-4 lg:px-14 flex-1 flex flex-col">
    
                <div id="mainTrending" class="flex flex-col items-center w-full flex-1">
                    
                    <div v-if="apiLoading && !question" class="text-white/40 italic py-10 mt-20">{{ t.forum.loadingThread }}</div>
                    
                    <div v-else-if="!apiLoading && !question" class="text-white/40 italic py-10 mt-20 text-2xl font-bold">
                        {{ t.forum.notFound }}
                    </div>
    
                    <div v-else class="w-full">
                        <!-- Tarjeta de la Pregunta Principal -->
                        <div class="post-card text-left bg-linear-to-br from-white/5 to-white/2 border border-white/10 rounded-2xl p-7 relative mb-8">
                            <div class="flex gap-4 items-center mb-4 border-b border-white/5 pb-4">
                                    <UserAvatar :user="question.user" size="w-12 h-12" class="shrink-0 shadow-lg bg-[#15202b]" />
                                <div class="flex flex-col">
                                    <span class="text-base font-bold text-white">
                                        {{ question.user?.name ?? 'Usuario' }} <span v-if="question.user?.last_name">{{ question.user.last_name }}</span>
                                    </span>
                                    <span class="text-xs text-white/40 uppercase tracking-widest">{{ question.user?.role_name || question.user?.role || 'Estudiante' }}</span>
                                </div>
                                <!-- Botón Eliminar Question (Admin) -->
                                <button 
                                    v-if="user?.role?.toLowerCase() === 'admin'"
                                    @click="triggerDelete('question', question.id)"
                                    class="ml-auto p-2 rounded-xl bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-all active:scale-90"
                                    :title="t.forum.deleteQuestion"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                </button>
                            </div>
                            <h1 class="text-4xl lg:text-5xl font-black text-white mb-4 tracking-tighter">{{ question.title }}</h1>
                            <p class="text-white/80 text-base leading-relaxed whitespace-pre-wrap">{{ question.content }}</p>
                            
                            <div v-if="question.image" class="mt-6 rounded-2xl overflow-hidden border border-white/10 bg-black/20 flex items-center justify-center">
                                <img :src="getImageUrl(question.image)" alt="Imagen de apoyo" class="w-full h-auto max-h-[700px] object-contain hover:scale-[1.01] transition-transform duration-700" />
                            </div>
                            
                            <div v-if="question.tags && question.tags.length" class="mt-6 pt-4 border-t border-white/5 flex flex-wrap gap-2">
                                 <div v-for="tag in question.tags" :key="tag.id" class="px-3 py-1 rounded-full bg-accent-normal text-white border border-white/10 text-xs font-bold shadow-xs">
                                      {{ tag.name }}
                                 </div>
                            </div>
                        </div>

                        <!-- Sección de Respuestas -->
                        <h3 id="answers-title" class="text-xl font-bold text-white mb-6 flex items-center gap-2 px-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success-normal"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M21 14l-3 -3h-7a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1h9a1 1 0 0 1 1 1v10" /><path d="M14 15v2a1 1 0 0 1 -1 1h-7l-3 3v-10a1 1 0 0 1 1 -1h2" /></svg>
                            {{ t.forum.responses }} ({{ pagination.total }})
                        </h3>
    
                        <div class="flex flex-col gap-4">
                            <div v-if="loadingAnswers && answers.length === 0" class="text-white/40 italic py-10">{{ t.forum.loading }}</div>
                            <div v-else-if="answers.length === 0" class="text-center py-10 bg-black/20 border border-white/5 rounded-2xl">
                                 <p class="text-white/40 italic">{{ t.forum.noReplies }}</p>
                            </div>
                            
                            <div v-for="ans in answers" :key="ans.id" class="bg-black/40 border border-white/5 rounded-xl p-5 flex gap-4 items-start relative hover:bg-black/60 transition-colors">
                                    <UserAvatar :user="ans.user" class="shrink-0 shadow-sm bg-[#15202b]" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center mb-2">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-white">
                                                {{ ans.user?.name ?? t.forum.user }} <span v-if="ans.user?.last_name">{{ ans.user.last_name }}</span>
                                            </span>
                                            <span class="text-[10px] text-white/30 uppercase tracking-widest mt-0.5">
                                                {{ 
                                                    ans.user?.role?.toLowerCase() === 'admin' ? t.forum.admin : 
                                                    (ans.user?.role?.toLowerCase() === 'teacher' || ans.user?.role_name?.toLowerCase() === 'profesor' ? t.forum.teacher : t.forum.student)
                                                }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs text-white/30">{{ new Date(ans.created_at).toLocaleDateString() }}</span>
                                            <!-- Botón Eliminar Answer (Admin) -->
                                            <button 
                                                v-if="user?.role?.toLowerCase() === 'admin'"
                                                @click="triggerDelete('answer', ans.id)"
                                                class="p-1.5 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-all active:scale-90"
                                                :title="t.forum.deleteAnswer"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-white/80 text-sm leading-relaxed whitespace-pre-wrap">{{ ans.content }}</p>
                                    <div v-if="ans.image" class="mt-4 rounded-xl overflow-hidden border border-white/10 inline-block w-full max-w-lg bg-black/20">
                                        <img :src="getImageUrl(ans.image)" alt="Imagen adjunta" class="w-full h-auto max-h-[400px] object-contain hover:scale-[1.02] transition-transform duration-500 cursor-pointer" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!loading && question" class="w-full pb-32 mt-auto pt-12">
                    <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-6 pt-8 border-t border-white/5 w-full">
                        <div class="flex justify-center md:justify-start">
                            <router-link to="/foro" class="inline-flex items-center gap-2 text-white hover:bg-accent-normal-hover transition-colors bg-accent-normal px-8 py-3 rounded-xl text-sm font-bold w-full md:w-auto justify-center shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                                {{ t.forum.backToForum }}
                            </router-link>
                        </div>
                        <div class="flex justify-center">
                            <Pagination 
                                :current-page="pagination.currentPage" 
                                :last-page="pagination.lastPage" 
                                @change="handlePageChange"
                                class="mt-0!"
                            />
                        </div>
                        <div class="hidden md:block"></div>
                    </div>
                </div>
            </section>
            <!-- El Formulario ahora usa TextChatBar con validación de Groq -->
            <div class="fixed bottom-0 right-0 left-0 lg:left-75 bg-[#0f2828] border-t border-white/10 px-4 lg:px-8 py-3 z-50">
                <div class="w-full max-w-7xl mx-auto">
                    <TextChatBar is-response @sendMessage="handleSendMessage" />
                </div>
            </div>
        </main>
    </div>
    <BaseModal 
        v-if="showConfirmModal"
        :title="t.forum.confirmDeleteTitle"
        :confirmText="t.forum.confirm"
        :cancelText="t.forum.cancel"
        @close="showConfirmModal = false"
        @confirm="confirmDelete"
    >
        <p class="text-white/70 text-sm leading-relaxed">
            {{ t.forum.confirmDeleteMessage }}
        </p>
    </BaseModal>
</template>

<style scoped>
.post-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
}
</style>