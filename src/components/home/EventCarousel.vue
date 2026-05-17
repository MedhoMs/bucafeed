<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useTranslations } from '../../composables/useTranslations'
import defaultImage from '../../assets/logo/logoTelamon.png';

const { t } = useTranslations()
const router = useRouter();

const props = defineProps({
    events: {
        type: Array,
        required: true
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const activeIndex = ref(1);
const isTransitioning = ref(false);
let intervalId = null;

const displayEvents = computed(() => {
    if (props.events.length === 0) return [];
    const last = props.events[props.events.length - 1];
    const first = props.events[0];
    return [last, ...props.events, first];
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
    if (props.events.length > 1) {
        intervalId = setInterval(nextSlide, 5000);
    }
};

const stopAutoSlide = () => {
    if (intervalId) clearInterval(intervalId);
};

const getEventImage = (event) => {
    return event.image_url ? event.image_url : defaultImage;
};

const viewEventDetails = (event) => {
    localStorage.setItem('selectedEvent', JSON.stringify(event));
    router.push({ name: 'event-details', params: { id: event.id } });
};

onMounted(() => {
    startAutoSlide();
});

watch(() => props.events, (newVal) => {
    if (newVal && newVal.length > 1) {
        startAutoSlide();
    }
}, { deep: true });

onUnmounted(() => {
    stopAutoSlide();
});
</script>

<template>
    <section class="text-white w-full max-w-screen-2xl mx-auto px-4 md:px-6 lg:px-14 mb-16">
        <div id="carouselContainer" class="w-full relative group/carousel py-4 lg:py-6 overflow-hidden">
            <div v-if="loading" class="text-white/20 italic py-32 text-center">{{ t.home.loadingEvents }}</div>
            
            <div v-if="!loading && events.length === 0" class="text-white/20 italic py-32 text-center bg-white/5 rounded-[3rem] border border-dashed border-white/10">
                {{ t.home.noEvents }}
            </div>
            
            <div v-if="events.length > 0" class="relative">
                <!-- Flechas de navegación -->
                <button @click="prevSlide(); startAutoSlide()" class="absolute left-2 lg:left-4 top-1/2 -translate-y-1/2 z-20 p-2.5 lg:p-4 rounded-full btn-dark-icon opacity-100 lg:opacity-0 lg:group-hover/carousel:opacity-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="lg:w-7 lg:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6" /></svg>
                </button>
                <button @click="nextSlide(); startAutoSlide()" class="absolute right-2 lg:right-4 top-1/2 -translate-y-1/2 z-20 p-2.5 lg:p-4 rounded-full btn-dark-icon opacity-100 lg:opacity-0 lg:group-hover/carousel:opacity-100">
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
                            <img :src="getEventImage(event)" class="w-full h-full object-cover" alt="event image">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            
                            <div v-if="index === activeIndex" class="absolute bottom-0 left-0 p-5 md:p-12 lg:p-16 w-full transform transition-all duration-700">
                                <div class="flex items-center gap-2 lg:gap-4 mb-2 lg:mb-4">
                                    <span class="px-3 py-1 glass-pill text-[8px] lg:text-[10px] font-black uppercase rounded-lg lg:rounded-xl tracking-widest">{{ event.center_name }}</span>
                                    <span class="text-dimmed text-[10px] lg:text-xs font-bold uppercase tracking-wider lg:tracking-[0.2em]">{{ new Date(event.date).toLocaleDateString() }}</span>
                                </div>
                                <h3 class="text-xl md:text-5xl lg:text-6xl font-black mb-2 lg:mb-4 tracking-tighter text-white line-clamp-2">{{ event.title }}</h3>
                                <p class="hidden md:block text-white/70 text-sm md:text-lg lg:text-xl line-clamp-2 mb-4 lg:mb-6 max-w-3xl leading-relaxed">{{ event.description }}</p>
                                <button @click="viewEventDetails(event)" class="inline-flex items-center justify-center btn-light gap-2 lg:gap-3 px-4 lg:px-8 py-2 lg:py-4 rounded-xl lg:rounded-2xl font-black uppercase text-[8px] lg:text-xs tracking-widest cursor-pointer">
                                    Ver Detalles
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="lg:w-5 lg:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dots para navegación -->
            <div v-if="events.length > 1" class="flex justify-center gap-2 lg:gap-3 mt-4 lg:mt-6">
                <button v-for="(_, i) in events" :key="i" 
                        @click="goToSlide(i); startAutoSlide()"
                        :class="['h-1.5 lg:h-2 rounded-full', (activeIndex === i + 1) ? 'w-8 lg:w-10 dot-indicator-active' : 'w-1.5 lg:w-2 dot-indicator']">
                </button>
            </div>
        </div>
    </section>
</template>

<style scoped>
    #carouselContainer {
        --item-width: 85%;
        --item-gap: 1rem;
    }

    @media (min-width: 1024px) {
        #carouselContainer {
            --item-width: 75%;
            --item-gap: 1.5rem;
        }
    }
</style>
