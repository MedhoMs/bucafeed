<script setup>
import { useRoute } from 'vue-router';
import NavBar from '@/components/NavBar/NavBar.vue';
import VideoCall from '@/components/VideoCall.vue';

const route = useRoute();
const roomId = route.params.roomId || 'sala-general';
</script>

<template>
    <div class="min-h-screen">
        <NavBar />
        <main class="lg:pl-75 pt-16 lg:pt-0 video-call-view p-4 flex flex-col min-h-screen">
            <div class="mb-4 text-center text-white">
                <h1 class="text-xl lg:text-2xl font-bold">{{ roomId }}</h1>
            </div>
            <VideoCall :room-id="roomId" @close="$router.back()" />
        </main>
    </div>
</template>