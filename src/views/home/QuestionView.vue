<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';

    import UserAvatar from '../../components/common/UserAvatar.vue';
    import EmojiPicker from '../../components/common/EmojiPicker.vue';

    import BaseModal from '@/components/modals/BaseModal.vue';
    import PageHeader from '@/components/common/PageHeader.vue';
    import Pagination from '../../components/common/Pagination.vue';
    import TextChatBar from '@/components/TextChatBar.vue';
    import UnverifiedBanner from '@/components/common/UnverifiedBanner.vue';
    import { ref, onMounted, watch, reactive, computed } from 'vue';
    import { useRoute, useRouter } from 'vue-router';
    import { user } from '@/stores/auth';

    import { useTranslations } from '../../composables/useTranslations'
    import { useApi } from '../../composables/useApi';
    const { t } = useTranslations()
    const { get, post, del, loading: apiLoading } = useApi();
    const router = useRouter();

    const isUnverified = computed(() => user.value?.role === 'Student' && user.value?.is_verified === false)

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

    const likeAnswer = async (answer) => {
        try {
            const result = await post(`answers/${answer.id}/useful`, {});
            if (result) {
                // Actualizar la respuesta localmente
                answer.is_useful = true;
                if (answer.user) {
                    answer.user.reputation = (answer.user.reputation || 0) + 50;
                }
            }
        } catch (e) {
            console.error("Error al marcar como útil", e);
        }
    };

    const unlikeAnswer = async (answer) => {
        try {
            const result = await del(`answers/${answer.id}/useful`);
            if (result) {
                // Actualizar la respuesta localmente
                answer.is_useful = false;
                if (answer.user) {
                    answer.user.reputation = Math.max(0, (answer.user.reputation || 0) - 50);
                }
            }
        } catch (e) {
            console.error("Error al retirar utilidad", e);
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
        <main class="lg:pl-75 pt-20 lg:pt-8 flex flex-col min-h-screen relative pb-44">
            <!-- Banner para alumnos no verificados (Centrado y bloqueante) -->
            <UnverifiedBanner 
                v-if="isUnverified"
                :message="t.forum.unverifiedThreadMessage || 'No puedes visualizar este hilo hasta que tu centro verifique tu identidad.'"
            />

            <template v-else>
                <section class="text-white w-full px-4 lg:px-14 flex-1 flex flex-col">
        
                    <div id="mainTrending" class="flex flex-col items-center w-full flex-1">
                        
                        <div v-if="apiLoading && !question" class="text-white/40 italic py-10 mt-20">{{ t.forum.loadingThread }}</div>
                        
                        <div v-else-if="!apiLoading && !question" class="text-white/40 italic py-10 mt-20 text-2xl font-bold">
                            {{ t.forum.notFound }}
                        </div>
        
                        <div v-else class="w-full">
                            <!-- Tarjeta de la Pregunta Principal -->
                            <div class="post-card text-left bg-linear-to-br from-white/5 to-white/2 border border-white/10 rounded-2xl p-7 relative mb-8">
                                <div class="flex gap-4 items-center mb-4 border-b border-white/5 pb-4 pr-14">
                                    <router-link v-if="question.user?.id" :to="'/profile/' + question.user.id" class="flex gap-4 items-center hover:opacity-80 transition-opacity no-underline">
                                        <UserAvatar :user="question.user" size="w-12 h-12" class="shrink-0 shadow-lg bg-[#15202b]" />
                                        <div class="flex flex-col">
                                            <span class="text-base font-bold text-white flex items-center gap-2">
                                                {{ question.user?.name ?? 'Usuario' }} <span v-if="question.user?.last_name">{{ question.user.last_name }}</span>
                                                <span class="px-2 py-0.5 rounded-full bg-accent-normal text-amber-300 text-[10px] font-black border border-white/60">
                                                    {{ question.user?.reputation ?? 0 }} pts
                                                </span>
                                            </span>
                                            <span class="text-xs text-white/40 uppercase tracking-normal">{{ question.user?.role_name || question.user?.role || 'Estudiante' }}</span>
                                        </div>
                                    </router-link>
                                    <div v-else class="flex gap-4 items-center">
                                        <UserAvatar :user="question.user" size="w-12 h-12" class="shrink-0 shadow-lg bg-[#15202b]" />
                                        <div class="flex flex-col">
                                            <span class="text-base font-bold text-white">
                                                {{ question.user?.name ?? 'Usuario' }} <span v-if="question.user?.last_name">{{ question.user.last_name }}</span>
                                            </span>
                                            <span class="text-xs text-white/40 uppercase tracking-widest">{{ question.user?.role_name || question.user?.role || 'Estudiante' }}</span>
                                        </div>
                                    </div>
                                    <!-- Botón Eliminar Question (Admin) -->
                                    <button 
                                        v-if="user?.role?.toLowerCase() === 'admin'"
                                        @click="triggerDelete('question', question.id)"
                                        class="absolute top-7 right-7 p-2 rounded-xl bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-all active:scale-90 z-10"
                                        :title="t.forum.deleteQuestion"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </button>
                                </div>
                                <h1 class="text-4xl lg:text-5xl font-black text-white mb-4 tracking-normal">{{ question.title }}</h1>
                                <p class="text-white/80 text-base leading-relaxed whitespace-pre-wrap">{{ question.content }}</p>
                                
                                <img v-if="question.image" :src="getImageUrl(question.image)" alt="Imagen de apoyo" class="mt-6 max-h-[300px] w-auto max-w-full rounded-2xl border border-white/10 hover:scale-[1.01] transition-transform duration-700" />
                                
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
                                    <router-link v-if="ans.user?.id" :to="'/profile/' + ans.user.id" class="shrink-0 hover:opacity-80 transition-opacity">
                                        <UserAvatar :user="ans.user" class="shrink-0 shadow-sm bg-[#15202b]" />
                                    </router-link>
                                    <UserAvatar v-else :user="ans.user" class="shrink-0 shadow-sm bg-[#15202b]" />
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-center mb-2 pr-10">
                                            <router-link v-if="ans.user?.id" :to="'/profile/' + ans.user.id" class="flex flex-col hover:opacity-80 transition-opacity no-underline">
                                                <span class="text-sm font-bold text-white flex items-center gap-2">
                                                    {{ ans.user?.name ?? t.forum.user }} <span v-if="ans.user?.last_name">{{ ans.user.last_name }}</span>
                                                    <span class="px-1.5 py-0.5 rounded-md bg-white/5 text-white/40 text-[9px] border border-white/5">
                                                        {{ ans.user?.reputation ?? 0 }} pts
                                                    </span>
                                                </span>
                                                <span class="text-[10px] text-white/30 uppercase tracking-normal mt-0.5">
                                                    {{ 
                                                        ans.user?.role?.toLowerCase() === 'admin' ? t.forum.admin : 
                                                        (ans.user?.role?.toLowerCase() === 'teacher' || ans.user?.role_name?.toLowerCase() === 'profesor' ? t.forum.teacher : t.forum.student)
                                                    }}
                                                </span>
                                            </router-link>
                                            <div v-else class="flex flex-col">
                                                <span class="text-sm font-bold text-white flex items-center gap-2">
                                                    {{ ans.user?.name ?? t.forum.user }} <span v-if="ans.user?.last_name">{{ ans.user.last_name }}</span>
                                                    <span class="px-1.5 py-0.5 rounded-md bg-white/5 text-white/40 text-[9px] border border-white/5">
                                                        {{ ans.user?.reputation ?? 0 }} pts
                                                    </span>
                                                </span>
                                                <span class="text-[10px] text-white/30 uppercase tracking-normal mt-0.5">
                                                    {{ 
                                                        ans.user?.role?.toLowerCase() === 'admin' ? t.forum.admin : 
                                                        (ans.user?.role?.toLowerCase() === 'teacher' || ans.user?.role_name?.toLowerCase() === 'profesor' ? t.forum.teacher : t.forum.student)
                                                    }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <!-- Botón Like (Reputación) -->
                                                <button 
                                                    v-if="user?.id === question?.user_id && ans.user_id !== user?.id && !ans.is_useful"
                                                    @click="likeAnswer(ans)"
                                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/10 text-amber-500 hover:bg-amber-500 hover:text-white transition-all active:scale-95 text-xs font-bold cursor-pointer"
                                                    title="Otorgar +50 puntos de reputación"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.74-1c.22-.4.45-.83.71-1.31.49-1 .81-2.22 1.2-2.83a4 4 0 0 1 4.59-2.22c1.4.3 1 2.09.81 3.25z"/></svg>
                                                    Útil
                                                </button>
                                                <button 
                                                    v-if="ans.is_useful"
                                                    @click="user?.id === question?.user_id ? unlikeAnswer(ans) : null"
                                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-green-500/20 text-green-400 text-xs font-bold border border-green-500/20 transition-all"
                                                    :class="user?.id === question?.user_id ? 'hover:bg-red-500/10 hover:text-red-400 hover:border-red-500/20 cursor-pointer' : ''"
                                                    :title="user?.id === question?.user_id ? 'Retirar reconocimiento' : 'Respuesta reconocida'"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="group-hover:hidden"><path d="M20 6 9 17l-5-5"/></svg>
                                                    {{ ans.is_useful && user?.id === question?.user_id ? 'Reconocida' : 'Reconocida' }}
                                                </button>
    
                                                <span class="text-xs text-white/30">{{ new Date(ans.created_at).toLocaleDateString() }}</span>
                                                <!-- Botón Eliminar Answer (Admin) -->
                                                <button 
                                                    v-if="user?.role?.toLowerCase() === 'admin'"
                                                    @click="triggerDelete('answer', ans.id)"
                                                    class="absolute top-5 right-5 p-1.5 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-all active:scale-90 z-10"
                                                    :title="t.forum.deleteAnswer"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <p class="text-white/80 text-sm leading-relaxed whitespace-pre-wrap">{{ ans.content }}</p>
                                        <img v-if="ans.image" :src="getImageUrl(ans.image)" alt="Imagen adjunta" class="mt-4 max-h-[300px] w-auto max-w-full rounded-xl border border-white/10 hover:scale-[1.02] transition-transform duration-500 cursor-pointer" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- Paginación y botón Volver al foro movidos a la barra fija inferior para mantenerse siempre visibles -->
                </section>
                <!-- Barra fija inferior unificada (Paginación + Entrada de texto) -->
                <div class="fixed bottom-0 right-0 left-0 lg:left-75 z-50 pointer-events-none">
                    <div class="w-full flex flex-col">
                        <!-- 1. Fila de paginación y botón Volver -->
                        <div v-if="!loading && question" class="px-4 lg:px-14 py-4 pointer-events-none">
                            <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4 w-full">
                                <div class="flex justify-center md:justify-start pointer-events-auto">
                                    <router-link to="/foro" class="inline-flex items-center gap-2 text-white hover:bg-accent-normal-hover transition-colors bg-accent-normal px-8 py-3 rounded-xl text-sm font-bold w-full md:w-auto justify-center shadow-lg no-underline">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                                        {{ t.forum.backToForum }}
                                    </router-link>
                                </div>
                                <div class="flex justify-center pointer-events-auto">
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

                        <!-- 2. Fila del formulario de respuesta -->
                        <div class="px-4 lg:px-14 py-3 bg-[#0f2828]/95 backdrop-blur-md border-t border-white/10 pointer-events-auto">
                            <div class="flex flex-col gap-1 w-full">
                                <TextChatBar 
                                    :disabled="isUnverified" 
                                    is-response 
                                    @sendMessage="handleSendMessage" 
                                />
                                <!-- Pequeño aviso inferior -->
                                <p v-if="isUnverified" class="text-[9px] text-amber-400/60 font-black uppercase tracking-widest text-center mt-1">
                                    Cuenta pendiente de verificación - Interacción deshabilitada
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
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