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

    // Solo estudiantes del mismo centro pueden entrar
    if (authUser.value && authUser.value.role === 'Student' && meetingGroup && authUser.value.institution_name !== meetingGroup) {
        // Redirigir si intento forzar la URL
        router.push({ name: 'meeting' });
    }

    const meetingName = route.params.name ? (route.params.group ? `${route.params.name} - ${route.params.group}` : route.params.name) : 'Chat de Reunión';
</script>

<template>
    <NavBar></NavBar>
    <main class="flex h-screen w-full overflow-hidden lg:pl-75">

        <div class="text-white lg:w-375 ml-auto lg:mr-4 flex flex-col flex-1 pt-5 pl-10 pb-6 overflow-hidden">
            <p class="text-2xl lg:text-4xl pl-5 lg:pl-0 w-50 lg:w-auto text-center font-bold shrink-0 mb-4">
                {{ meetingId ? `${meetingName}` : 'Chat de Reunión' }}
            </p>
            <MeetingChatBar class="flex-1 overflow-hidden" />
        </div>

        <div class="hover:bg-[#152027] self-end mb-13 p-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white cursor-pointer text-2xl icon icon-tabler icons-tabler-outline icon-tabler-brand-kako-talk"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M10 8v7" /><path d="M14 10l-2 2.5l2 2.5" /><path d="M12 4c4.97 0 9 3.358 9 7.5c0 4.142 -4.03 7.5 -9 7.5c-.67 0 -1.323 -.061 -1.95 -.177l-3.05 2.177l.592 -2.962c-2.741 -1.284 -4.592 -3.73 -4.592 -6.538c0 -4.142 4.03 -7.5 9 -7.5" /></svg>
        </div>

        <ChatMembers />
    </main>
</template>