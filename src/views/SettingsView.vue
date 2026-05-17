<script setup>
import { ref, computed, onMounted } from 'vue'
import NavBar from '@/components/NavBar/NavBar.vue'
import { useTranslations } from '@/composables/useTranslations'
import { useAnalytics } from '@/composables/useAnalytics'
import { user, token } from '@/stores/auth'
import BaseModal from '@/components/modals/BaseModal.vue'

import { SETTINGS_SECTIONS } from '@/constants/settings'

const { t, locale, setLocale } = useTranslations()
const { isTrackingEnabled, toggleTracking } = useAnalytics()

const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const activeSection = ref('account')
const tutorDni = ref('')
const tutorsList = ref([])
const searching = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const showDeleteModal = ref(false)
const tutorToDelete = ref(null)

const filteredSections = computed(() => {
    return SETTINGS_SECTIONS.filter(section => {
        if (section.id === 'tutors') {
            return user.value && ['Student', 'student', 'Alumno', 'alumno'].includes(user.value.role)
        }
        return true
    })
})

const fetchTutors = async () => {
    if (!user.value || !token.value) return
    try {
        const res = await fetch(`${API_BASE}/users/${user.value.id}/tutors`, {
            headers: {
                'Authorization': `Bearer ${token.value}`,
                'Accept': 'application/json'
            }
        })
        if (res.ok) {
            tutorsList.value = await res.json()
        }
    } catch (e) {
        console.error('Error fetching tutors:', e)
    }
}

const addTutor = async () => {
    if (!tutorDni.value) return
    searching.value = true
    errorMessage.value = ''
    successMessage.value = ''
    
    try {
        const findRes = await fetch(`${API_BASE}/users/find-tutor?dni=${tutorDni.value}`, {
            headers: {
                'Authorization': `Bearer ${token.value}`,
                'Accept': 'application/json'
            }
        })
        
        if (!findRes.ok) {
            errorMessage.value = t.value.settings.tutors.search_error
            searching.value = false
            return
        }
        
        const tutor = await findRes.json()
        
        const addRes = await fetch(`${API_BASE}/users/tutors`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token.value}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ tutor_id: tutor.id })
        })
        
        if (addRes.ok) {
            successMessage.value = t.value.settings.tutors.add_success
            tutorDni.value = ''
            await fetchTutors()
        } else {
            const data = await addRes.json()
            errorMessage.value = data.message || 'Error al añadir tutor'
        }
    } catch (e) {
        errorMessage.value = 'Error de conexión'
    } finally {
        searching.value = false
    }
}

const openDeleteModal = (tutorId) => {
    tutorToDelete.value = tutorId
    showDeleteModal.value = true
}

const confirmRemoveTutor = async () => {
    if (!tutorToDelete.value) return
    
    try {
        const res = await fetch(`${API_BASE}/users/tutors/${tutorToDelete.value}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token.value}`,
                'Accept': 'application/json'
            }
        })
        
        if (res.ok) {
            await fetchTutors()
        }
    } catch (e) {
        console.error('Error removing tutor:', e)
    } finally {
        showDeleteModal.value = false
        tutorToDelete.value = null
    }
}

onMounted(() => {
    if (user.value && ['Student', 'student', 'Alumno', 'alumno'].includes(user.value.role)) {
        fetchTutors()
    }
})
</script>

