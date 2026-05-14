<script setup>
import { onMounted, watch } from 'vue';
import { loadAnalytics } from '@/utils/analytics';
import { refreshUser, user } from '@/stores/auth';
import { unreadCount } from '@/stores/notifications';
import { useSocket } from '@/composables/useSocket';
import CookieBanner from '@/components/CookieBanner.vue';
import ScrollToTop from '@/components/common/ScrollToTop.vue';

const { socket, connect, on } = useSocket();

onMounted(() => {
    loadAnalytics();
    refreshUser();
});

watch(() => user?.value?.id, (userId) => {
    if (userId) {
        connect(`user:${userId}`);
        on('notification', (notif) => {
            unreadCount.value++;
        });
    }
}, { immediate: true });
</script>

<!--Desde la App se maneja todo lo que se va a ver, usando <router-view /> es lo que me permite elegir que vista va a ver el usuario-->
<!--Ir a /router/index.js para ver la estructura del router-->
<template>
    <div id="app">
        <main class="main-content">
            <router-view />
        </main>
        <CookieBanner />
        <ScrollToTop />
    </div>
</template>

<style>
    ::-webkit-scrollbar { 
        display: none; 
    }
</style>