<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    slides: {
        type: Array,
        required: true
    },
    className: {
        type: String,
        default: ''
    }
});

const current = ref(0);
const direction = ref(1);
const containerRef = ref(null);

const handleScroll = () => {
    if (!containerRef.value) return;
    
    const containerTop = containerRef.value.offsetTop;
    const scrollY = window.scrollY;
    
    // progress within the container
    const scrollableDistance = containerRef.value.offsetHeight - window.innerHeight;
    const progress = Math.max(0, Math.min(1, (scrollY - containerTop) / scrollableDistance));
    
    const index = Math.min(Math.floor(progress * props.slides.length), props.slides.length - 1);
    
    if (index !== current.value && index >= 0) {
        direction.value = index > current.value ? 1 : -1;
        current.value = index;
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const scrollToSlide = (i) => {
    if (!containerRef.value) return;
    const containerTop = containerRef.value.offsetTop;
    const scrollableDistance = containerRef.value.offsetHeight - window.innerHeight;
    const targetScroll = containerTop + (i / props.slides.length) * scrollableDistance;
    
    window.scrollTo({ top: targetScroll, behavior: 'smooth' });
};

const transitionName = computed(() => {
    return direction.value > 0 ? 'slide-up' : 'slide-down';
});
</script>

<template>
    <div ref="containerRef" :style="{ height: `${slides.length * 100}vh` }">
        <div :class="['sticky top-0 overflow-hidden h-screen', className]">
            <slot name="background" :slide="slides[current]"></slot>
            <slot></slot>

            <Transition :name="transitionName" mode="out-in">
                <div :key="slides[current].id" class="absolute inset-0 flex items-center pl-10 xl:pl-0">
                    <div class="flex-1 w-full flex items-center justify-center p-10 max-w-5xl mx-auto">
                        <h1 class="text-4xl md:text-6xl font-black text-white text-center leading-tight drop-shadow-2xl">
                            {{ slides[current].content }}
                        </h1>
                    </div>
                    <div class="hidden xl:flex shrink-0 items-center justify-center w-[40%] pr-20">
                        <slot name="image" :slide="slides[current]"></slot>
                    </div>
                </div>
            </Transition>

            <div v-if="$slots.sideElement" class="absolute inset-0 flex items-center pointer-events-none">
                <div class="flex-1" />
                <div class="hidden xl:flex shrink-0 items-center justify-center w-[40%] pr-20 pointer-events-auto">
                    <slot name="sideElement"></slot>
                </div>
            </div>

            <nav class="absolute left-6 top-1/2 -translate-y-1/2 flex flex-col items-center z-40">
                <div v-for="(slide, i) in slides" :key="'dot-'+slide.id" class="flex flex-col items-center">
                    <button
                        @click="scrollToSlide(i)"
                        class="rounded-full cursor-pointer border-0 transition-all duration-300 shadow-md"
                        :class="i === current ? 'w-[11px] h-[11px] bg-[#179cf0]' : 'w-[9px] h-[9px] bg-white/40 hover:bg-white/60'"
                        :title="'Ir al slide ' + (i + 1)"
                    ></button>
                    <div v-if="i < slides.length - 1" class="w-[1.5px] h-[30px] bg-white/20 my-1"></div>
                </div>
            </nav>
        </div>
    </div>
</template>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active,
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-up-enter-from {
    opacity: 0;
    transform: translateY(28px);
}
.slide-up-leave-to {
    opacity: 0;
    transform: translateY(-28px);
}

.slide-down-enter-from {
    opacity: 0;
    transform: translateY(-28px);
}
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(28px);
}
</style>
