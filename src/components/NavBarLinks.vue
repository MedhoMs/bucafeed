<script setup>
    const props = defineProps({
        title: {
            type: String,
            required: true
        },
        to: {
            type: String,
            required: true
        }
    })
</script>

<template>
    <router-link :to="to" class="relative flex items-center gap-[10px] mb-5 mr-4 rounded-xl text-[17px] font-[500] py-3 px-4 text-white no-underline transition-all duration-[0.2s] ease-in-out hover:bg-[#406071] hover:cursor-pointer active:bg-[#406071] active:font-bold">
        <slot>

        </slot>
        {{ title }}
    </router-link>
</template>