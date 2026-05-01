<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    import SideBar from '../../components/SideBar.vue';
    import { ref, onMounted, watch } from 'vue';
    import { useRoute } from 'vue-router';
    import { user } from '@/stores/auth';

    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations()

    import defaultAvatar from '../../assets/logo/logoTelamon.png';
    const getAvatar = (user) => {
        const baseSrc = import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace(/\/api$/, '') : 'http://localhost:8000';
        return user?.profile_picture ? baseSrc + user.profile_picture : defaultAvatar;
    };

    
    const route = useRoute();
    const question = ref(null);
    const loading = ref(true);
    const submitting = ref(false);
    const newAnswer = ref("");

    const loadQuestion = async (id) => {
        loading.value = true;
        try {
            const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
            const response = await fetch(`${apiBase}/questions/${id}`);
            if (response.ok) {
                const data = await response.json();
                question.value = data.question || data; // ajustando por estructura del backend
            }
        } catch (e) {
            console.error("Error cargando pregunta", e);
        } finally {
            loading.value = false;
        }
    };

    const submitAnswer = async () => {
        if (!newAnswer.value.trim() || submitting.value) return;
        
        submitting.value = true;
        try {
            const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
            const response = await fetch(`${apiBase}/answers`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}` // Asumiendo que guardas el token
                },
                body: JSON.stringify({
                    question_id: question.value.id,
                    user_id: user.value?.id,
                    content: newAnswer.value
                })
            });

            if (response.ok) {
                newAnswer.value = "";
                await loadQuestion(route.params.id); // Recargar para ver la nueva respuesta
            }
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
    <NavBar></NavBar>
    <main class="flex min-h-screen justify-between lg:pl-75">
        <section class="text-white lg:w-375 w-87.5 mx-auto lg:mr-14 mb-4">
            <SearchBar></SearchBar>
            <div id="mainTrending" class="flex flex-col items-center mt-6 min-h-[90vh]">
                
                <div v-if="loading" class="text-white/40 italic py-10 mt-20">Cargando hilo de discusión...</div>
                
                <div v-else-if="!question" class="text-white/40 italic py-10 mt-20 text-2xl font-bold">
                    La pregunta no existe o fue eliminada.
                </div>

                <div v-else class="w-full max-w-375">
                    <!-- Botón Volver -->
                    <router-link to="/foro" class="inline-flex items-center gap-2 mb-6 text-[#179cf0] hover:text-white transition-colors bg-[#179cf0]/10 hover:bg-[#179cf0]/20 px-4 py-2 rounded-full text-sm font-bold w-fit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                        Volver al foro
                    </router-link>

                    <!-- Tarjeta de la Pregunta Principal -->
                    <div class="post-card text-left bg-linear-to-br from-white/5 to-white/2 border border-white/10 rounded-2xl p-7 relative mb-8">
                        <div class="flex gap-4 items-center mb-4 border-b border-white/5 pb-4">
                            <router-link :to="'/profile/' + (question.user_id || question.user?.id)" class="shrink-0 hover:scale-105 transition-transform">
                                <img :src="getAvatar(question.user)" alt="icono" class="w-12 h-12 rounded-full border border-white/20 shadow-lg object-cover bg-[#15202b]" />
                            </router-link>
                            <div class="flex flex-col">
                                <router-link :to="'/profile/' + (question.user_id || question.user?.id)" class="text-base font-bold text-white hover:text-emerald-400 transition-colors">
                                    {{ question.user?.name ?? 'Usuario' }} <span v-if="question.user?.last_name">{{ question.user.last_name }}</span>
                                </router-link>
                                <span class="text-xs text-white/40 uppercase tracking-widest">{{ question.user?.role ?? 'Student' }}</span>
                            </div>
                        </div>
                        <h1 class="text-2xl font-black text-white mb-4">{{ question.title }}</h1>
                        <p class="text-white/80 text-base leading-relaxed whitespace-pre-wrap">{{ question.content }}</p>
                        
                        <div v-if="question.tags && question.tags.length" class="mt-6 pt-4 border-t border-white/5 flex flex-wrap gap-2">
                             <div v-for="tag in question.tags" :key="tag.id" class="px-3 py-1 rounded-full bg-[#179cf0]/10 text-[#179cf0] border border-[#179cf0]/20 text-[10px] font-bold shadow-xs">
                                  {{ tag.name }}
                             </div>
                        </div>
                    </div>
                    <!-- Formulario de Respuesta -->
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 mb-12 shadow-inner">
                        <div class="flex gap-4 items-start mb-4">
                            <img :src="getAvatar(user)" class="w-10 h-10 rounded-full border border-white/20 object-cover shrink-0" alt="me">
                            <div class="flex-1">
                                <textarea 
                                    v-model="newAnswer"
                                    placeholder="Escribe tu respuesta aquí..." 
                                    class="w-full bg-black/30 border border-white/10 rounded-xl p-4 text-white placeholder:text-white/20 focus:outline-none focus:border-[#179cf0] transition-colors min-h-[120px] resize-none text-sm"
                                ></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button 
                                @click="submitAnswer"
                                :disabled="!newAnswer.trim() || submitting"
                                class="bg-[#179cf0] hover:bg-emerald-500 disabled:opacity-50 disabled:hover:bg-[#179cf0] text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center gap-2 shadow-lg active:scale-95"
                            >
                                <span v-if="submitting">Enviando...</span>
                                <template v-else>
                                    Responder
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                                </template>
                            </button>
                        </div>
                     </div>

                    <!-- Sección de Respuestas -->
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 px-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M21 14l-3 -3h-7a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1h9a1 1 0 0 1 1 1v10" /><path d="M14 15v2a1 1 0 0 1 -1 1h-7l-3 3v-10a1 1 0 0 1 1 -1h2" /></svg>
                        Respuestas ({{ question.answers ? question.answers.length : 0 }})
                    </h3>

                    <div class="flex flex-col gap-4">
                        <div v-if="!question.answers || question.answers.length === 0" class="text-center py-10 bg-black/20 border border-white/5 rounded-2xl">
                             <p class="text-white/40 italic">Aún no hay respuestas para esta pregunta. ¡Sé el primero en ayudar!</p>
                        </div>
                        
                        <div v-for="ans in question.answers" :key="ans.id" class="bg-black/40 border border-white/5 rounded-xl p-5 flex gap-4 items-start relative hover:bg-black/60 transition-colors">
                            <router-link :to="'/profile/' + (ans.user_id || ans.user?.id)" class="shrink-0 mt-1 hover:scale-105 transition-transform">
                                <img :src="getAvatar(ans.user)" alt="icono" class="w-10 h-10 rounded-full border border-white/10 shadow-sm object-cover bg-[#15202b]" />
                            </router-link>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center mb-2">
                                    <div class="flex flex-col">
                                        <router-link :to="'/profile/' + (ans.user_id || ans.user?.id)" class="text-sm font-bold text-white hover:text-[#179cf0] transition-colors">
                                            {{ ans.user?.name ?? 'Usuario' }} <span v-if="ans.user?.last_name">{{ ans.user.last_name }}</span>
                                        </router-link>
                                        <span class="text-[10px] text-white/30 uppercase tracking-widest mt-0.5">{{ ans.user?.role ?? 'User' }}</span>
                                    </div>
                                    <span class="text-xs text-white/30">{{ new Date(ans.created_at).toLocaleDateString() }}</span>
                                </div>
                                <p class="text-white/80 text-sm leading-relaxed whitespace-pre-wrap">{{ ans.content }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>
</template>

<style scoped>
.post-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
}
</style>