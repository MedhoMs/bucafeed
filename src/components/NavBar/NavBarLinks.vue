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
            const backendBase = import.meta.env.VITE_BACKEND_URL || 
                               'http://localhost:8000'
            
            const cleanBase = backendBase.replace(/\/$/, '')
            const targetUrl = `${cleanBase}/${props.to.replace(/^\//, '')}`
            
            window.location.href = targetUrl
        }
    }
</script>

<template>
    <router-link v-if="!backend" :to="to" class="relative flex items-center gap-2.5 mb-5 mr-4 rounded-xl text-[17px] font-medium py-3 px-4 text-white no-underline transition-all duration-200 ease-in-out hover:bg-[#406071] hover:cursor-pointer active:bg-[#406071] active:font-bold w-full">
        <slot name="icon"></slot>
        <span class="flex-1">{{ title }}</span>
        <slot></slot>
    </router-link>
    
    <a v-else @click.prevent="navigate" href="#" class="relative flex items-center gap-2.5 mb-5 mr-4 rounded-xl text-[17px] font-medium py-3 px-4 text-white no-underline transition-all duration-200 ease-in-out hover:bg-[#406071] hover:cursor-pointer active:bg-[#406071] active:font-bold w-full">
        <slot name="icon"></slot>
        <span class="flex-1">{{ title }}</span>
        <slot></slot>
    </a>
</template>