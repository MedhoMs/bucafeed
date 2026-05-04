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
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4 scale-90"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-4 scale-90"
    >
        <button
            v-if="showScrollButton"
            @click="scrollToTop"
            class="fixed bottom-24 right-6 lg:bottom-28 lg:right-10 z-[45] bg-emerald-500 hover:bg-emerald-400 text-white p-3 rounded-full shadow-[0_4px_14px_rgba(16,185,129,0.3)] backdrop-blur-sm transition-all hover:-translate-y-1 active:scale-95"
            title="Volver arriba"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m18 15-6-6-6 6"/>
            </svg>
        </button>
    </Transition>
</template>
