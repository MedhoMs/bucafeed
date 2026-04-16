<script setup>

    const props = defineProps({
        id: Number,
        name: String,
        teacher: String,
        schedule: String,
        description: String
    })

</script>

<template>
    <div class="
        w-81.25
        h-93.75 
        rounded-[20px] 
        p-5 relative 
        bg-linear-to-br 
        from-[rgba(255,255,255,0.08)] to-[rgba(255,255,255,0.04)] 
        border 
        border-[rgba(255,255,255,0.12)]
    ">

        <p class="text-2xl font-bold">{{ props.name }}</p>
        <div class="flex mt-4 mb-4">
            <p class="font-bold mr-2">Profesor:</p>
            <p>{{ props.teacher }}</p>
        </div>

        <div class="flex mt-2 mb-4">
            <p class="font-bold mr-2">Horario:</p>
            <p>{{ props.schedule }}</p>
        </div>

        <p class="h-36 overflow-y-auto">{{ props.description }}</p>
            
        <router-link 
            :to="{ name: 'meetingchat', params: { id: props.id, name: props.name, teacher: props.teacher } }"
            class="
            bg-[#0a2d4e]
            p-2 
            absolute 
            bottom-5 
            right-5 
            rounded-[20px] 
            w-25 
            text-base 
            text-center
            hover:bg-[#0a1e35] 
            hover:cursor-pointer 
            transition-colors 
            duration-200
        ">
            Entrar
        </router-link>
    </div>
</template>