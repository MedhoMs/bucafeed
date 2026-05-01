<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    import { ref, onMounted, onUnmounted, computed } from 'vue';
    import { useTranslations } from '../../composables/useTranslations'

    const { t } = useTranslations()
    const rawEvents = ref([]);
    const activeIndex = ref(1);
    const loading = ref(true);
    const isTransitioning = ref(false);
    let intervalId = null;

    const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

    const fetchEvents = async () => {
        loading.value = true;
        try {
            const response = await fetch(`${apiBase}/events`);
            if (response.ok) {
                rawEvents.value = await response.json();
            }
        } catch (e) {
            console.error("Error cargando eventos:", e);
        } finally {
            loading.value = false;
        }
    };

    const displayEvents = computed(() => {
        if (rawEvents.value.length === 0) return [];
        const last = rawEvents.value[rawEvents.value.length - 1];
        const first = rawEvents.value[0];
        return [last, ...rawEvents.value, first];
    });

    const handleTransitionEnd = () => {
        isTransitioning.value = false;
        if (activeIndex.value === 0) {
            activeIndex.value = displayEvents.value.length - 2;
        }
        if (activeIndex.value === displayEvents.value.length - 1) {
            activeIndex.value = 1;
        }
    };

    const nextSlide = () => {
        if (isTransitioning.value || displayEvents.value.length === 0) return;
        isTransitioning.value = true;
        activeIndex.value++;
    };

    const prevSlide = () => {
        if (isTransitioning.value || displayEvents.value.length === 0) return;
        isTransitioning.value = true;
        activeIndex.value--;
    };

    const goToSlide = (index) => {
        if (isTransitioning.value) return;
        isTransitioning.value = true;
        activeIndex.value = index + 1;
    };

    const startAutoSlide = () => {
        stopAutoSlide();
        intervalId = setInterval(nextSlide, 5000);
    };

    const stopAutoSlide = () => {
        if (intervalId) clearInterval(intervalId);
    };

    onMounted(async () => {
        await fetchEvents();
        startAutoSlide();
    });

    onUnmounted(() => {
        stopAutoSlide();
    });

    const fallbackImages = [
        '/home/antonio/.gemini/antigravity/brain/742c40ae-7178-4f6c-a92e-94adb607c123/school_conference_event_1777559450586.png',
        '/home/antonio/.gemini/antigravity/brain/742c40ae-7178-4f6c-a92e-94adb607c123/student_forum_gathering_1777559610451.png',
        '/home/antonio/.gemini/antigravity/brain/742c40ae-7178-4f6c-a92e-94adb607c123/educational_workshop_hands_on_1777559772847.png'
    ];

    const getEventImage = (event, index) => {
        return event.image_url || fallbackImages[index % fallbackImages.length];
    };
</script>

