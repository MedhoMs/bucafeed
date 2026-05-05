<script setup>
import NavBar from '@/components/NavBar/NavBar.vue';
import VideoCall from '@/components/VideoCall.vue';
</script>

<template>
    <div class="min-h-screen">
        <NavBar />
        <main class="lg:pl-75 pt-16 lg:pt-0 video-call-view p-4 flex flex-col min-h-screen">
            <div class="mb-4 text-center text-white">
                <h1 class="text-2xl lg:text-4xl font-bold">Video Call Room</h1>
                <p class="text-gray-400">Connect with peers using WebRTC.</p>
            </div>
            <VideoCall />
        </main>
    </div>
</template>