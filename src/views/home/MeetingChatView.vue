<script setup>
import { useRoute, useRouter } from 'vue-router';
import NavBar from '../../components/NavBar/NavBar.vue';
import MeetingChatBar from '../../components/MeetingChatBar.vue';
import ChatMembers from '../../components/ChatMembers.vue';
import { useTranslations } from '../../composables/useTranslations'
import { user as authUser } from '../../stores/auth';
import { ref, onMounted, computed } from 'vue';
const { t } = useTranslations()
const route = useRoute();
const router = useRouter();

const meetingId = route.params.id;
const meetingGroup = route.params.group;
const meeting = ref(null);

// Security check: only redirect if it's a specific group and doesn't match
onMounted(() => {
    fetchMeeting();
    
    if (authUser.value) {
        const role = authUser.value.role?.toLowerCase();
        const userInstitution = authUser.value.institution_name?.toLowerCase();
        const targetGroup = meetingGroup?.toLowerCase();

        if ((role === 'student' || role === 'alumno') && targetGroup && targetGroup !== 'varios' && userInstitution !== targetGroup) {
            console.warn('Access denied: Institution mismatch', { userInstitution, targetGroup });
            router.push({ name: 'meeting' });
        }
    }
});
const fetchMeeting = async () => {
    if (!meetingId) return;
    try {
        const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
        const response = await fetch(`${apiBase}/meetings/${meetingId}`);
        if (response.ok) {
            meeting.value = await response.json();
        }
    } catch (error) {
        console.error('Error fetching meeting:', error);
    }
}



const meetingName = computed(() => {
    if (route.params.name) {
        return route.params.group ? `${route.params.name} - ${route.params.group}` : route.params.name;
    }
    return meeting.value?.name || 'Chat de Reunión';
});
</script>

<template>
    <div class="h-screen overflow-hidden">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-16 lg:pt-0 flex w-full h-full overflow-hidden">
    
            <div
                class="text-white flex flex-col flex-1 pt-5 px-5 lg:px-10 pb-6 overflow-hidden relative">
                
                <!-- Header con botón y título -->
                <div class="flex items-center justify-between mb-4 gap-4 shrink-0">
                    <button @click="router.push({ name: 'meeting' })" 
                            class="flex items-center gap-2 cursor-pointer text-white/70 hover:text-white transition-colors group shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:-translate-x-1">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M5 12l14 0" />
                            <path d="M5 12l6 6" />
                            <path d="M5 12l6 -6" />
                        </svg>
                        <span class="hidden xl:inline text-sm font-medium">Volver</span>
                    </button>

                    <p class="text-xl lg:text-4xl text-center font-bold truncate px-2 flex-1">
                        {{ meetingId ? `${meetingName}` : 'Chat de Reunión' }}
                    </p>

                    <!-- Espaciador para mantener el título centrado -->
                    <div class="w-7 lg:w-16 xl:w-24 shrink-0"></div>
                </div>

                <MeetingChatBar :meeting="meeting" class="flex-1 overflow-hidden" />
            </div>
    
            <ChatMembers :meeting="meeting" />
        </main>
    </div>
</template>