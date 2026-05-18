<script setup>
import { useRoute, useRouter } from 'vue-router';
import NavBar from '../../components/NavBar/NavBar.vue';
import MeetingChatBar from '../../components/MeetingChatBar.vue';
import ChatMembers from '../../components/ChatMembers.vue';
import VideoCall from '../../components/VideoCall.vue';
import { useTranslations } from '../../composables/useTranslations'
import { user as authUser } from '../../stores/auth';
import { ref, onMounted, computed } from 'vue';
import { useApi } from '../../composables/useApi';
import { useSocket } from '../../composables/useSocket';
const { t } = useTranslations()
const { get, post: apiPost } = useApi();
const { emit: emitSocket } = useSocket();
const route = useRoute();
const router = useRouter();

const meetingId = route.params.id;
const meetingGroup = route.params.group;
const meeting = ref(null);
const activeCallRoomId = ref(null);
const chatBarRef = ref(null);

onMounted(() => {
    fetchMeeting();
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
    return meeting.value?.name || t.value.meetings.chatTitle;
});

const canStartCall = computed(() => {
    if (!authUser.value) return false;
    const role = authUser.value.role?.toLowerCase();
    return ['teacher', 'admin', 'ei', 'profesor', 'administrador'].includes(role);
});

async function startMeetingCall() {
    const roomId = `meeting-${meetingId}`;
    activeCallRoomId.value = roomId;
    
    // Notification for other members could be a special message
    try {
        const savedMsg = await apiPost(`meetings/${meetingId}/messages`, {
            type: 'call',
            content: roomId,
            file_name: 'Sesión de video iniciada'
        });

        if (savedMsg) {
            const currentRoomId = `meeting-${meetingId}`;
            const normalizedMsg = { ...savedMsg, type: savedMsg.message_type || savedMsg.type };
            emitSocket('chat:message', currentRoomId, normalizedMsg);
            chatBarRef.value?.loadMessages();
        }
    } catch (e) {
        console.error('Error starting meeting call:', e);
    }
}

function handleCallEnd() {
    // Si es profesor/admin, finalizar la llamada para todos
    if (canStartCall.value && activeCallRoomId.value) {
        const meetingRoom = `meeting-${meetingId}`;
        emitSocket('call:ended', meetingRoom, { roomId: activeCallRoomId.value });
        // Marcar los mensajes locales del profesor también
        chatBarRef.value?.markCallsEnded();
    }
    activeCallRoomId.value = null;
}
</script>

<template>
    <div class="h-screen overflow-hidden">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-16 lg:pt-0 flex w-full h-full overflow-hidden">
    
            <div
                class="text-white flex flex-col flex-1 pt-5 px-5 lg:px-10 pb-6 overflow-hidden relative">
                
                <div class="flex items-center justify-between mb-4 gap-4 w-full shrink-0">

                    <div class="flex-1 flex justify-start">
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
                    </div>

                    
                    <p class="text-xl lg:text-4xl text-center font-bold truncate px-2 tracking-normal flex-shrink min-w-0 max-w-[60%]">
                        {{ meetingId ? `${meetingName}` : 'Chat de Reunión' }}
                    </p>

                    
                    <div class="flex-1 flex justify-end">
                        <button v-if="canStartCall" @click="startMeetingCall" 
                                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white/70 hover:text-white hover:bg-white/10 transition-all text-[10px] font-black uppercase tracking-widest shadow-lg shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 10l4.553 -2.276a1 1 0 0 1 1.447 .894v6.764a1 1 0 0 1 -1.447 .894l-4.553 -2.276v-4z"/><path d="M3 6m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z"/></svg>
                            <span class="hidden sm:inline">Iniciar Llamada</span>
                        </button>
                    </div>
                </div>

                <!-- Videollamada Embebida -->
                <div v-if="activeCallRoomId" class="bg-black/40 border-b border-white/10 relative overflow-hidden transition-all duration-500 max-h-[85vh] min-h-[400px] rounded-2xl mb-4 shrink-0 shadow-2xl">
                    <div class="flex items-center justify-between px-6 py-2 bg-black/60 backdrop-blur-sm z-30 relative">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-white/80">Sesión de video grupal activa</span>
                        </div>
                    </div>
                    <VideoCall :room-id="activeCallRoomId" @close="handleCallEnd" />
                </div>

                <MeetingChatBar ref="chatBarRef" :meeting="meeting" :active-call-id="activeCallRoomId" @joinCall="activeCallRoomId = $event" @forceLeaveCall="activeCallRoomId = null" class="flex-1 overflow-hidden" />
            </div>
    
            <ChatMembers :meeting="meeting" />
        </main>
    </div>
</template>