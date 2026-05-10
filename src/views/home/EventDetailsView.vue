<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import PageHeader from '@/components/common/PageHeader.vue';
    import { ref, onMounted, computed } from 'vue'
    import { token as authToken } from '@/stores/auth'
    import { useTranslations } from '../../composables/useTranslations'

    const eventDetails = ref(null)
    const direccion = ref('')
    const mapaUrl = ref('')
    const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
    const { t } = useTranslations()
    const toast = ref({ show: false, msg: '', type: 'success' })

    const showToast = ({ msg, type = 'success' }) => {
        const translated = msg.split('.').reduce((obj, key) => obj?.[key], t.value) || msg;
        toast.value = { show: true, msg: translated, type }
        setTimeout(() => toast.value.show = false, 3000)
    }

    const token = computed(() => authToken.value || localStorage.getItem('token'))
    const headers = computed(() => ({
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token.value}`
    }))

    const handleJoin = async () => {
        if (!eventDetails.value) return;
        if (!token.value) {
            alert('Debes iniciar sesión para unirte a eventos');
            return;
        }
        try {
            const res = await fetch(`${apiBase}/events/${eventDetails.value.id}/join`, {
                method: 'POST',
                headers: headers.value
            });
            if (res.ok) {
                const data = await res.json();
                eventDetails.value.joined = data.joined;
                eventDetails.value.participants_count = data.count;
                localStorage.setItem('selectedEvent', JSON.stringify(eventDetails.value));
                showToast({ msg: eventDetails.value.joined ? t.events.joined : t.events.left });
            } else {
                const data = await res.json();
                showToast({ msg: data.message || 'Error', type: 'error' });
            }
        } catch (e) {
            showToast({ msg: t.events.networkError, type: 'error' });
        }
    }

    onMounted(() => {
        const saved = localStorage.getItem('selectedEvent');
        if (saved) {
            try {
                const event = JSON.parse(saved);
                eventDetails.value = event;
                direccion.value = event.location || event.center_name || 'CIFP Zonzamas, Arrecife, Lanzarote';
                actualizarMapa();
            } catch (e) {
                console.error("Error parsing event");
            }
        }
    })

    function actualizarMapa() {
        if (!direccion.value) return
        const searchQuery = direccion.value.includes(',') ? direccion.value : `${direccion.value}, Lanzarote`;
        mapaUrl.value = `https://maps.google.com/maps?q=${encodeURIComponent(searchQuery)}&output=embed`
    }
</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-20 lg:pt-0 flex flex-col min-h-screen">
            <PageHeader 
                :title="t.events.title"  
                :subtitle="t.events.subtitle"
                noMargin
            >
                <template #headerActions>
                    <button @click="handleJoin" 
                        :class="['flex justify-center items-center gap-2 p-4 cursor-pointer rounded-2xl duration-300 ease-in shadow-xl min-w-[180px] w-full md:w-auto font-black uppercase tracking-widest text-[10px]', 
                                 eventDetails?.joined ? 'bg-success-normal hover:bg-success-normal-hover text-white' : 'bg-accent-normal hover:bg-accent-normal-hover text-white']">
                        <span class="material-symbols-outlined !text-3xl text-white">{{ eventDetails?.joined ? 'check_circle' : 'local_activity' }}</span>
                        {{ eventDetails?.joined ? 'Anotado / Dentro' : 'Participar' }}
                    </button>
                </template>
            </PageHeader>

            <section class="flex flex-col gap-8 text-white w-full max-w-screen-2xl mx-auto px-6 lg:px-14 mb-20">
                <div class="flex flex-col sm:flex-row gap-8 sm:gap-14 w-fit mt-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined bg-accent-normal py-2 px-3 !text-3xl rounded-2xl text-white">school</span>
                        <div>
                            <p class="font-bold text-xs">Institución educativa</p>
                            <p>{{ eventDetails?.center_name || 'Desconocido' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined bg-accent-normal py-2 px-3 !text-3xl rounded-2xl text-white">schedule</span>
                        <div>
                            <p class="font-bold text-xs">Hora inicio - Hora fin</p>
                            <p>{{ eventDetails?.start_time?.substring(0,5) || '--:--' }} - {{ eventDetails?.end_time?.substring(0,5) || '--:--' }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-8 bg-white/5 border border-white/10 rounded-3xl p-6 md:p-10">
                    <img v-if="eventDetails?.image_url" :src="eventDetails.image_url" class="w-full lg:w-2/5 rounded-3xl object-cover" alt="Event Image">
                    <div v-else class="w-full lg:w-2/5 rounded-3xl bg-secondary-normal/20 flex items-center justify-center border border-secondary-normal/30 min-h-[200px]">
                        <span class="material-symbols-outlined text-6xl text-white/30">image</span>
                    </div>
                    <div class="flex flex-col gap-8 flex-1">
                        <div class="flex flex-col gap-4">
                            <h2 class="text-3xl font-black uppercase tracking-tighter">{{ eventDetails?.title || 'Detalles del Evento' }}</h2>
                            <p class="max-w-3xl text-white/80 whitespace-pre-line text-sm md:text-base leading-relaxed">{{ eventDetails?.description || 'No hay descripción disponible para este evento.' }}</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-8 sm:gap-12">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined bg-[#0f2828] py-2 px-3 !text-3xl rounded-2xl text-white">groups</span>
                                <div>
                                    <p class="font-bold text-xs">Participantes</p>
                                    <p>{{ eventDetails?.participants_count || 0 }} admitidos</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined bg-[#0f2828] py-2 px-3 !text-3xl rounded-2xl text-white">location_on</span>
                                <div>
                                    <p class="font-bold text-xs">Ubicación</p>
                                    <p>{{ eventDetails?.location || 'Ubicación no disponible' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4 mt-8">
                    <h3 class="text-3xl font-bold mx-auto mb-2 text-white">Google Maps</h3>
                    <iframe
                        :src="mapaUrl"
                        class="w-full lg:w-4/5 h-[300px] md:h-[450px] rounded-2xl mx-auto"
                        style="border:0"
                        loading="lazy"
                        allowfullscreen
                    ></iframe>
                </div>
                <div class="mt-4 flex justify-center md:justify-start">
                    <RouterLink to="/event" class="flex justify-center items-center gap-2 bg-accent-normal hover:bg-accent-normal-hover text-white px-8 py-3.5 cursor-pointer rounded-2xl duration-300 shadow-lg font-black uppercase tracking-widest text-[10px]">
                        <span class="material-symbols-outlined !text-2xl text-white">arrow_back</span>
                        {{ t.nav.back }}
                    </RouterLink>
                </div>
            </section>

            <!-- Toast Notification -->
            <div v-if="toast.show" 
                :class="['fixed top-6 left-1/2 -translate-x-1/2 z-[200] px-6 py-3 rounded-xl shadow-2xl font-black uppercase tracking-widest text-xs transition-all duration-300 border border-white/10', 
                         toast.type === 'error' ? 'bg-error-normal text-white' : 'bg-secondary-normal text-white']">
                {{ toast.msg }}
            </div>
        </main>
    </div>
</template>