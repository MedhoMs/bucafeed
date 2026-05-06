<script setup>
import { onMounted } from 'vue';
import { loadAnalytics } from '@/utils/analytics';
import { refreshUser } from '@/stores/auth';
import CookieBanner from '@/components/CookieBanner.vue';
import ScrollToTop from '@/components/common/ScrollToTop.vue';

onMounted(() => {
    loadAnalytics();
    refreshUser(); // Sincroniza rol y datos del usuario con la BD al arrancar
});
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