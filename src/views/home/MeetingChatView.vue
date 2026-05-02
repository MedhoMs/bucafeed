<script setup>
    import { useRoute, useRouter } from 'vue-router';
    import NavBar from '../../components/NavBar/NavBar.vue';
    import MeetingChatBar from '../../components/MeetingChatBar.vue';
    import ChatMembers from '../../components/ChatMembers.vue';
    import { useTranslations } from '../../composables/useTranslations'
    import { user as authUser } from '../../stores/auth';
    
    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
    const route = useRoute();
    const router = useRouter();

    const meetingId = route.params.id;
    const meetingGroup = route.params.group;

    if (authUser.value && authUser.value.role === 'Student' && meetingGroup && authUser.value.institution_name !== meetingGroup) {
        router.push({ name: 'meeting' });
    }

    const meetingName = route.params.name ? (route.params.group ? `${route.params.name} - ${route.params.group}` : route.params.name) : 'Chat de Reunión';
</script>

<template>
    <NavBar></NavBar>
    <main class="flex h-screen w-full overflow-hidden lg:pl-75">

        <div class="text-white lg:w-375 ml-auto lg:mr-4 flex flex-col flex-1 pt-5 px-5 lg:px-0 lg:pl-10 pb-6 overflow-hidden">
            <p class="text-2xl lg:text-4xl text-center font-bold shrink-0 mb-4">
                {{ meetingId ? `${meetingName}` : 'Chat de Reunión' }}
            </p>
            <MeetingChatBar class="flex-1 overflow-hidden" />
        </div>

        <ChatMembers />
    </main>
</template>