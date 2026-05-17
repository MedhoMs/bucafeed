<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import PageHeader from '@/components/common/PageHeader.vue';
    import { ref, onMounted, computed } from 'vue'
    import { token as authToken, user } from '@/stores/auth'
    import { useTranslations } from '../../composables/useTranslations'
    import UnverifiedBanner from '@/components/common/UnverifiedBanner.vue'

    const isUnverified = computed(() => user.value?.role === 'Student' && user.value?.is_verified === false)

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
        if (!eventDetails.value || isUnverified.value) return;
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

    const downloadPDF = () => {
        console.log('Generando PDF para el evento:', eventDetails.value?.id);
        if (!eventDetails.value) {
            console.error('No hay detalles del evento cargados');
            return;
        }
        const url = `${apiBase}/events/${eventDetails.value.id}/pdf`;
        window.open(url, '_blank');
    }
</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-20 lg:pt-0 flex flex-col min-h-screen">
            <!-- Banner compacto superior si no está verificado -->
            <div class="px-6 lg:px-14 pt-6" v-if="isUnverified">
                <UnverifiedBanner 
                    compact 
                    message="Puedes ver la información del evento, pero no podrás inscribirte hasta que tu centro verifique tu identidad."
                />
            </div>
            <PageHeader 
                :title="t.events.title"  
                :subtitle="t.events.subtitle"
                noMargin
            >
                <template #headerActions>
                    <button @click="isUnverified ? null : handleJoin()" 
                        :disabled="isUnverified"
                        :class="['flex justify-center items-center gap-2 p-4 duration-300 ease-in shadow-xl min-w-[180px] w-full md:w-auto font-black uppercase tracking-widest text-[10px] rounded-2xl', 
                                 isUnverified ? 'bg-amber-500/20 text-amber-500/50 cursor-not-allowed border border-amber-500/20' :
                                 (eventDetails?.joined ? 'bg-success-normal hover:bg-success-normal-hover text-white cursor-pointer' : 'bg-accent-normal hover:bg-accent-normal-hover text-white cursor-pointer')]">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor" :class="isUnverified ? 'text-amber-500/30' : 'text-white/80'"><path d="m368-320 112-84 110 84-42-136 112-88H524l-44-136-44 136H300l110 88-42 136ZM160-160q-33 0-56.5-23.5T80-240v-135q0-11 7-19t18-10q24-8 39.5-29t15.5-47q0-26-15.5-47T105-556q-11-2-18-10t-7-19v-135q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v135q0 11-7 19t-18 10q-24 8-39.5 29T800-480q0 26 15.5 47t39.5 29q11 2 18 10t7 19v135q0 33-23.5 56.5T800-160H160Zm0-80h640v-102q-37-22-58.5-58.5T720-480q0-43 21.5-79.5T800-618v-102H160v102q37 22 58.5 58.5T240-480q0 43-21.5 79.5T160-342v102Zm320-240Z"/></svg>
                        {{ isUnverified ? 'Bloqueado (Sin verificar)' : (eventDetails?.joined ? 'Anotado / Dentro' : 'Participar') }}
                    </button>
                </template>
            </PageHeader>

            <section class="flex flex-col gap-8 text-white w-full max-w-screen-2xl mx-auto px-6 lg:px-14 mb-20">
                <div class="flex flex-col sm:flex-row gap-8 sm:gap-14 w-fit mt-4">
                    <div class="flex items-center gap-2">
                        <svg class="bg-accent-normal p-3 rounded-2xl" xmlns="http://www.w3.org/2000/svg" height="56px" viewBox="0 -960 960 960" width="56px" fill="#e3e3e3"><path d="M480-144 216-276v-240L48-600l432-216 432 216v312h-72v-276l-96 48v240L480-144Zm0-321 271-135-271-135-271 135 271 135Zm0 240 192-96v-159l-192 96-192-96v159l192 96Zm0-240Zm0 81Zm0 0Z"/></svg>
                        <div>
                            <p class="font-bold text-xs">Institución educativa</p>
                            <p>{{ eventDetails?.center_name || 'Desconocido' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="bg-accent-normal p-3 rounded-2xl" xmlns="http://www.w3.org/2000/svg" height="56px" viewBox="0 -960 960 960" width="56px" fill="#e3e3e3"><path d="m627-287 45-45-159-160v-201h-60v225l174 181ZM480-80q-82 0-155-31.5t-127.5-86Q143-252 111.5-325T80-480q0-82 31.5-155t86-127.5Q252-817 325-848.5T480-880q82 0 155 31.5t127.5 86Q817-708 848.5-635T880-480q0 82-31.5 155t-86 127.5Q708-143 635-111.5T480-80Zm0-400Zm0 340q140 0 240-100t100-240q0-140-100-240T480-820q-140 0-240 100T140-480q0 140 100 240t240 100Z"/></svg>
                        <div>
                            <p class="font-bold text-xs">Hora inicio - Hora fin</p>
                            <p>{{ eventDetails?.start_time?.substring(0,5) || '--:--' }} - {{ eventDetails?.end_time?.substring(0,5) || '--:--' }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-8 bg-white/5 border border-white/10 rounded-3xl p-6 md:p-10">
                    <img v-if="eventDetails?.image_url" :src="eventDetails.image_url" class="w-auto lg:max-w-[40%] max-h-[300px] rounded-3xl object-contain border border-white/10" alt="Event Image">
                    <div v-else class="w-full lg:w-2/5 h-[300px] rounded-3xl bg-secondary-normal/20 flex items-center justify-center border border-secondary-normal/30">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#B7B7B7"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm40-80h480L570-480 450-320l-90-120-120 160Zm-40 80v-560 560Z"/></svg>
                    </div>
                    <div class="flex flex-col gap-8 flex-1">
                        <div class="flex flex-col gap-4">
                            <h2 class="text-3xl font-black uppercase tracking-tighter">{{ eventDetails?.title || 'Detalles del Evento' }}</h2>
                            <p class="max-w-3xl text-white/80 whitespace-pre-line text-sm md:text-base leading-relaxed">{{ eventDetails?.description || 'No hay descripción disponible para este evento.' }}</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-8 sm:gap-12">
                            <div class="flex items-center gap-2">
                                <svg class="bg-accent-normal p-3 rounded-2xl" xmlns="http://www.w3.org/2000/svg" height="56px" viewBox="0 -960 960 960" width="56px" fill="#e3e3e3"><path d="M0-240v-53q0-38.57 41.5-62.78Q83-380 150.38-380q12.16 0 23.39.5t22.23 2.15q-8 17.35-12 35.17-4 17.81-4 37.18v65H0Zm240 0v-65q0-32 17.5-58.5T307-410q32-20 76.5-30t96.5-10q53 0 97.5 10t76.5 30q32 20 49 46.5t17 58.5v65H240Zm540 0v-65q0-19.86-3.5-37.43T765-377.27q11-1.73 22.17-2.23 11.17-.5 22.83-.5 67.5 0 108.75 23.77T960-293v53H780Zm-480-60h360v-6q0-37-50.5-60.5T480-390q-79 0-129.5 23.5T300-305v5ZM149.57-410q-28.57 0-49.07-20.56Q80-451.13 80-480q0-29 20.56-49.5Q121.13-550 150-550q29 0 49.5 20.5t20.5 49.93q0 28.57-20.5 49.07T149.57-410Zm660 0q-28.57 0-49.07-20.56Q740-451.13 740-480q0-29 20.56-49.5Q781.13-550 810-550q29 0 49.5 20.5t20.5 49.93q0 28.57-20.5 49.07T809.57-410ZM480-480q-50 0-85-35t-35-85q0-51 35-85.5t85-34.5q51 0 85.5 34.5T600-600q0 50-34.5 85T480-480Zm.35-60Q506-540 523-557.35t17-43Q540-626 522.85-643t-42.5-17q-25.35 0-42.85 17.15t-17.5 42.5q0 25.35 17.35 42.85t43 17.5ZM480-300Zm0-300Z"/></svg>
                                <div>
                                    <p class="font-bold text-xs">Participantes</p>
                                    <p>{{ eventDetails?.participants_count || 0 }} admitidos</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="bg-accent-normal p-3 rounded-2xl" xmlns="http://www.w3.org/2000/svg" height="56px" viewBox="0 -960 960 960" width="56px" fill="#e3e3e3"><path d="M529.5-510.5Q550-531 550-560t-20.5-49.5Q509-630 480-630t-49.5 20.5Q410-589 410-560t20.5 49.5Q451-490 480-490t49.5-20.5ZM480-159q133-121 196.5-219.5T740-552q0-118-75.5-193T480-820q-109 0-184.5 75T220-552q0 75 65 173.5T480-159Zm0 79Q319-217 239.5-334.5T160-552q0-150 96.5-239T480-880q127 0 223.5 89T800-552q0 100-79.5 217.5T480-80Zm0-480Z"/></svg>
                                <div>
                                    <p class="font-bold text-xs">Ubicación</p>
                                    <p>{{ eventDetails?.location || 'Ubicación no disponible' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button @click="downloadPDF" class="flex items-center gap-2 mt-10 self-center w-fit bg-accent-normal hover:bg-accent-normal-hover text-white px-8 py-3.5 cursor-pointer rounded-full duration-300 shadow-lg font-black tracking-widest text-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF"><path d="m560-240-56-58 142-142H160v-80h486L504-662l56-58 240 240-240 240Z"/></svg>
                    Leer Folleto del Evento
                </button>

                <div class="flex flex-col gap-4 mt-8">
                    <h3 class="text-3xl font-black uppercase mx-auto w-fit tracking-tighter mb-2 text-white">Google Maps</h3>
                    <iframe
                        :src="mapaUrl"
                        class="w-full h-[300px] md:h-[450px] rounded-3xl border border-white/10 shadow-lg"
                        style="border:0"
                        loading="lazy"
                        allowfullscreen
                    ></iframe>
                </div>
                <div class="mt-4 flex justify-center md:justify-start">
                    <RouterLink to="/event" class="flex justify-center items-center gap-2 bg-accent-normal hover:bg-accent-normal-hover text-white px-8 py-3.5 cursor-pointer rounded-2xl duration-300 shadow-lg font-black uppercase tracking-widest text-[10px]">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="m274-450 248 248-42 42-320-320 320-320 42 42-248 248h526v60H274Z"/></svg>
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