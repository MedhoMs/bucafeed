<script setup>
import { computed } from 'vue';
import defaultAvatar from '@/assets/logo/logoTelamon.png';

const props = defineProps({
    user: {
        type: Object,
        required: false,
        default: () => ({})
    },
    size: {
        type: String,
        default: 'w-10 h-10'
    },
    className: {
        type: String,
        default: ''
    }
});

const avatarUrl = computed(() => {
    if (props.user?.profile_picture) {
        const baseSrc = import.meta.env.VITE_API_URL 
            ? import.meta.env.VITE_API_URL.replace(/\/api$/, '') 
            : 'http://localhost:8000';
        
        const path = props.user.profile_picture;
        if (path.startsWith('http')) return path;
        return baseSrc + (path.startsWith('/') ? '' : '/') + path;
    }
    return defaultAvatar;
});
</script>

<template>
    <img 
        v-if="user?.role === 'Student' && user?.is_verified === false"
        :src="avatarUrl" 
        :class="[size, 'rounded-full border-2 border-amber-300 object-cover shrink-0', className]" 
        :alt="user?.name || 'User'"
    >
    <img 
        v-else
        :src="avatarUrl" 
        :class="[size, 'rounded-full border-2 border-white/20 object-cover shrink-0', className]" 
        :alt="user?.name || 'User'"
    >
</template>
