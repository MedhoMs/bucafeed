<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    import { ref, onMounted } from 'vue';

    import { useTranslations } from '../../composables/useTranslations'

    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
    import defaultAvatar from '../../assets/logo/logoTelamon.png';
    const getAvatar = (user) => {
        const baseSrc = import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace(/\/api$/, '') : 'http://localhost:8000';
        return user?.profile_picture ? baseSrc + user.profile_picture : defaultAvatar;
    };
    
    const questions = ref([]);

    onMounted(async () => {
        try {
            const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
            const response = await fetch(`${apiBase}/questions`);
            if (response.ok) {
                const result = await response.json();
                questions.value = result.data || result;
            }
        } catch(e) {
            console.error("Error cargando preguntas:", e);
        }
    });

</script>

<template>
    <NavBar></NavBar>
    <main class="flex min-h-screen lg:pl-75">
        <section class="text-white w-full max-w-screen-2xl mx-auto px-6 lg:px-14 mb-20">
            <!-- Header con Buscador -->
            <div class="mt-8 md:mt-12 mb-8 md:mb-12 max-w-3xl">
                <h1 class="text-4xl font-black mb-4">Foro de la Comunidad</h1>
                <p class="text-white/60 mb-8">Resuelve tus dudas y ayuda a otros estudiantes.</p>
                <SearchBar></SearchBar>
            </div>

            <div id="mainBody" class="flex flex-col gap-6 w-full">
                <div v-if="!questions || questions.length === 0" class="text-white/40 italic py-10">Cargando preguntas del servidor...</div>

                <div v-for="q in questions" :key="q.id" class="post-card text-left w-full">
                    <div class="flex gap-4 items-center mb-4">
                        <router-link :to="'/profile/' + q.user_id" class="shrink-0 hover:scale-105 transition-transform" title="Ver perfil">
                            <img :src="getAvatar(q.user)" alt="icono" class="w-10 h-10 rounded-full border border-white/20 shadow-lg object-cover bg-[#15202b]" />
                        </router-link>
                        <div class="flex flex-col">
                            <router-link :to="'/profile/' + q.user_id" class="text-sm font-bold text-white hover:text-emerald-400 transition-colors">
                                {{ q.user?.name ?? 'Usuario' }} <span v-if="q.user?.last_name">{{ q.user.last_name }}</span>
                            </router-link>
                            <span class="text-[10px] text-white/40 uppercase tracking-widest">{{ q.user?.role_name || q.user?.role || 'Estudiante' }}</span>
                        </div>
                    </div>
                    <router-link :to="'/question/' + q.id">
                        <h2 class="post-title hover:text-emerald-400 transition-colors">{{ q.title }}</h2>
                    </router-link>
                    <p class="text-white/80 mt-2 text-sm line-clamp-3 leading-relaxed">{{ q.content }}</p>
                    <div class="post-footer mt-4 flex items-center justify-between">
                        <router-link :to="'/question/' + q.id" class="responses-badge hover:bg-white/10 transition-colors px-3 py-2 rounded-lg cursor-pointer flex items-center gap-2 select-none" title="Ver respuestas">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white/60"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                            <span class="response-count text-sm text-white/80 font-medium">{{ q.answers ? q.answers.length : 0 }} Respuestas</span>
                        </router-link>
                        
                        <router-link :to="'/question/' + q.id" class="text-xs font-bold uppercase tracking-widest text-[#179cf0] hover:text-white transition-colors">
                            Ver hilo completo
                        </router-link>
                    </div>
                </div>
            </div>
        </section>
    </main>
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

    .responses-badge {
        display: flex;
        justify-content: flex-end;
    }
</style>
