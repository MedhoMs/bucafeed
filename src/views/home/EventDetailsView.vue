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
                showToast({ msg: eventDetails.value.joined ? 'Te has unido al evento' : 'Has abandonado el evento' });
            } else {
                const data = await res.json();
                showToast({ msg: data.message || 'Error', type: 'error' });
            }
        } catch (e) {
            showToast({ msg: 'Error de red', type: 'error' });
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
    <NavBar></NavBar>
    <main class="flex flex-col min-h-screen lg:pl-75">
        <div class="flex items-center">
            <PageHeader 
                title="Eventos Académicos"
                subtitle="Participa en las actividades y charlas de tu centro."
            />
            <div class="flex items-center gap-4 mt-12 mr-20">
                <button @click="handleJoin" 
                    :class="['flex justify-center items-center gap-2 p-4 cursor-pointer rounded-2xl duration-300 ease-in shadow-xl min-w-[180px]', 
                             eventDetails?.joined ? 'bg-cyan-600 hover:bg-cyan-500 text-white' : 'bg-[#0f2828] hover:bg-[#507a8f] text-white']">
                    <span class="material-symbols-outlined !text-3xl">{{ eventDetails?.joined ? 'check_circle' : 'local_activity' }}</span>
                    {{ eventDetails?.joined ? 'Anotado / Dentro' : 'Participar' }}
                </button>
            </div>
        </div>

        <section class="flex flex-col gap-8 text-white w-full max-w-screen-2xl mx-auto pl-4 mb-20">
            <div class="flex gap-14 w-fit mt-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined bg-[#0f2828] py-2 px-3 !text-3xl rounded-2xl">school</span>
                    <div>
                        <p class="font-bold text-xs">Institución educativa</p>
                        <p>{{ eventDetails?.center_name || 'Desconocido' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined bg-[#0f2828] py-2 px-3 !text-3xl rounded-2xl">schedule</span>
                    <div>
                        <p class="font-bold text-xs">Hora inicio - Hora fin</p>
                        <p>{{ eventDetails?.start_time?.substring(0,5) || '--:--' }} - {{ eventDetails?.end_time?.substring(0,5) || '--:--' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-8 bg-white/5 border border-white/10 rounded-3xl p-10">
                <img v-if="eventDetails?.image_url" :src="eventDetails.image_url" class="w-2/5 rounded-3xl object-cover" alt="Event Image">
                <div v-else class="w-2/5 rounded-3xl bg-[#406071]/20 flex items-center justify-center border border-[#406071]/30">
                    <span class="material-symbols-outlined text-6xl text-white/30">image</span>
                </div>
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col gap-4">
                        <h2 class="text-3xl font-bold">{{ eventDetails?.title || 'Detalles del Evento' }}</h2>
                        <p class="w-200 text-white/80 whitespace-pre-line">{{ eventDetails?.description || 'No hay descripción disponible para este evento.' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined bg-[#0f2828] py-2 px-3 !text-3xl rounded-2xl">groups</span>
                        <div>
                            <p class="font-bold text-xs">Participantes</p>
                            <p>{{ eventDetails?.participants_count || 0 }} admitidos</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 mt-8">
                <h3 class="text-3xl font-bold mx-auto mb-2">Ubicación</h3>
                <p v-if="eventDetails?.location" class="text-white/60 text-xs text-center mb-2 uppercase tracking-[0.2em] font-black">
                    {{ eventDetails.location }}
                </p>
                <iframe
                    :src="mapaUrl"
                    class="w-4/5 h-[450px] rounded-2xl mx-auto"
                    style="border:0"
                    loading="lazy"
                    allowfullscreen
                ></iframe>
            </div>
            <div class="mt-4 flex justify-start">
                        <RouterLink to="/event" class="flex justify-center items-center gap-2 bg-[#0f2828] hover:bg-[#507a8f] text-white px-6 py-3 cursor-pointer rounded-[14px] duration-100 ease-in w-fit">
                            <span class="material-symbols-outlined !text-2xl">arrow_back</span>
                            Volver
                        </RouterLink>
                    </div>
        </section>

        <!-- Toast Notification -->
        <div v-if="toast.show" 
            :class="['fixed top-6 left-1/2 -translate-x-1/2 z-[200] px-6 py-3 rounded-xl shadow-2xl font-semibold text-sm transition-all duration-300', 
                     toast.type === 'error' ? 'bg-red-500 text-white' : 'bg-emerald-500 text-white']">
            {{ toast.msg }}
        </div>
    </main>
</template>