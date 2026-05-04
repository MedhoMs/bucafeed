<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    noMargin: { type: Boolean, default: false }
});

const isScrolled = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div v-if="title || subtitle" class="w-full px-6 lg:px-14 max-w-screen-2xl mx-auto pt-8 md:pt-12 mb-4 transition-all duration-500 text-white">
        <h1 class="text-3xl md:text-4xl font-black tracking-tight">{{ title }}</h1>
        <p class="text-white/50 text-sm md:text-base font-medium mt-1">{{ subtitle }}</p>
    </div>

    <div 
        :class="[
            'sticky top-0 z-40 w-full transition-all duration-300',
            isScrolled 
                ? 'bg-[#326465]/95 backdrop-blur-md border-b border-white/10 py-4 shadow-xl' 
                : 'bg-transparent pt-4 pb-6 md:pb-8',
            noMargin ? '' : 'mb-6'
        ]"
    >
        <div class="w-full px-6 lg:px-14 max-w-screen-2xl mx-auto flex flex-col md:flex-row gap-4 items-center">
            <div v-if="$slots.left" class="shrink-0">
                <slot name="left"></slot>
            </div>
            <div class="flex-1 w-full">
                <slot name="search"></slot>
            </div>
            <div v-if="$slots.actions" class="shrink-0 flex items-center gap-3">
                <slot name="actions"></slot>
            </div>
        </div>
    </div>
</template>

<style scoped>
.sticky {
    will-change: padding, background-color;
}
</style>
