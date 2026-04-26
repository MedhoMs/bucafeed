<script setup>
    import { computed } from 'vue';
    import { user } from '../stores/auth';

    const props = defineProps({
        id: Number,
        name: String,
        teacher: String,
        group: String,
        schedule: String,
        description: String
    })

    const emit = defineEmits(['delete']);

    const canManageMeeting = computed(() => {
        if (!user.value) return false;
        // Solo Profesores, Admins globales y Admins de Centro pueden borrar
        const allowedRoles = ['Teacher', 'Admin', 'EI'];
        return allowedRoles.includes(user.value.role);
    });

    const handleDelete = () => {
        if (confirm('¿Estás seguro de que quieres eliminar esta charla?')) {
            emit('delete', props.id);
        }
    };

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
        hover:border-white/30
        transition-all
    ">

        <div class="flex justify-between items-start mb-2">
            <p class="text-2xl font-bold pr-8">{{ props.name }}</p>
            
            <!-- Botón de Eliminar -->
            <button 
                v-if="canManageMeeting"
                @click="handleDelete"
                class="absolute top-4 right-4 p-2 text-white/30 hover:text-red-500 hover:bg-red-500/10 rounded-full transition-all cursor-pointer"
                title="Eliminar charla"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
            </button>
        </div>

        <div class="flex mt-2 mb-4">
            <p class="font-bold mr-2">Profesor:</p>
            <p>{{ props.teacher }}</p>
        </div>

        <div class="flex mt-2 mb-4">
            <p class="font-bold mr-2">Horario:</p>
            <p>{{ props.schedule }}</p>
        </div>

        <div class="flex mt-2 mb-4">
            <p class="font-bold mr-2">Grupo:</p>
            <p>{{ props.group }}</p>
        </div>

        <p class="h-30 overflow-y-auto">{{ props.description }}</p>
            
        <router-link 
            :to="{ name: 'meetingchat', params: { id: props.id, name: props.name, teacher: props.teacher, group: props.group } }"
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
            hover:bg-[#0a3a65] 
            hover:cursor-pointer 
            transition-colors 
            duration-200
        ">
            Entrar
        </router-link>
    </div>
</template>