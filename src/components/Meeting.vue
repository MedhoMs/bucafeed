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
        w-[325px]
        h-[375px] 
        rounded-[20px] 
        p-5 relative 
        bg-gradient-to-br 
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
            
        <button class="
            bg-[#0a2d4e]
            p-2 
            absolute 
            bottom-5 
            right-5 
            rounded-[20px] 
            w-[100px] 
            text-base 
            hover:bg-[#0a1e35] 
            hover:cursor-pointer 
            transition-colors 
            duration-200
        ">
            Entrar
        </button>
    </div>
</template>