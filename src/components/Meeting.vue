<script setup>
import { computed } from 'vue';
import { user } from '../stores/auth';

const props = defineProps({
    id: Number,
    name: String,
    teacher: String,
    teacher_id: Number,
    group: String,
    group_id: [Number, null],
    schedule: String,
    description: String
})

const emit = defineEmits(['delete']);

const canManageMeeting = computed(() => {
    if (!user.value) return false;
    const role = user.value.role?.toLowerCase();
    const allowedRoles = ['teacher', 'admin', 'ei', 'administrador', 'profesor'];
    return allowedRoles.includes(role);
});

const handleDelete = () => {
    emit('delete', props.id);
};

</script>

<template>
    <div
        class="group relative bg-linear-to-br from-white/10 to-white/5 border border-white/10 rounded-3xl p-6 h-100 w-full max-w-sm transition-all duration-500 shadow-xl flex flex-col overflow-hidden">

        <!-- Imagen de fondo/decoración centrada y más grande -->
        <div
            class="absolute inset-0 flex items-center justify-center opacity-10 transition-opacity pointer-events-none">
            <img src="../assets/logo/logoTelamon.png" class="w-80 h-80 object-contain mb-10" alt="Decoration" />
        </div>

        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-xl font-black text-white uppercase tracking-tight line-clamp-2 pr-8">{{ props.name }}</h3>

            <button v-if="canManageMeeting" @click.stop="handleDelete"
                class="top-0 right-0 p-2 text-white/20 hover:text-white hover:bg-red-500 rounded-xl transition-all cursor-pointer z-20"
                title="Eliminar charla">
                <svg class="my-auto" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18" />
                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                    <line x1="10" y1="11" x2="10" y2="17" />
                    <line x1="14" y1="11" x2="14" y2="17" />
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6 relative z-10">
            <div>
                <p class="text-[9px] font-black uppercase text-white/20 tracking-[0.2em] mb-1">Ponente</p>
                <router-link v-if="props.teacher_id" :to="'/profile/' + props.teacher_id" class="text-xs font-bold text-white/80 truncate hover:text-primary-normal transition-colors">
                    {{ props.teacher }}
                </router-link>
                <p v-else class="text-xs font-bold text-white/80 truncate">{{ props.teacher }}</p>
            </div>
            <div>
                <p class="text-[9px] font-black uppercase text-white/20 tracking-[0.2em] mb-1">Horario</p>
                <p class="text-xs font-bold text-white/80 truncate">{{ props.schedule }}</p>
            </div>
        </div>

        <div class="mb-6 relative z-10">
            <p class="text-[9px] font-black uppercase text-white/20 tracking-[0.2em] mb-1">Grupo</p>
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-cyan-400 rounded-full animate-pulse"></span>
                <p class="text-xs font-bold text-white/80 truncate">{{ props.group }}</p>
            </div>
        </div>

        <div class="flex-1 min-h-0 mb-8 overflow-hidden relative z-10">
            <p class="text-[11px] font-medium text-white/40 leading-relaxed line-clamp-4 italic">
                {{ props.description || 'Sin descripción disponible para esta charla.' }}
            </p>
        </div>

        <div class="mt-auto pt-6 flex justify-end relative z-10">
            <router-link
                :to="{ name: 'meetingchat', params: { id: props.id, name: props.name, teacher: props.teacher, group: props.group, groupId: props.group_id } }"
                class="bg-[#1a2d42] hover:bg-[#406071] text-white text-[10px] font-black uppercase tracking-widest px-8 py-3 rounded-xl transition-all duration-300 active:scale-95 border border-white/5 shadow-lg shadow-black/20">
                Entrar al Chat
            </router-link>
        </div>
    </div>
</template>