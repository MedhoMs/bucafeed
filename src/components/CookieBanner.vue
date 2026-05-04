<script setup>
import { ref, onMounted } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import { toggleUmamiConsent } from '@/utils/umami';

const { t } = useTranslations();
const visible = ref(false);

onMounted(() => {
    const consent = localStorage.getItem('umami-consent');
    if (consent === null) {
        setTimeout(() => {
            visible.value = true;
        }, 1000);
    }
});

const acceptAll = () => {
    toggleUmamiConsent(true);
    visible.value = false;
};

const rejectAll = () => {
    toggleUmamiConsent(false);
    visible.value = false;
};
</script>

<template>
    <Transition name="slide-up">
        <div v-if="visible" class="fixed bottom-6 left-6 right-6 md:left-auto md:max-w-md bg-[#1c2732] border border-white/10 rounded-2xl p-6 shadow-2xl z-50 backdrop-blur-xl">
            <div class="flex items-start gap-4">
                <div class="p-3 rounded-xl bg-blue-500/10 text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/><path d="M11 17v.01"/><path d="M7 14v.01"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-white mb-2">{{ t.settings.cookies }}</h3>
                    <p class="text-sm text-white/60 mb-4 leading-relaxed">
                        {{ t.settings.cookieDescription }}
                    </p>
                    <div class="flex gap-3">
                        <button @click="acceptAll" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white py-2 rounded-xl text-sm font-semibold transition-all">
                            {{ t.settings.acceptAll }}
                        </button>
                        <button @click="rejectAll" class="flex-1 bg-white/5 hover:bg-white/10 text-white py-2 rounded-xl text-sm font-semibold transition-all border border-white/5">
                            {{ t.settings.rejectAll }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(100%) scale(0.9);
    opacity: 0;
}
</style>
