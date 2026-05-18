<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
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
    <transition
        enter-active-class="transition duration-300 ease-out transform"
        enter-from-class="opacity-0 scale-50 translate-y-5"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition duration-300 ease-in transform"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-50 translate-y-5"
    >
        <button
            v-if="showScrollButton"
            @click="scrollToTop"
            class="fixed right-6 lg:right-10 z-50 bg-accent-normal hover:bg-accent-normal-hover text-white p-3 rounded-full shadow-2xl cursor-pointer transition-all duration-300"
            :class="route?.name === 'question' ? 'bottom-28 lg:bottom-32' : 'bottom-6 lg:bottom-10'"
            title="Volver arriba"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m18 15-6-6-6 6"/>
            </svg>
        </button>
    </transition>
</template>
