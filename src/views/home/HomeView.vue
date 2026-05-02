<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import EventCarousel from '../../components/home/EventCarousel.vue';
    import PageHeader from '@/components/common/PageHeader.vue';
    import { ref, onMounted } from 'vue';
    import { useTranslations } from '../../composables/useTranslations'

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
                // Ordenar por ID descendente (más nuevos primero) y tomar los 10 primeros
                rawEvents.value = data.sort((a, b) => b.id - a.id).slice(0, 10);
            }
        } catch (e) {
            console.error("Error cargando eventos:", e);
        } finally {
            loading.value = false;
        }
    };

    onMounted(async () => {
        await fetchEvents();
    });
</script>

<template>
    <NavBar></NavBar>
    <main class="flex flex-col min-h-screen lg:pl-75">
        <PageHeader 
            :title="t.home?.welcome || 'Bienvenido a TelamoNet'" 
            :subtitle="t.home?.subtitle || 'Tu red social académica.'"
            noMargin
        />

        <EventCarousel :events="rawEvents" :loading="loading" />
    </main>
</template>

<style scoped>
</style>