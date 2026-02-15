<script setup>
    const props = defineProps({
        title: {
            type: String,
            required: true
        },
        to: {
            type: String,
            required: true
        },
        backend: {
            type: Boolean,
            default: false
        }
    })

    const navigate = () => {
        if (props.backend) {
            const backendBase = import.meta.env.VITE_BACKEND_URL || 'http://localhost:8001'
            const cleanBase = backendBase.replace(/\/$/, '').replace(/\/frontend$/, '')
            window.location.href = `${cleanBase}/${props.to.replace(/^\//, '')}`
        }
    }
</script>

<template>
    <router-link v-if="!backend" :to="to" class="relative flex items-center gap-[10px] mb-5 mr-4 rounded-xl text-[17px] font-[500] py-3 px-4 text-white no-underline transition-all duration-[0.2s] ease-in-out hover:bg-[#406071] hover:cursor-pointer active:bg-[#406071] active:font-bold w-full">
        <slot name="icon"></slot>
        <span class="flex-1">{{ title }}</span>
        <slot></slot>
    </router-link>
    
    <a v-else @click.prevent="navigate" href="#" class="relative flex items-center gap-[10px] mb-5 mr-4 rounded-xl text-[17px] font-[500] py-3 px-4 text-white no-underline transition-all duration-[0.2s] ease-in-out hover:bg-[#406071] hover:cursor-pointer active:bg-[#406071] active:font-bold w-full">
        <slot name="icon"></slot>
        <span class="flex-1">{{ title }}</span>
        <slot></slot>
    </a>
</template>