<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import MeetingChat from '../../components/MeetingChat.vue';
    import ChatMembers from '../../components/ChatMembers.vue';
    
    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
</script>

<template>
    <div class="flex flex-row h-screen overflow-hidden">
        <NavBar />
        <main class="flex-1 flex flex-row overflow-hidden">
            <div class="text-white lg:w-[1500px] mx-auto lg:mr-14 flex flex-col flex-1 pt-8 pl-10 pr-10 pb-6 overflow-hidden">
                <p class="text-[40px] font-bold shrink-0 mb-4">Nombre del meeting</p>
                <MeetingChat class="flex-1 overflow-hidden" />
            </div>
            <ChatMembers />
        </main>
    </div>
</template>