<template>
    <NavBar></NavBar>
    <main class="flex min-h-screen lg:pl-75">
        <section class="text-white w-full max-w-screen-2xl mx-auto px-4 md:px-6 lg:px-14 mb-20">
            <!-- Header con Buscador -->
            <div class="mt-8 md:mt-12 mb-8 md:mb-12 max-w-3xl">
                <SearchBar></SearchBar>
            </div>

            <div id="mainBody" class="w-full relative group/carousel py-6 lg:py-10 overflow-hidden">
                <div v-if="loading" class="text-white/20 italic py-40 text-center">Cargando eventos...</div>
                
                <div v-if="!loading && rawEvents.length === 0" class="text-white/20 italic py-40 text-center bg-white/5 rounded-[3rem] border border-dashed border-white/10">
                    No hay eventos programados actualmente.
                </div>
                
                <div v-if="rawEvents.length > 0" class="relative">
                    <!-- Flechas de navegación -->
                    <button @click="prevSlide(); startAutoSlide()" class="absolute left-2 lg:left-4 top-1/2 -translate-y-1/2 z-20 p-2.5 lg:p-4 bg-black/60 hover:bg-[#179cf0] text-white rounded-full transition-all active:scale-90 shadow-2xl opacity-100 lg:opacity-0 lg:group-hover/carousel:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="lg:w-7 lg:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6" /></svg>
                    </button>
                    <button @click="nextSlide(); startAutoSlide()" class="absolute right-2 lg:right-4 top-1/2 -translate-y-1/2 z-20 p-2.5 lg:p-4 bg-black/60 hover:bg-[#179cf0] text-white rounded-full transition-all active:scale-90 shadow-2xl opacity-100 lg:opacity-0 lg:group-hover/carousel:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="lg:w-7 lg:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6" /></svg>
                    </button>

                    <!-- Carousel Track -->
                    <div class="flex items-center gap-4 lg:gap-6"
                         :class="{ 'transition-transform duration-700 ease-in-out': isTransitioning }"
                         :style="{ transform: `translateX(calc(50% - (var(--item-width) / 2) - (${activeIndex} * (var(--item-width) + var(--item-gap)))))` }"
                         @transitionend="handleTransitionEnd">
                        
                        <div v-for="(event, index) in displayEvents" :key="index" 
                             class="shrink-0 w-[var(--item-width)] transition-all duration-700"
                             :class="index === activeIndex ? 'scale-100 opacity-100' : 'scale-90 opacity-40 blur-[1px] lg:blur-[2px]'">
                            
                            <div class="relative aspect-[16/10] lg:aspect-video rounded-[2rem] lg:rounded-[3rem] overflow-hidden border border-white/10 shadow-2xl">
                                <img :src="getEventImage(event, index)" class="w-full h-full object-cover" alt="event image">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                                
                                <div v-if="index === activeIndex" class="absolute bottom-0 left-0 p-5 md:p-12 lg:p-16 w-full transform transition-all duration-700">
                                    <div class="flex items-center gap-2 lg:gap-4 mb-2 lg:mb-6">
                                        <span class="px-3 py-1 bg-[#406071] text-[8px] lg:text-[10px] font-black uppercase rounded-lg lg:rounded-xl shadow-xl tracking-widest text-white">{{ event.center_name }}</span>
                                        <span class="text-[#a0c4d4] text-[10px] lg:text-xs font-bold uppercase tracking-wider lg:tracking-[0.2em]">{{ new Date(event.date).toLocaleDateString() }}</span>
                                    </div>
                                    <h3 class="text-xl md:text-5xl lg:text-6xl font-black mb-2 lg:mb-6 tracking-tighter text-white line-clamp-2">{{ event.title }}</h3>
                                    <p class="hidden md:block text-white/70 text-sm md:text-lg lg:text-xl line-clamp-2 mb-6 lg:mb-10 max-w-3xl leading-relaxed">{{ event.description }}</p>
                                    <router-link :to="'/event'" class="inline-flex items-center gap-2 lg:gap-3 bg-[#179cf0] text-white px-4 lg:px-8 py-2 lg:py-4 rounded-xl lg:rounded-2xl font-black uppercase text-[8px] lg:text-xs tracking-widest hover:bg-[#406071] transition-all shadow-lg shadow-[#179cf0]/20">
                                        Explorar
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="lg:w-5 lg:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dots para navegación -->
                <div v-if="rawEvents.length > 1" class="flex justify-center gap-2 lg:gap-3 mt-6 lg:mt-12">
                    <button v-for="(_, i) in rawEvents" :key="i" 
                            @click="goToSlide(i); startAutoSlide()"
                            :class="['h-1.5 lg:h-2 rounded-full transition-all duration-500', (activeIndex === i + 1 || (activeIndex === 0 && i === rawEvents.length - 1) || (activeIndex === rawEvents.length + 1 && i === 0)) ? 'w-8 lg:w-10 bg-[#179cf0]' : 'w-1.5 lg:w-2 bg-white/20 hover:bg-white/40']">
                    </button>
                </div>
            </div>
        </section>
    </main>
</template>

<style scoped>
    #mainBody {
        --item-width: 85%;
        --item-gap: 1rem;
    }

    @media (min-width: 1024px) {
        #mainBody {
            --item-width: 75%;
            --item-gap: 1.5rem;
        }
    }

    .carousel-item {
        transition: all 0.7s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>