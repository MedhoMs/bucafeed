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
        function showCreatedPost(postObjectValues) {

            const postObjectDict = Object.entries(postObjectValues);

            const mainBody = document.getElementById("mainBody");

            const newPostContainer = document.createElement("div");
            const newPostTitle = document.createElement("p");
            const newPostDescription = document.createElement("p");

            newPostContainer.className = "border border-red-500 w-[600px]";
            mainBody.append(newPostContainer);

            newPostTitle.innerHTML = postObjectDict[0][1];
            newPostTitle.className = "text-[30px]";

            newPostDescription.innerHTML = postObjectDict[2][1];
            newPostTitle.className = "text-[25px]";

            newPostContainer.append(newPostTitle);
            newPostContainer.append(newPostDescription);
        }

        const postObject = JSON.parse(sessionStorage.getItem("newPost"));

        if (postObject) {
            showCreatedPost(postObject);
        }

    });

</script>

<template>
    <NavBar></NavBar>
    <main class="flex min-h-screen justify-between lg:pl-[300px]">
        <section class="text-white lg:w-[1500px] w-[350px] mx-auto lg:mr-14 mb-4">
            <SearchBar></SearchBar>
            <div id="mainBody" class="flex flex-col gap-4 justify-center items-center min-h-[92.9vh]">
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
                            <span class="text-[10px] text-white/40 uppercase tracking-widest">{{ q.user?.role ?? 'Student' }}</span>
                        </div>
                    </div>
                    <h2 class="post-title">{{ q.title }}</h2>
                    <p class="text-white/80 mt-2 text-sm line-clamp-3 leading-relaxed">{{ q.content }}</p>
                    <div class="post-footer mt-4">
                        <div @click="q.showAnswers = !q.showAnswers" class="responses-badge hover:bg-white/10 transition-colors p-2 rounded-lg cursor-pointer inline-flex items-center gap-1.5 select-none" title="Ver respuestas">
                            <span class="response-emoji text-lg">💬</span>
                            <span class="response-count">{{ q.answers ? q.answers.length : 0 }} Respuestas</span>
                        </div>
                        <hr class="border-white/10 my-4"></hr>
                        
                        <!-- Desplegable de Respuestas -->
                        <div v-show="q.showAnswers" class="mt-4 flex flex-col gap-3">
                            <div v-if="!q.answers || q.answers.length === 0" class="text-white/40 italic py-2">
                                Aún no hay respuestas.
                            </div>
                            <div v-for="ans in q.answers" :key="ans.id" class="bg-black/20 rounded-lg p-4 border border-white/5 flex gap-3 text-sm">
                                <router-link :to="'/profile/' + ans.user_id" class="shrink-0">
                                    <img :src="getAvatar(ans.user)" alt="icono" class="w-8 h-8 rounded-full border border-white/10 object-cover bg-[#15202b]" />
                                </router-link>
                                <div>
                                    <router-link :to="'/profile/' + ans.user_id" class="font-bold hover:text-[#179cf0] transition-colors">
                                        {{ ans.user?.name ?? 'Usuario' }} <span v-if="ans.user?.last_name">{{ ans.user.last_name }}</span>
                                    </router-link>
                                    <span class="text-white/30 text-xs ml-2">{{ new Date(ans.created_at).toLocaleDateString() }}</span>
                                    <p class="text-white/80 mt-1 whitespace-pre-wrap">{{ ans.content }}</p>
                                </div>
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
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 20px;
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
    }

    .post-card:hover {
        cursor: pointer;
        background: linear-gradient(135deg, rgba(54, 54, 54, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
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