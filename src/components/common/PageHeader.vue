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
    <div v-if="title || subtitle" class="w-full px-6 lg:px-14 max-w-screen-2xl mx-auto pt-12 md:pt-20 mb-4 transition-all duration-500 text-white flex flex-col md:flex-row md:items-center justify-between gap-8">
        <div class="flex-1">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-black tracking-normal">{{ title }}</h1>
            <p class="text-white/60 text-base md:text-xl font-medium mt-2 max-w-2xl">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.headerActions" class="shrink-0 flex items-center gap-4">
            <slot name="headerActions"></slot>
        </div>
    </div>

    <div 
        v-if="$slots.left || $slots.search || $slots.actions || $slots.bottom"
        :class="[
            'sticky top-0 z-40 w-full transition-all duration-300',
            isScrolled 
                ? 'bg-[#326465]/95 border-b border-white/10 shadow-xl' 
                : 'bg-transparent',
            noMargin ? '' : 'mb-8'
        ]"
    >
        <div class="w-full px-6 lg:px-14 max-w-screen-2xl mx-auto flex flex-col gap-6 py-6">
            <div class="flex flex-col md:flex-row gap-6 items-center w-full">
                <div v-if="$slots.left" class="shrink-0">
                    <slot name="left"></slot>
                </div>
                <div v-if="$slots.search" class="flex-1 w-full">
                    <slot name="search"></slot>
                </div>
                <div v-if="$slots.actions" class="shrink-0 flex items-center gap-3">
                    <slot name="actions"></slot>
                </div>
            </div>
            
            <div v-if="$slots.bottom" class="w-full flex justify-center">
                <slot name="bottom"></slot>
            </div>
        </div>
    </div>
    <div v-else-if="!noMargin" class="mb-6"></div>
</template>

<style scoped>
.sticky {
    will-change: padding, background-color;
}
</style>
