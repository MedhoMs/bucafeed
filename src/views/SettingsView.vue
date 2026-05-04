<script setup>
import NavBar from '@/components/NavBar/NavBar.vue';
import { useTranslations } from '@/composables/useTranslations'
import { ref } from 'vue'
import { toggleUmamiConsent } from '@/utils/umami'

const { t } = useTranslations()

const umamiEnabled = ref(localStorage.getItem('umami-consent') === 'true')

const handleToggleUmami = () => {
    umamiEnabled.value = !umamiEnabled.value
    toggleUmamiConsent(umamiEnabled.value)
}

const acceptAll = () => {
    umamiEnabled.value = true
    toggleUmamiConsent(true)
}

const rejectAll = () => {
    umamiEnabled.value = false
    toggleUmamiConsent(false)
}
</script>

<template>
    <NavBar></NavBar>
    <main class="flex min-h-screen justify-center lg:pl-75">
        <div class="text-white lg:w-375 w-full max-w-4xl mx-auto p-6 flex flex-col gap-8">
            <header class="border-b border-white/10 pb-4">
                <h1 class="text-3xl font-bold">{{ t.settings.title }}</h1>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Sidebar -->
                <aside class="flex flex-col gap-2">
                    <button class="flex items-center gap-3 p-3 rounded-lg bg-white/5 text-white font-medium transition-all hover:bg-white/10 text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        {{ t.settings.account }}
                    </button>
                    <button class="flex items-center gap-3 p-3 rounded-lg text-white/60 font-medium transition-all hover:bg-white/5 text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        {{ t.settings.privacy }}
                    </button>
                    <button class="flex items-center gap-3 p-3 rounded-lg bg-blue-500/10 text-blue-400 font-medium transition-all text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/><path d="M11 17v.01"/><path d="M7 14v.01"/></svg>
                        {{ t.settings.cookies }}
                    </button>
                </aside>

                <!-- Main Content -->
                <section class="md:col-span-2 flex flex-col gap-6">
                    <div class="bg-[#15202b80] border border-white/10 rounded-2xl p-6 backdrop-blur-md shadow-2xl">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                                <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-400"><path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/><path d="M11 17v.01"/><path d="M7 14v.01"/></svg>
                                    {{ t.settings.cookies }} & {{ t.settings.analytics }}
                                </h2>
                                <p class="text-white/60 text-sm leading-relaxed">
                                    {{ t.settings.cookieDescription }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 rounded-xl bg-white/5 border border-white/5 transition-all hover:bg-white/10">
                                <div>
                                    <p class="font-medium">{{ t.settings.umamiStatus }}</p>
                                    <p class="text-xs text-white/40">{{ umamiEnabled ? t.settings.enabled : t.settings.disabled }}</p>
                                </div>
                                <button 
                                    @click="handleToggleUmami"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                                    :class="umamiEnabled ? 'bg-blue-600' : 'bg-white/20'"
                                >
                                    <span 
                                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 ease-in-out"
                                        :class="umamiEnabled ? 'translate-x-6' : 'translate-x-1'"
                                    />
                                </button>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col sm:flex-row gap-3">
                            <button @click="acceptAll" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-xl font-semibold transition-all shadow-lg shadow-blue-600/20 active:scale-95">
                                {{ t.settings.acceptAll }}
                            </button>
                            <button @click="rejectAll" class="flex-1 bg-white/10 hover:bg-white/20 text-white py-3 rounded-xl font-semibold transition-all active:scale-95">
                                {{ t.settings.rejectAll }}
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
</template>

<style scoped>
/* Refined styles for premium feel */
main {
    background: radial-gradient(circle at top right, rgba(29, 78, 216, 0.05), transparent 400px),
                radial-gradient(circle at bottom left, rgba(30, 41, 59, 0.2), transparent 400px);
}
</style>
