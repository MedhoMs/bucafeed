<script setup>
import { ref } from 'vue'
import NavBar from '@/components/NavBar/NavBar.vue'
import { useTranslations } from '@/composables/useTranslations'
import { useUmami } from '@/composables/useUmami'

import { SETTINGS_SECTIONS } from '@/constants/settings'

const { t, locale, setLocale } = useTranslations()
const { isTrackingEnabled, toggleTracking } = useUmami()

const activeSection = ref('account')
</script>

<template>
    <div class="min-h-screen">
        <NavBar />
        <main class="lg:pl-75 pt-16 lg:pt-0 min-h-screen text-white">
            <div class="max-w-7xl mx-auto px-6 py-12">
                <header class="mb-12">
                    <h1 class="text-4xl font-bold tracking-tight text-white">{{ t.settings.title }}</h1>
                    <p class="text-white/60 mt-1 text-lg">{{ t.settings.subtitle }}</p>
                </header>
    
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
                    <!-- Navigation -->
                    <nav class="lg:col-span-4 space-y-2">
                        <button 
                            v-for="section in SETTINGS_SECTIONS" 
                            :key="section.id"
                            @click="activeSection = section.id"
                            :class="[
                                'w-full flex items-center gap-4 p-4 rounded-xl transition-all duration-300 text-left group cursor-pointer',
                                activeSection === section.id 
                                    ? 'bg-surface/50 border border-white/10 shadow-lg' 
                                    : 'bg-transparent border border-transparent hover:bg-white/5'
                            ]"
                        >
                            <div :class="[
                                'w-10 h-10 rounded-lg flex items-center justify-center transition-colors',
                                activeSection === section.id ? 'bg-[#406071] text-white' : 'bg-white/5 text-white/40 group-hover:text-white'
                            ]">
                                <span class="material-symbols-outlined">{{ section.icon }}</span>
                            </div>
                            <div>
                                <p :class="['font-bold text-sm transition-colors', activeSection === section.id ? 'text-white' : 'text-white/60 group-hover:text-white']">
                                    {{ t.settings.sections[section.id]?.label || section.label }}
                                </p>
                                <p :class="['text-xs uppercase tracking-widest mt-0.5 transition-colors', activeSection === section.id ? 'text-white/50' : 'text-white/20 group-hover:text-white/40']">
                                    {{ t.settings.sections[section.id]?.desc || section.desc }}
                                </p>
                            </div>
                        </button>
                    </nav>
    
                    <!-- Content Area -->
                    <div class="lg:col-span-8">
                        <div class="glass-card rounded-2xl p-8 lg:p-10 shadow-2xl">
                            
                            <!-- Account Section -->
                            <section v-if="activeSection === 'account'" class="space-y-8">
                                <div class="flex items-center gap-4 mb-8">
                                    <h2 class="text-2xl font-bold">{{ t.settings.account.title }}</h2>
                                    <div class="h-px flex-1 bg-white/10"></div>
                                </div>
                            </section>
    
                            <!-- Privacy Section -->
                            <section v-if="activeSection === 'privacy'" class="space-y-8">
                                <div class="flex items-center gap-4 mb-8">
                                    <h2 class="text-2xl font-bold">{{ t.settings.privacy.title }}</h2>
                                    <div class="h-px flex-1 bg-white/10"></div>
                                </div>
                                <p class="text-white/40 italic text-sm">{{ t.settings.privacy.coming_soon }}</p>
                            </section>
    
                            <!-- Language Section -->
                            <section v-if="activeSection === 'language'" class="space-y-8">
                                <div class="flex items-center gap-4 mb-8">
                                    <h2 class="text-2xl font-bold">{{ t.settings.language.title }}</h2>
                                    <div class="h-px flex-1 bg-white/10"></div>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <button 
                                        @click="setLocale('es')"
                                        :class="[
                                            'flex items-center justify-between p-6 rounded-2xl border transition-all cursor-pointer',
                                            locale === 'es' ? 'bg-accent/10 border-accent text-white' : 'bg-white/5 border-white/10 text-white/40 hover:bg-white/10'
                                        ]"
                                    >
                                        <div class="flex items-center gap-4">
                                            <span class="text-2xl">🇪🇸</span>
                                            <span class="font-bold">{{ t.settings.language.spanish }}</span>
                                        </div>
                                        <span v-if="locale === 'es'" class="material-symbols-outlined text-accent">check_circle</span>
                                    </button>
    
                                    <button 
                                        @click="setLocale('en')"
                                        :class="[
                                            'flex items-center justify-between p-6 rounded-2xl border transition-all cursor-pointer',
                                            locale === 'en' ? 'bg-accent/10 border-accent text-white' : 'bg-white/5 border-white/10 text-white/40 hover:bg-white/10'
                                        ]"
                                    >
                                        <div class="flex items-center gap-4">
                                            <span class="text-2xl">🇺🇸</span>
                                            <span class="font-bold">{{ t.settings.language.english }}</span>
                                        </div>
                                        <span v-if="locale === 'en'" class="material-symbols-outlined text-accent">check_circle</span>
                                    </button>
                                </div>
                            </section>
    
                            <!-- Cookies Section -->
                            <section v-if="activeSection === 'cookies'" class="space-y-8">
                                <div class="flex items-center gap-4 mb-8">
                                    <h2 class="text-2xl font-bold">{{ t.settings.cookies.title }}</h2>
                                    <div class="h-px flex-1 bg-white/10"></div>
                                </div>
                                
                                <p class="text-white/60 leading-relaxed text-sm">
                                    {{ t.settings.cookies.desc }}
                                </p>
    
                                <div class="bg-black/20 rounded-2xl p-6 border border-white/5">
                                    <div class="flex items-center justify-between gap-6">
                                        <div>
                                            <p class="font-bold text-base">{{ t.settings.cookies.tracking_label }}</p>
                                            <p class="text-xs text-white/40 mt-1">
                                                {{ isTrackingEnabled ? t.settings.cookies.tracking_on : t.settings.cookies.tracking_off }}
                                            </p>
                                        </div>
                                        <button 
                                            @click="toggleTracking"
                                            :class="[
                                                'w-14 h-7 rounded-full transition-all relative shrink-0 cursor-pointer',
                                                isTrackingEnabled ? 'bg-emerald-500' : 'bg-white/20'
                                            ]"
                                        >
                                            <div :class="[
                                                'absolute top-1 w-5 h-5 bg-white rounded-full transition-all shadow-lg',
                                                isTrackingEnabled ? 'left-8' : 'left-1'
                                            ]"></div>
                                        </button>
                                    </div>
                                </div>
    
                                <div class="flex flex-wrap gap-4 pt-4">
                                    <button class="bg-accent hover:bg-accent/80 text-white text-xs font-black uppercase tracking-widest px-8 py-3.5 rounded-3xl transition-all cursor-pointer">
                                        {{ t.settings.cookies.accept_all }}
                                    </button>
                                    <button class="bg-white/5 hover:bg-white/10 text-white/60 text-xs font-black uppercase tracking-widest px-8 py-3.5 rounded-3xl transition-all border border-white/10 cursor-pointer">
                                        {{ t.settings.cookies.reject_optional }}
                                    </button>
                                </div>
                            </section>
    
                            <!-- Notifications Section -->
                            <section v-if="activeSection === 'notifications'" class="space-y-8">
                                <div class="flex items-center gap-4 mb-8">
                                    <h2 class="text-2xl font-bold">{{ t.settings.notifications.title }}</h2>
                                    <div class="h-px flex-1 bg-white/10"></div>
                                </div>
                                <p class="text-white/40 italic text-sm">{{ t.settings.notifications.coming_soon }}</p>
                            </section>
    
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Estilos simplificados sin animaciones de transición complejas */
.bg-gradient-to-br {
    background-attachment: fixed;
}
</style>