<template>
    <div class="min-h-screen">
        <NavBar />
        <main class="lg:pl-75 pt-20 lg:pt-0 flex flex-col min-h-screen text-white">
            <header class="px-6 lg:px-14 pt-8 mb-6">
                <h1 class="text-4xl font-bold tracking-tight text-white">{{ t.settings.title }}</h1>
                <p class="text-white/60 mt-1 text-lg">{{ t.settings.subtitle }}</p>
            </header>
    
            <section class="text-white w-full max-w-screen-2xl mx-auto px-6 lg:px-14 flex-1 flex flex-col pt-6 pb-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
                    <!-- Left Column (Navigation) -->
                    <nav class="lg:col-span-4 space-y-2">
                        <button 
                            v-for="section in filteredSections" 
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
                                activeSection === section.id ? 'bg-accent-normal text-white shadow-lg' : 'bg-white/5 text-white/40 group-hover:text-white'
                            ]">
                                <span v-html="section.icon" class="w-5 h-5 flex items-center justify-center"></span>
                            </div>
                            <div>
                                <p :class="['font-bold text-sm transition-colors', activeSection === section.id ? 'text-white' : 'text-white/60 group-hover:text-white']">
                                    {{ t.settings.sections[section.id]?.label || section.label }}
                                </p>
                                <p :class="['text-xs uppercase tracking-widest mt-0.5 transition-colors', activeSection === section.id ? 'text-white/40' : 'text-white/10 group-hover:text-white/30']">
                                    {{ t.settings.sections[section.id]?.desc || section.desc }}
                                </p>
                            </div>
                        </button>
                    </nav>
    
                    <!-- Content Area -->
                    <div class="lg:col-span-8">
                        <div class="glass-card bg-accent-normal/50 backdrop-blur-md rounded-3xl p-8 lg:p-12 shadow-2xl border border-white/5">
                            
                            <!-- Account Section -->
                            <section v-if="activeSection === 'account'" class="space-y-8">
                                <div class="flex items-center gap-4 mb-10">
                                    <h2 class="text-3xl font-black uppercase tracking-tighter">{{ t.settings.account.title }}</h2>
                                    <div class="h-px flex-1 bg-brand-net/20"></div>
                                </div>
                                <div class="bg-black/20 rounded-2xl p-6 border border-white/5 space-y-4">
                                    <div class="flex justify-between items-center border-b border-white/5 pb-4">
                                        <span class="text-white/40 text-sm font-bold uppercase tracking-wider">{{ t.settings.account.email_label }}</span>
                                        <span class="text-white font-medium">{{ user?.email }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-white/40 text-sm font-bold uppercase tracking-wider">{{ t.settings.account.status_label }}</span>
                                        <div class="flex items-center gap-2 text-emerald-400">
                                            <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                                            <span class="text-sm font-bold uppercase tracking-widest">{{ t.settings.account.status_active }}</span>
                                        </div>
                                    </div>
                                </div>
                            </section>
    
                            <!-- Tutors Section -->
                            <section v-if="activeSection === 'tutors'" class="space-y-8">
                                <div class="flex items-center gap-4 mb-10">
                                    <h2 class="text-3xl font-black uppercase tracking-tighter">{{ t.settings.tutors.title }}</h2>
                                    <div class="h-px flex-1 bg-brand-net/20"></div>
                                </div>

                                <!-- Add Tutor Form -->
                                <div class="bg-black/20 rounded-2xl p-6 border border-white/5 space-y-4">
                                    <div class="flex gap-4">
                                        <input 
                                            v-model="tutorDni"
                                            type="text" 
                                            :placeholder="t.settings.tutors.dni_placeholder"
                                            class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-net transition-all"
                                            @keyup.enter="addTutor"
                                            maxlength="9"
                                        >
                                        <button 
                                            @click="addTutor"
                                            :disabled="searching || !tutorDni"
                                            class="bg-brand-net hover:bg-brand-net/80 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold px-6 py-3 rounded-xl transition-all flex items-center gap-2 cursor-pointer"
                                        >
                                            <span v-if="searching" class="material-symbols-outlined animate-spin">sync</span>
                                            <span v-else class="material-symbols-outlined">person_add</span>
                                            {{ t.settings.tutors.add_button }}
                                        </button>
                                    </div>
                                    <p v-if="errorMessage" class="text-red-400 text-sm flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">error</span>
                                        {{ errorMessage }}
                                    </p>
                                    <p v-if="successMessage" class="text-emerald-400 text-sm flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">check_circle</span>
                                        {{ successMessage }}
                                    </p>
                                </div>

                                <!-- Tutors List -->
                                <div class="space-y-4">
                                    <div v-if="tutorsList.length === 0" class="text-center py-12 bg-white/5 rounded-2xl border border-dashed border-white/10">
                                        <span class="material-symbols-outlined text-4xl text-white/10 mb-2">family_restroom</span>
                                        <p class="text-white/40">{{ t.settings.tutors.no_tutors }}</p>
                                    </div>
                                    <div 
                                        v-for="tutor in tutorsList" 
                                        :key="tutor.id"
                                        class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10 group hover:border-brand-net/30 transition-all"
                                    >
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-full bg-brand-net/10 flex items-center justify-center overflow-hidden border border-brand-net/20">
                                                <img v-if="tutor.profile_picture" :src="tutor.profile_picture" class="w-full h-full object-cover">
                                                <span v-else class="material-symbols-outlined text-brand-net">person</span>
                                            </div>
                                            <div>
                                                <p class="font-bold text-white">{{ tutor.name }} {{ tutor.last_name }}</p>
                                                <p class="text-xs text-white/40 tracking-wider uppercase font-mono">{{ tutor.dni }}</p>
                                            </div>
                                        </div>
                                        <button 
                                            @click="openDeleteModal(tutor.id)"
                                            class="w-10 h-10 rounded-xl bg-white/5 hover:bg-red-500/20 text-white/40 hover:text-red-400 flex items-center justify-center transition-all cursor-pointer"
                                        >
                                            <span class="material-symbols-outlined text-xl">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </section>


    
                            <!-- Language Section -->
                            <section v-if="activeSection === 'language'" class="space-y-8">
                                <div class="flex items-center gap-4 mb-10">
                                    <h2 class="text-3xl font-black uppercase tracking-tighter">{{ t.settings.language.title }}</h2>
                                    <div class="h-px flex-1 bg-brand-net/20"></div>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <button 
                                        @click="setLocale('es')"
                                        :class="[
                                            'flex items-center justify-between p-6 rounded-2xl border transition-all cursor-pointer',
                                            locale === 'es' ? 'bg-brand-net/10 border-brand-net text-white shadow-xl' : 'bg-white/5 border-white/10 text-white/40 hover:bg-white/10'
                                        ]"
                                    >
                                        <div class="flex items-center gap-4">
                                            <span class="text-2xl">🇪🇸</span>
                                            <span class="font-bold">{{ t.settings.language.spanish }}</span>
                                        </div>
                                        <span v-if="locale === 'es'" class="material-symbols-outlined text-brand-net">check_circle</span>
                                    </button>
    
                                    <button 
                                        @click="setLocale('en')"
                                        :class="[
                                            'flex items-center justify-between p-6 rounded-2xl border transition-all cursor-pointer',
                                            locale === 'en' ? 'bg-brand-net/10 border-brand-net text-white shadow-xl' : 'bg-white/5 border-white/10 text-white/40 hover:bg-white/10'
                                        ]"
                                    >
                                        <div class="flex items-center gap-4">
                                            <span class="text-2xl">🇺🇸</span>
                                            <span class="font-bold">{{ t.settings.language.english }}</span>
                                        </div>
                                        <span v-if="locale === 'en'" class="material-symbols-outlined text-brand-net">check_circle</span>
                                    </button>
                                </div>
                            </section>
    
                            <!-- Cookies Section -->
                            <section v-if="activeSection === 'cookies'" class="space-y-8">
                                <div class="flex items-center gap-4 mb-10">
                                    <h2 class="text-3xl font-black uppercase tracking-tighter">{{ t.settings.cookies.title }}</h2>
                                    <div class="h-px flex-1 bg-brand-net/20"></div>
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
                                                isTrackingEnabled ? 'bg-brand-net' : 'bg-white/20'
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
                                    <button class="bg-brand-net hover:bg-brand-net/80 text-white text-xs font-black uppercase tracking-widest px-8 py-3.5 rounded-3xl transition-all cursor-pointer">
                                        {{ t.settings.cookies.accept_all }}
                                    </button>
                                    <button class="bg-white/5 hover:bg-white/10 text-white/60 text-xs font-black uppercase tracking-widest px-8 py-3.5 rounded-3xl transition-all border border-white/10 cursor-pointer">
                                        {{ t.settings.cookies.reject_optional }}
                                    </button>
                                </div>
                            </section>
    

    
                        </div>
                    </div>
                </div>

                <!-- Botón Volver al final (Abajo de toda la vista, alineado a la izquierda en desktop) -->
                <div class="mt-auto pt-12 flex justify-center lg:justify-start">
                    <RouterLink to="/profile" class="flex justify-center items-center gap-2 bg-accent-normal hover:bg-accent-normal-hover text-white px-8 py-3.5 cursor-pointer rounded-2xl duration-300 shadow-lg font-black uppercase tracking-widest text-xs w-fit">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="m274-450 248 248-42 42-320-320 320-320 42 42-248 248h526v60H274Z"/></svg>
                        {{ t.nav.back }}
                    </RouterLink>
                </div>
            </section>
        </main>

        <!-- Delete Tutor Confirmation Modal -->
        <BaseModal
            v-if="showDeleteModal"
            :title="t.settings.tutors.remove_modal.title"
            :confirmText="t.settings.tutors.remove_modal.confirm"
            :cancelText="t.settings.tutors.remove_modal.cancel"
            @close="showDeleteModal = false"
            @confirm="confirmRemoveTutor"
        >
            <p class="text-white/60 text-sm leading-relaxed">
                {{ t.settings.tutors.remove_modal.description }}
            </p>
        </BaseModal>
    </div>
</template>

<style scoped>
/* Estilos simplificados sin animaciones de transición complejas */
.bg-gradient-to-br {
    background-attachment: fixed;
}
</style>
