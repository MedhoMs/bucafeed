<script setup>
    import { ref, onMounted } from 'vue';
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    import SideBar from '../../components/SideBar.vue';
    
    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations()

    const events = ref([]);

    onMounted(async () => {
        try {
            const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
            const response = await fetch(`${apiBase}/events`);
            if (response.ok) {
                events.value = await response.json();
            }
        } catch (error) {
            console.error('Error fetching events:', error);
        }
    });
</script>

<template>
    <NavBar></NavBar>
    <main class="flex min-h-screen justify-between lg:pl-75">
        <section class="text-white lg:w-375 w-87.5 mx-auto lg:mr-14 mb-4">
            <SearchBar></SearchBar>
            <h1 class="text-2xl lg:text-4xl text-center pt-5 font-extrabold bg-clip-text tracking-tight leading-none mb-2">
                Eventos Disponibles
            </h1>
            <div id="mainBody" class="grid grid-cols-1 lg:grid-cols-4 gap-4 justify-items-center min-h-[92.9vh] mt-12">
                <!-- Eventos dinámicos -->
                <div v-for="event in events" :key="event.id" class="event-card group cursor-pointer hover:border-white/20 transition-all duration-300">
                    <img :src="event.image_url" alt="Imagen del evento" class="group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    <h2 class="font-bold text-[16px] ml-4 leading-tight pr-4 text-white group-hover:text-cyan-400 transition-colors duration-300">
                        {{ event.title }}
                    </h2>
                    <p class="text-white/60 text-[14px] ml-4 mt-1 pr-6 line-clamp-2 leading-snug">
                        {{ event.description }}
                    </p>
                    <div class="flex flex-col gap-1 mt-2 ml-4">
                        <!-- Fecha y Ubicación -->
                        <div class="flex items-center text-[14px] text-white/70">
                            <span class="mr-1">📅</span>
                            <span>{{ new Date(event.date).getDate() }}/{{ new Date(event.date).getMonth() + 1 }}/{{ new Date(event.date).getFullYear().toString().slice(-2) }} </span>
                            <span class="ml-1 mr-1">📍</span>
                            <span class="truncate">{{ event.location }}</span>
                        </div>
                        <!-- Horario -->
                        <div class="flex items-center text-[14px]">
                            <span class="mr-1 opacity-70">🕒</span>
                            <span class="font-bold mr-1 text-cyan-400/80">Horario:</span>
                            <span class="text-white/90">
                                {{ event.start_time ? event.start_time.substring(0, 5) : '00:00' }} - 
                                {{ event.end_time ? event.end_time.substring(0, 5) : '00:00' }}
                            </span>
                        </div>
                    </div>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>
            </div>
        </section>
    </main>
</template>

<style scoped>

    .event-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        width: 325px;
        height: 375px;
        border-radius: 20px;
        position: relative;
        overflow: hidden;
    }

    .event-card img {
        width: 100%;
        height: 55%;
        object-fit: cover;
        margin: auto;
        margin-bottom: 10px;
    }

    .event-card button {
        position: absolute;
        bottom: 20px;
        right: 20px;
        border-radius: 20px;
        width: 100px;
    }

    .event-card button:hover {
        background-color: #0a1e35;
        cursor: pointer;
    }
</style>