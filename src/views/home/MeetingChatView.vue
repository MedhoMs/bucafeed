<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import MeetingChat from '../../components/MeetingChat.vue';
    import ChatMembers from '../../components/ChatMembers.vue';
    
    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
</script>

<template>
    <NavBar></NavBar>
    <main class="flex h-screen w-full overflow-hidden lg:pl-75">

        <div class="text-white lg:w-375 mx-auto lg:mr-14 flex flex-col flex-1 pt-5 pl-10 pr-10 pb-6 overflow-hidden">
            <p class="text-2xl lg:text-4xl pl-5 lg:pl-0 w-50 lg:w-auto text-center font-bold shrink-0 mb-4">Nombre del meeting</p>
            <MeetingChat class="flex-1 overflow-hidden" />
        </div>

        <ChatMembers />
    </main>
</template>