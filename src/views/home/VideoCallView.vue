<script setup>
import NavBar from '@/components/NavBar/NavBar.vue';
import VideoCall from '@/components/VideoCall.vue';
</script>

<template>
    <div class="flex flex-row lg:pl-75">
        <NavBar />
        <div class="video-call-view p-4 flex-1">
        <div class="mb-4 text-center text-white">
            <h1 class="text-2xl lg:text-4xl font-bold">Video Call Room</h1>
            <p class="text-gray-400">Connect with peers using WebRTC.</p>
        </div>
        <VideoCall />
        </div>
    </div>
</template>