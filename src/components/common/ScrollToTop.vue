<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const showScrollButton = ref(false);

const handleScroll = () => {
    showScrollButton.value = window.scrollY > 300;
};

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <button
        v-if="showScrollButton"
        @click="scrollToTop"
        class="fixed bottom-6 right-6 lg:bottom-10 lg:right-10 z-[100] bg-accent-normal hover:bg-accent-normal-hover text-white p-3 rounded-full shadow-2xl cursor-pointer"
        title="Volver arriba"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="m18 15-6-6-6 6"/>
        </svg>
    </button>
</template>
