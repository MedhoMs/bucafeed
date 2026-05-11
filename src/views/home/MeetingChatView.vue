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
if (authUser.value && authUser.value.role === 'Student' && meetingGroup && meetingGroup !== 'Varios' && authUser.value.institution_name !== meetingGroup) {
    router.push({ name: 'meeting' });
}

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

onMounted(fetchMeeting);

const meetingName = computed(() => {
    if (route.params.name) {
        return route.params.group ? `${route.params.name} - ${route.params.group}` : route.params.name;
    }
    return meeting.value?.name || t.value.meetings.chatTitle;
});
</script>

<template>
    <div class="h-screen overflow-hidden">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-16 lg:pt-0 flex w-full h-full overflow-hidden">
    
            <div
                class="text-white flex flex-col flex-1 pt-5 px-5 lg:px-0 lg:pl-10 pb-6 overflow-hidden">
                <p class="text-2xl lg:text-4xl text-center font-bold shrink-0 mb-4 px-16 lg:px-0">
                    {{ meetingId ? `${meetingName}` : t.value.meetings.chatTitle }}
                </p>
                <MeetingChatBar :meeting="meeting" class="flex-1 overflow-hidden" />
            </div>
    
            <ChatMembers :meeting="meeting" />
        </main>
    </div>
</template>