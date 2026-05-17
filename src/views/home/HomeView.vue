<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import EventCarousel from '../../components/home/EventCarousel.vue';
    import PageHeader from '@/components/common/PageHeader.vue';
    import { ref, onMounted } from 'vue';
    import { useTranslations } from '../../composables/useTranslations'
    import { ABOUT_US_TIMELINE, PROJECT_TEAM } from '@/constants/aboutUs';

    const { t } = useTranslations()
    const rawEvents = ref([]);
    const loading = ref(true);

    const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

    const fetchEvents = async () => {
        loading.value = true;
        try {
            const response = await fetch(`${apiBase}/events`);
            if (response.ok) {
                const data = await response.json();
                const items = Array.isArray(data) ? data : (data.data || []);
                rawEvents.value = items.sort((a, b) => b.id - a.id).slice(0, 10);
            }
        } catch {
        } finally {
            loading.value = false;
        }
    };

    onMounted(async () => {
        await fetchEvents();

        // Configurar Intersection Observer para animar las tarjetas de la línea de tiempo
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slide-up');
                    entry.target.classList.remove('opacity-0', 'translate-y-10');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        const timelineElements = document.querySelectorAll('.timeline-item-animated');
        timelineElements.forEach(el => observer.observe(el));
    });

    const getIconSvg = (iconName) => {
        switch(iconName) {
            case 'origin':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
            case 'tools':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-6"/><path d="M9 15h6"/></svg>';
            case 'community':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
            case 'future':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M2 12h20"/><path d="M12 2v20"/><path d="M5 5l14 14"/><path d="M5 19L19 5"/></svg>';
            default:
                return '';
        }
    };
</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-20 lg:pt-0 flex flex-col min-h-screen">
            <PageHeader 
                :title="t.home.welcome" 
                :subtitle="t.home.subtitle"
                noMargin
            />
    
            <EventCarousel :events="rawEvents" :loading="loading" />

            <!-- About Us Timeline -->
            <section id="about-us" class="py-24 px-4 md:px-10 mt-16">
                <div class="max-w-7xl mx-auto">
                    <div class="text-center mb-24 timeline-item-animated opacity-0 translate-y-10 transition-all duration-700">
                        <h2 class="text-4xl md:text-6xl font-black text-white mb-6 tracking-tight">
                            {{ t.home.aboutTitle }}
                        </h2>
                        <p class="text-white/60 text-xl md:text-2xl max-w-3xl mx-auto leading-relaxed">
                            {{ t.home.aboutSubtitle }}
                        </p>
                    </div>
                    
                    <div class="relative w-full">
                        <!-- Línea vertical central -->
                        <div class="absolute left-1/2 top-0 bottom-0 w-1 bg-white/10 -translate-x-1/2 hidden md:block rounded-full"></div>
                        
                        <!-- Elementos de la línea de tiempo -->
                        <div class="space-y-12 relative">
                            
                            <div v-for="(item, index) in ABOUT_US_TIMELINE" :key="item.id" 
                                 class="timeline-item-animated opacity-0 translate-y-10 transition-all duration-700 delay-100 flex flex-col md:flex-row justify-between items-center w-full group">
                                
                                <div v-if="index % 2 === 0" class="md:w-[45%] order-2 md:order-1 text-left md:text-right mt-6 md:mt-0 transition-all duration-300 group-hover:-translate-x-4">
                                    <div class="bg-white/5 backdrop-blur-md p-8 md:p-10 rounded-3xl border border-white/10 shadow-2xl">
                                        <h3 class="mb-4 font-black text-white text-3xl text-shadow-sm">{{ t.home.timeline[item.key].title }}</h3>
                                        <p class="text-lg leading-relaxed text-white/80">{{ t.home.timeline[item.key].description }}</p>
                                    </div>
                                </div>
                                
                                <div v-if="index % 2 !== 0" class="md:w-[45%] order-3 md:order-1"></div>

                                <div class="z-20 flex items-center justify-center order-1 md:order-2 shadow-xl w-16 h-16 md:w-20 md:h-20 rounded-full border-4 border-[#0f172a] transition-transform duration-300 group-hover:scale-110" :style="{ backgroundColor: item.color, boxShadow: `0 15px 30px -5px ${item.color}88` }" v-html="getIconSvg(item.icon)">
                                </div>

                                <div v-if="index % 2 === 0" class="md:w-[45%] order-3"></div>

                                <div v-if="index % 2 !== 0" class="md:w-[45%] order-2 md:order-3 text-left mt-6 md:mt-0 transition-all duration-300 group-hover:translate-x-4">
                                    <div class="bg-white/5 backdrop-blur-md p-8 md:p-10 rounded-3xl border border-white/10 shadow-2xl">
                                        <h3 class="mb-4 font-black text-white text-3xl text-shadow-sm">{{ t.home.timeline[item.key].title }}</h3>
                                        <p class="text-lg leading-relaxed text-white/80">{{ t.home.timeline[item.key].description }}</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>

            <!-- Project Team Section -->
            <section id="project-team" class="py-24 px-4 md:px-10 mb-32">
                <div class="max-w-7xl mx-auto">
                    <div class="text-center mb-20 timeline-item-animated opacity-0 translate-y-10 transition-all duration-700">
                        <h2 class="text-4xl md:text-5xl font-black text-white mb-6 tracking-tight">
                            {{ t.home.teamTitle }}
                        </h2>
                        <p class="text-white/60 text-xl max-w-3xl mx-auto">
                            {{ t.home.teamSubtitle }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                        <div v-for="member in PROJECT_TEAM" :key="member.name" 
                             class="timeline-item-animated opacity-0 translate-y-10 transition-all duration-700 delay-200 bg-white/5 backdrop-blur-md p-10 md:p-12 rounded-3xl border border-white/10 shadow-2xl flex flex-col items-center group hover:bg-white/10 transition-colors">
                            <div class="w-32 h-32 md:w-40 md:h-40 rounded-full overflow-hidden mb-8 shadow-2xl shadow-black/60 group-hover:scale-110 transition-transform border-4 border-[#326465]">
                                <img :src="member.avatar" :alt="member.name" class="w-full h-full object-cover">
                            </div>
                            <h3 class="text-2xl md:text-3xl font-bold text-white mb-8 text-center">{{ member.name }}</h3>
                            <a :href="member.github" target="_blank" class="flex items-center gap-3 px-6 py-3 bg-white/10 hover:bg-white/20 rounded-2xl text-white text-lg font-bold transition-all group-hover:bg-[#326465]">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>
                                GitHub
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<style scoped>
.animate-slide-up {
    animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>