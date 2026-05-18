<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import NavBar from '../../components/NavBar/NavBar.vue';
import BaseModal from '@/components/modals/BaseModal.vue';
import { useTranslations } from '../../composables/useTranslations';
import { useApi } from '@/composables/useApi';
import { user } from '@/stores/auth';

const { t } = useTranslations();
const router = useRouter();
const { get, post } = useApi();

const loading = ref(true);
const questions = ref([]);
const events = ref([]);
const publications = ref([]);
const followCenters = ref([]);
const searchQuery = ref('');
const activeTab = ref('para_ti');
const showAllCenters = ref(false);
const hoveredCenterId = ref(null);
const isScrolled = ref(false);
const showDetailsModal = ref(false);
const selectedPublication = ref(null);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

const fetchAllData = async () => {
    loading.value = true;
    try {
        const promises = [
            get('events'),
            get('users?role=EI'), // Fetch educational centers (Users with role EI)
            get('publications') // Fetch publications
        ];

        // Omit questions fetch if user is External User (EU)
        if (user.value?.role !== 'EU') {
            promises.push(get('questions'));
        }

        const results = await Promise.all(promises);

        const eRes = results[0];
        const uRes = results[1];
        const pRes = results[2];
        const qRes = user.value?.role !== 'EU' ? results[3] : { data: [] };

        questions.value = qRes.data && Array.isArray(qRes.data) ? qRes.data : (qRes || []);
        events.value = eRes.data && Array.isArray(eRes.data) ? eRes.data : (eRes || []);
        publications.value = pRes.data && Array.isArray(pRes.data) ? pRes.data : (pRes || []);
        
        const usersList = uRes.data && Array.isArray(uRes.data) ? uRes.data : (uRes || []);
        // Map centers dynamically from actual User accounts having role = 'EI'
        followCenters.value = usersList.map(u => {
            // Priority: u.institution_name -> u.educational_center?.name -> u.name
            let displayName = u.institution_name || u.educational_center?.name || (u.name + (u.last_name ? ' ' + u.last_name : ''));
            
            // Clean 'Admin ' or 'Admin' prefix if present
            if (displayName.toLowerCase().startsWith('admin ')) {
                displayName = displayName.substring(6).trim();
            } else if (displayName.toLowerCase().startsWith('admin')) {
                displayName = displayName.substring(5).trim();
            }

            // Generate clean alias
            let aliasName = displayName.toLowerCase().replace(/[^a-z0-9]/g, '');
            if (aliasName.startsWith('admin')) {
                aliasName = aliasName.substring(5);
            }
            if (!aliasName) {
                aliasName = 'centro';
            }

            return {
                id: u.id,
                name: displayName,
                alias: `@${aliasName}`,
                verified: true,
                avatarLetter: displayName.substring(0, 1).toUpperCase(),
                profile_picture: u.profile_picture || u.educational_center?.icon || u.educationalCenter?.icon
            };
        });
    } catch (e) {
        console.error('Error fetching explore data:', e);
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
};

const daysUntil = (dateStr) => {
    if (!dateStr) return '';
    const diff = Math.ceil((new Date(dateStr) - new Date()) / (1000 * 60 * 60 * 24));
    if (diff === 0) return 'Hoy';
    if (diff === 1) return 'Mañana';
    if (diff < 0) return 'Finalizado';
    return `En ${diff} días`;
};

const viewPublication = (pub) => {
    router.push({ name: 'publication-details', params: { id: pub.id } });
};

const goToQuestion = (id) => router.push({ name: 'question', params: { id } });
const goToProfile = (id) => router.push({ name: 'profile', params: { id } });

const viewEvent = (event) => {
    if (!event) return;
    localStorage.setItem('selectedEvent', JSON.stringify(event));
    router.push({ name: 'event-details', params: { id: event.id } });
};

const getQuestionTag = (q) => {
    if (!q) return '';
    if (q.cycle_name) return q.cycle_name;
    if (q.category?.name) return q.category.name;
    const role = q.user?.role?.toLowerCase();
    if (role === 'student' || role === 'alumno') return 'Alumno';
    if (role === 'teacher' || role === 'profesor') return 'Profesor';
    if (role === 'admin' || role === 'administrador') return 'Admin';
    return 'Duda';
};

const getCenterName = (event) => {
    if (!event) return '';
    return event.center_name || event.creator?.institution_name || event.educational_center?.name || 'Comunidad';
};

const getEventImage = (event) => {
    if (!event || !event.image_url) return null;
    if (event.image_url.startsWith('http')) return event.image_url;
    const baseSrc = import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace(/\/api$/, '') : 'http://localhost:8000';
    return baseSrc + (event.image_url.startsWith('/') ? event.image_url : '/' + event.image_url);
};

const getProfileImage = (path) => {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    const baseSrc = import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace(/\/api$/, '') : 'http://localhost:8000';
    return baseSrc + (path.startsWith('/') ? path : '/' + path);
};

// Dynamic search filtering
const filteredQuestions = computed(() => {
    if (!searchQuery.value) return questions.value;
    const query = searchQuery.value.toLowerCase();
    return questions.value.filter(q => 
        (q.title && q.title.toLowerCase().includes(query)) ||
        (q.content && q.content.toLowerCase().includes(query)) ||
        (getQuestionTag(q).toLowerCase().includes(query))
    );
});

const filteredEvents = computed(() => {
    if (!searchQuery.value) return events.value;
    const query = searchQuery.value.toLowerCase();
    return events.value.filter(e => 
        (e.title && e.title.toLowerCase().includes(query)) ||
        (e.description && e.description.toLowerCase().includes(query)) ||
        (getCenterName(e).toLowerCase().includes(query))
    );
});

const filteredPublications = computed(() => {
    if (!searchQuery.value) return publications.value;
    const query = searchQuery.value.toLowerCase();
    return publications.value.filter(p => 
        (p.title && p.title.toLowerCase().includes(query)) ||
        (p.description && p.description.toLowerCase().includes(query)) ||
        (p.center_name && p.center_name.toLowerCase().includes(query))
    );
});

// Display up to 6 centers by default, toggle to expand with "Ver más"
const displayedCenters = computed(() => {
    return showAllCenters.value ? followCenters.value : followCenters.value.slice(0, 6);
});

// Persistent local follow state + actual Backend follow endpoint integration
const followedStates = ref({});
const toggleFollow = async (id) => {
    // Optimistic UI update
    followedStates.value[id] = !followedStates.value[id];
    localStorage.setItem('followed_centers', JSON.stringify(followedStates.value));
    
    try {
        const response = await post(`users/${id}/follow`);
        if (response && response.is_following !== undefined) {
            followedStates.value[id] = response.is_following;
            localStorage.setItem('followed_centers', JSON.stringify(followedStates.value));
        }
    } catch (e) {
        console.error('Error toggling follow relationship on backend:', e);
    }
};

onMounted(() => {
    fetchAllData();
    window.addEventListener('scroll', handleScroll);
    // Load followed state persistently from localStorage
    const savedFollows = localStorage.getItem('followed_centers');
    if (savedFollows) {
        try {
            followedStates.value = JSON.parse(savedFollows);
        } catch (e) {
            console.error('Error loading saved followed states:', e);
        }
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-20 lg:pt-0 flex flex-col min-h-screen">
            <div class="w-full max-w-screen-2xl mx-auto px-4 md:px-6 lg:px-8 py-6">

                <!-- Twitter-like 2-column layout -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- MAIN FEED COLUMN (Left) -->
                    <div class="col-span-1 lg:col-span-2 border-r border-white/5 pr-0 lg:pr-8">
                        
                        <!-- Search Bar header styled exactly like SearchBar component with dynamic scrolled background -->
                        <div 
                            :class="[
                                'sticky top-0 z-30 transition-all duration-350 py-3 mb-4',
                                isScrolled 
                                    ? 'bg-[#326465]/95 backdrop-blur-md border-b border-white/10 shadow-xl px-3 rounded-b-xl' 
                                    : 'bg-transparent border-b border-transparent'
                            ]"
                        >
                            <div class="group flex items-center bg-white/5 border border-white/10 rounded-[22px] px-5 py-3 transition-all focus-within:bg-white/10 focus-within:border-primary-normal/50 focus-within:ring-1 focus-within:ring-primary-normal/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-white/20 group-focus-within:text-primary-normal transition-colors">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <circle cx="10" cy="10" r="7" />
                                    <path d="M21 21l-6 -6" />
                                </svg>
                                <input 
                                    v-model="searchQuery" 
                                    type="text" 
                                    :placeholder="t.explore.searchPlaceholder || 'Buscar en TelamoNet...'" 
                                    class="ml-4 w-full flex-1 outline-none border-none bg-transparent text-sm font-bold text-white placeholder:text-white/20 tracking-tight"
                                />
                                <button v-if="searchQuery" @click="searchQuery = ''" class="flex items-center pl-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-white/40 hover:text-white transition-colors"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Twitter-style tabs - Compacted tab size (No Trends Tab) -->
                        <div class="flex border-b border-white/5 mb-6 overflow-x-auto select-none scrollbar-none">
                            <button 
                                @click="activeTab = 'para_ti'"
                                :class="['tab-btn', activeTab === 'para_ti' ? 'tab-btn-active' : '']"
                            >
                                {{ t.explore.forYou || 'Para ti' }}
                            </button>
                            <button 
                                @click="activeTab = 'eventos'"
                                :class="['tab-btn', activeTab === 'eventos' ? 'tab-btn-active' : '']"
                            >
                                {{ t.explore.events || 'Eventos' }}
                            </button>
                            <button 
                                @click="activeTab = 'publicaciones'"
                                :class="['tab-btn', activeTab === 'publicaciones' ? 'tab-btn-active' : '']"
                            >
                                {{ t.explore.publications || 'Publicaciones' }}
                            </button>
                            <button 
                                v-if="user?.role !== 'EU'"
                                @click="activeTab = 'preguntas'"
                                :class="['tab-btn', activeTab === 'preguntas' ? 'tab-btn-active' : '']"
                            >
                                {{ t.explore.questions || 'Preguntas' }}
                            </button>
                        </div>

                        <!-- SKELETON LOADER -->
                        <div v-if="loading" class="flex flex-col gap-1">
                            <div v-for="i in 5" :key="i" class="py-4 px-4 border-b border-white/5 animate-pulse">
                                <div class="h-3 bg-white/5 rounded w-1/4 mb-2"></div>
                                <div class="h-4 bg-white/10 rounded w-3/4 mb-2"></div>
                                <div class="h-3 bg-white/5 rounded w-1/3"></div>
                            </div>
                        </div>

                        <!-- TAB CONTENT STREAM -->
                        <div v-else>
                            
                            <!-- TAB 1: PARA TI (Mix with single most recent Event Banner) -->
                            <div v-if="activeTab === 'para_ti'">
                                <!-- Single Most Recent Event Banner (Placed Standalone at top of feed) -->
                                <div v-if="events.length > 0 && !searchQuery" 
                                     @click="viewEvent(events[0])"
                                     class="hero-trend-card mb-6 group cursor-pointer relative overflow-hidden rounded-2xl border border-white/5 aspect-[21/9]">
                                    <img v-if="events[0].image_url" :src="getEventImage(events[0])" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" alt="">
                                    <div v-else class="absolute inset-0 bg-gradient-to-br from-[#0f2828] to-[#1a2d37]"></div>
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent flex flex-col justify-end p-6">
                                        <p class="text-[#bae6fd] text-[11px] font-black uppercase tracking-widest mb-1 flex items-center gap-1">
                                            {{ t.explore.academicTrend || 'Académico · Tendencia' }}
                                        </p>
                                        <h2 class="text-2xl md:text-3xl font-black text-white leading-tight mb-2">{{ events[0].title }}</h2>
                                        <p class="text-white/60 text-xs md:text-sm font-semibold uppercase tracking-wider">{{ getCenterName(events[0]) }} · {{ formatDate(events[0].date) }}</p>
                                    </div>
                                </div>

                                <!-- Novedades de Centros (Standalone recent publications teaser widget in Para ti) -->
                                <div v-if="publications.length > 0 && !searchQuery" class="tab-content-container">
                                    <h3 class="px-5 py-4 text-white font-black text-lg border-b border-white/5">{{ t.explore.recentPublications || 'Novedades de centros' }}</h3>
                                    
                                    <div v-for="p in publications.slice(0, 2)" :key="'pt-p-'+p.id"
                                         @click="viewPublication(p)"
                                         class="trend-row">
                                        <div class="flex flex-col pr-8">
                                            <span class="trend-category">Novedad · {{ p.center_name }}</span>
                                            <span class="trend-topic">{{ p.title }}</span>
                                            <span class="trend-meta">{{ formatDate(p.created_at) }}</span>
                                        </div>
                                    </div>

                                    <button 
                                        @click="activeTab = 'publicaciones'" 
                                        class="w-full text-center py-4 text-xs font-black uppercase tracking-widest text-white hover:text-white/80 transition-colors cursor-pointer border-t border-white/5 bg-white/[0.01] hover:bg-white/[0.03]"
                                    >
                                        {{ t.explore.seeMorePublications || 'Ver más publicaciones →' }}
                                    </button>
                                </div>

                                <!-- "Ver eventos" Feed list styled exactly like the "Who to follow" widget (For External Users) -->
                                <div v-if="user?.role === 'EU'" class="tab-content-container">
                                    <h3 class="px-5 py-4 text-white font-black text-lg border-b border-white/5">{{ t.explore.seeEvents || 'Ver eventos' }}</h3>
                                    
                                    <!-- Dynamic Events only -->
                                    <div v-for="e in filteredEvents.slice(0, 4)" :key="'pt-e-'+e.id"
                                         @click="viewEvent(e)"
                                         class="trend-row">
                                        <div class="flex flex-col pr-8">
                                            <span class="trend-category">Evento Académico · {{ getCenterName(e) }}</span>
                                            <span class="trend-topic">{{ e.title }}</span>
                                            <span class="trend-meta">{{ formatDate(e.date) }} · {{ daysUntil(e.date) }}</span>
                                        </div>
                                        <button class="trend-options-btn">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                        </button>
                                    </div>

                                    <!-- Ver más eventos button at the end -->
                                    <button 
                                        @click="activeTab = 'eventos'" 
                                        class="w-full text-center py-4 text-xs font-black uppercase tracking-widest text-white hover:text-white/80 transition-colors cursor-pointer border-t border-white/5 bg-white/[0.01] hover:bg-white/[0.03]"
                                    >
                                        {{ t.explore.seeMoreEvents || 'Ver más eventos →' }}
                                    </button>
                                </div>

                                <!-- "Ver preguntas" Feed list styled exactly like the "Who to follow" widget (For other roles) -->
                                <div v-else class="tab-content-container">
                                    <h3 class="px-5 py-4 text-white font-black text-lg border-b border-white/5">{{ t.explore.seeQuestions || 'Ver preguntas' }}</h3>
                                    
                                    <!-- Dynamic Questions only -->
                                    <div v-for="q in filteredQuestions.slice(0, 4)" :key="'pt-q-'+q.id"
                                         @click="goToQuestion(q.id)"
                                         class="trend-row">
                                        <div class="flex flex-col pr-8">
                                            <span class="trend-category">Foro · {{ t.explore.trendIn || 'Tendencia en' }} {{ getQuestionTag(q) }}</span>
                                            <span class="trend-topic">{{ q.question || q.title }}</span>
                                            <span class="trend-meta">{{ q.answers ? q.answers.length : 0 }} {{ t.explore.answers || 'respuestas' }} · {{ t.explore.createdBy || 'Creado por' }} {{ q.user?.name ?? 'Usuario' }}</span>
                                        </div>
                                        <button class="trend-options-btn">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                        </button>
                                    </div>

                                    <!-- Ver más preguntas button at the end -->
                                    <button 
                                        @click="router.push({ name: 'foro' })" 
                                        class="w-full text-center py-4 text-xs font-black uppercase tracking-widest text-white hover:text-white/80 transition-colors cursor-pointer border-t border-white/5 bg-white/[0.01] hover:bg-white/[0.03]"
                                    >
                                        {{ t.explore.seeMoreQuestions || 'Ver más preguntas →' }}
                                    </button>
                                </div>
                            </div>

                            <!-- TAB 2: EVENTOS -->
                            <div v-if="activeTab === 'eventos'">
                                <div class="tab-content-container">
                                    <div v-if="filteredEvents.length === 0" class="py-12 text-center text-white/30">
                                        {{ t.explore.noMatchingEvents || 'No se encontraron eventos coincidentes.' }}
                                    </div>
                                    <div v-else>
                                        <!-- List of up to 6 events at a time -->
                                        <div v-for="e in filteredEvents.slice(0, 6)" :key="e.id" 
                                             @click="viewEvent(e)"
                                             class="trend-row">
                                            <div class="flex flex-col pr-8">
                                                <span class="trend-category">Evento Académico · {{ getCenterName(e) }}</span>
                                                <span class="trend-topic">{{ e.title }}</span>
                                                <span class="trend-meta">{{ formatDate(e.date) }} · {{ daysUntil(e.date) }}</span>
                                            </div>
                                            <button class="trend-options-btn">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                            </button>
                                        </div>

                                        <!-- See more events redirection button -->
                                        <button 
                                            @click="router.push({ name: 'event' })" 
                                            class="w-full text-center py-4 text-xs font-black uppercase tracking-widest text-white hover:text-white/80 transition-colors cursor-pointer border-t border-white/5 bg-white/[0.01] hover:bg-white/[0.03]"
                                        >
                                            {{ t.explore.seeMoreEvents || 'Ver más eventos →' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: PREGUNTAS -->
                            <div v-if="activeTab === 'preguntas'">
                                <div class="tab-content-container">
                                    <div v-if="filteredQuestions.length === 0" class="py-12 text-center text-white/30">
                                        {{ t.explore.noMatchingQuestions || 'No se encontraron dudas en el foro.' }}
                                    </div>
                                    <div v-else>
                                        <!-- List of up to 6 questions at a time -->
                                        <div v-for="q in filteredQuestions.slice(0, 6)" :key="q.id" 
                                             @click="goToQuestion(q.id)"
                                             class="trend-row">
                                            <div class="flex flex-col pr-8">
                                                <span class="trend-category">Foro · {{ getQuestionTag(q) }}</span>
                                                <span class="trend-topic">{{ q.question || q.title }}</span>
                                                <span class="trend-meta">{{ q.answers ? q.answers.length : 0 }} {{ t.explore.answers || 'respuestas' }} · {{ t.explore.by || 'Por' }} {{ q.user?.name ?? 'Usuario' }}</span>
                                            </div>
                                            <button class="trend-options-btn">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                            </button>
                                        </div>

                                        <!-- See more questions redirection button -->
                                        <button 
                                            @click="router.push({ name: 'foro' })" 
                                            class="w-full text-center py-4 text-xs font-black uppercase tracking-widest text-white hover:text-white/80 transition-colors cursor-pointer border-t border-white/5 bg-white/[0.01] hover:bg-white/[0.03]"
                                        >
                                            {{ t.explore.seeMoreQuestions || 'Ver más preguntas →' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 4: PUBLICACIONES -->
                            <div v-if="activeTab === 'publicaciones'">
                                <div class="tab-content-container">
                                    <div v-if="filteredPublications.length === 0" class="py-12 text-center text-white/30">
                                        {{ t.explore.noMatchingPublications || 'No se encontraron publicaciones coincidentes.' }}
                                    </div>
                                    <div v-else>
                                        <div v-for="p in filteredPublications.slice(0, 6)" :key="p.id" 
                                             @click="viewPublication(p)"
                                             class="trend-row">
                                            <div class="flex flex-col pr-8">
                                                <span class="trend-category">Novedad · {{ p.center_name }}</span>
                                                <span class="trend-topic">{{ p.title }}</span>
                                                <span class="trend-meta">{{ formatDate(p.created_at) }}</span>
                                            </div>
                                            <button class="trend-options-btn">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                            </button>
                                        </div>

                                        <button 
                                            @click="router.push({ name: 'publications' })" 
                                            class="w-full text-center py-4 text-xs font-black uppercase tracking-widest text-white hover:text-white/80 transition-colors cursor-pointer border-t border-white/5 bg-white/[0.01] hover:bg-white/[0.03]"
                                        >
                                            {{ t.explore.seeMorePublications || 'Ver más publicaciones →' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- SIDEBAR COLUMN (Right - Desktop only) -->
                    <div class="hidden lg:flex flex-col gap-6 col-span-1">
                        
                        <!-- "A quién seguir" Sidebar Widget - Dynamic users with role 'EI' (Spacious & showing 6) -->
                        <div v-if="followCenters.length > 0" class="sidebar-widget">
                            <h3 class="widget-title">{{ t.explore.whoToFollow || 'A quién seguir' }}</h3>
                            <div class="flex flex-col gap-4">
                                <div v-for="center in displayedCenters" :key="center.id" class="flex items-center justify-between py-2 border-b border-white/[0.02] last:border-0 animate-fade-in">
                                    
                                    <!-- Clickable profile area -->
                                    <div @click="goToProfile(center.id)" class="flex items-center gap-3 cursor-pointer group/item flex-1 pr-2">
                                        <!-- Dynamic letter avatar or profile image -->
                                        <img v-if="center.profile_picture" :src="getProfileImage(center.profile_picture)" class="w-11 h-11 rounded-full object-cover border border-[#326465]/30 group-hover/item:border-[#326465]/80 transition-colors" alt="">
                                        <div v-else class="w-11 h-11 rounded-full bg-[#326465]/40 border border-[#326465]/50 flex items-center justify-center font-black text-white text-base group-hover/item:bg-[#326465]/60 transition-colors">
                                            {{ center.avatarLetter }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-white flex items-center gap-1 leading-tight group-hover/item:text-[#bae6fd] transition-colors">
                                                {{ center.name }}
                                                <svg v-if="center.verified" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" class="text-[#bae6fd]"><path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                            </span>
                                            <span class="text-xs text-white/35 font-medium leading-none mt-1 group-hover/item:text-white/50 transition-colors">{{ center.alias }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- follow/unfollow toggle button (Independent & Min-width to prevent overlap layout shift) -->
                                    <button 
                                        @click="toggleFollow(center.id)"
                                        @mouseenter="hoveredCenterId = center.id"
                                        @mouseleave="hoveredCenterId = null"
                                        :class="['follow-btn', followedStates[center.id] ? 'follow-btn-active' : '']"
                                        class="min-w-24 text-center"
                                    >
                                        <span v-if="followedStates[center.id]">
                                            {{ hoveredCenterId === center.id ? (t.explore.unfollow || 'Dejar de seguir') : (t.explore.following || 'Siguiendo') }}
                                        </span>
                                        <span v-else>
                                            {{ t.explore.follow || 'Seguir' }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- "Ver más" button to toggle dynamic centers list expansion -->
                            <button 
                                v-if="followCenters.length > 6" 
                                @click="showAllCenters = !showAllCenters" 
                                class="w-full text-center py-2.5 mt-3 text-xs font-black uppercase tracking-widest text-white hover:text-white/80 transition-colors cursor-pointer border-t border-white/5 bg-white/[0.01] hover:bg-white/[0.03] rounded-lg"
                            >
                                {{ showAllCenters ? (t.explore.seeLess || 'Ver menos') : (t.explore.seeMoreCenters || 'Ver más centros') }}
                            </button>
                        </div>

                        <!-- Footer Links -->
                        <div class="px-4 text-[11px] text-white/20 leading-relaxed font-semibold">
                            Términos de Servicio · Política de Privacidad · Uso de Cookies · Más opciones · © 2026 TelamoNet.
                        </div>

                    </div>

                </div>

            </div>
            
        </main>
    </div>
</template>

<style scoped>
.tab-btn {
    flex: 1;
    text-align: center;
    padding: 10px 0; /* Compact tab padding */
    font-size: 13px; /* Smaller font-size for tabs */
    font-weight: 800;
    color: rgba(255, 255, 255, 0.45);
    border-bottom: 2px solid transparent;
    cursor: pointer;
    background: transparent;
    transition: all 0.2s ease;
    white-space: nowrap;
    outline: none;
}
.tab-btn:hover {
    color: rgba(255, 255, 255, 0.85);
    background-color: rgba(255, 255, 255, 0.02);
}
.tab-btn-active {
    color: #fff !important;
    border-bottom-color: #326465 !important; /* TelamoNet brand teal bottom line */
}

.tab-content-container {
    background-color: rgba(26, 45, 55, 0.45); /* Exact match for sidebar widget glass background */
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    overflow: hidden; /* Crucial to keep hover highlights respecting card borders */
    margin-bottom: 24px;
}

.trend-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 16px 20px; /* Restored elegant and spacious padding for premium look! */
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    cursor: pointer;
    transition: background-color 0.2s ease;
}
.trend-row:hover {
    background-color: rgba(255, 255, 255, 0.02);
}

.trend-category {
    font-size: 11px; /* Restored beautiful category size */
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.3);
}

.trend-topic {
    font-size: 16px; /* Restored bold & premium topic title size */
    font-weight: 900;
    color: #fff;
    margin-top: 3px;
    letter-spacing: -0.01em;
}

.trend-meta {
    font-size: 12px; /* Restored meta info size */
    color: rgba(255, 255, 255, 0.35);
    margin-top: 3px;
}

.trend-options-btn {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.3);
    cursor: pointer;
    padding: 6px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.trend-options-btn:hover {
    background-color: rgba(50, 100, 101, 0.1);
    color: #bae6fd;
}

.sidebar-widget {
    background-color: rgba(26, 45, 55, 0.45); /* Predefined --forum-card-bg base transparentized */
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 20px;
}

.widget-title {
    font-size: 18px;
    font-weight: 900;
    color: #fff;
    margin-bottom: 16px;
    letter-spacing: -0.02em;
}

.follow-btn {
    background-color: #fff;
    color: #000;
    font-size: 12px;
    font-weight: 900;
    padding: 6px 16px;
    border-radius: 9999px;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}
.follow-btn:hover {
    background-color: rgba(255, 255, 255, 0.9);
}
.follow-btn-active {
    background-color: transparent !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
}
.follow-btn-active:hover {
    background-color: rgba(255, 0, 0, 0.08) !important;
    border-color: rgba(255, 0, 0, 0.3) !important;
    color: #ff4d4d !important;
}

.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>