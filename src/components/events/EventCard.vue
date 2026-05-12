<script setup>
/**
 * EventCard.vue - Plantilla maestra unificada para eventos en TelamoNet
 */
import { computed } from 'vue'
import { useTranslations } from '../../composables/useTranslations'
import { user } from '../../stores/auth'

const { t } = useTranslations()

const props = defineProps({
    event: { type: Object, required: true },
    mode: { type: String, default: 'public' }, // 'public' o 'manage'
    loading: { type: Boolean, default: false }
})

const emit = defineEmits(['edit', 'delete', 'details'])

const formattedDate = computed(() => {
    try {
        if (!props.event?.date) return t.value.events.noDate
        const d = new Date(props.event.date)
        if (isNaN(d.getTime())) return props.event.date
        return d.toISOString().split('T')[0]
    } catch (e) {
        return props.event?.date || '---'
    }
})

const startTime = computed(() => props.event.start_time?.substring(0, 5) || '00:00')
const endTime = computed(() => props.event.end_time?.substring(0, 5) || '00:00')

const canManageEvent = computed(() => {
    if (!user.value) return false;
    const role = user.value.role?.toLowerCase();
    const allowedRoles = ['admin', 'ei', 'administrador'];
    return allowedRoles.includes(role);
});
</script>

<template>
    <div class="event-card-unified relative bg-[#0a1a1a]/60 border border-white/10 rounded-[2rem] overflow-hidden hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 backdrop-blur-md">
        
        <!-- HEADER / IMAGEN -->
        <div class="relative h-44 overflow-hidden">
            <img v-if="event.image_url" :src="event.image_url" class="w-full h-full object-cover">
            <div v-else class="w-full h-full bg-secondary-normal/20 flex items-center justify-center">
                <span class="material-symbols-outlined !text-4xl opacity-10 text-white">image</span>
            </div>

            <!-- Badge Participantes (Solo en modo public) -->
            <div v-if="mode === 'public'" class="absolute top-4 right-4 bg-black/40 px-2 py-1 rounded-lg border border-white/10 text-[9px] font-black text-white/60">
                <button v-if="canManageEvent" @click.stop="emit('delete', event)" 
                    class="p-2 text-white/20 hover:text-red-400 transition-colors active:scale-90 cursor-pointer flex items-center justify-center"
                    title="Eliminar evento">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/></svg>
                </button>
            </div>
        </div>

        <!-- CONTENIDO -->
        <div class="p-6">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-white text-[9px] font-black uppercase tracking-[0.15em] bg-secondary-normal px-2 py-1 rounded-lg shadow-sm">{{ formattedDate }}</span>
                <span v-if="mode === 'public'" class="text-white/20 text-[9px] font-black uppercase tracking-widest">• {{ event.center_name }}</span>
            </div>
            
            <h3 class="text-white font-black uppercase text-base tracking-tight mb-2 truncate">{{ event.title }}</h3>
            <p class="text-white/90 text-[11px] font-bold leading-relaxed line-clamp-2 mb-4">{{ event.description }}</p>
            
            <div class="flex items-center gap-1.5 mb-5">
                <span class="material-symbols-outlined !text-[14px] text-secondary-normal shrink-0">location_on</span>
                <span class="text-white/90 text-[9px] font-black tracking-widest truncate uppercase">{{ event.location || 'CIFP Zonzamas, Arrecife' }}</span>
            </div>

            <div class="flex items-center gap-1.5 mb-5">
                <span class="material-symbols-outlined !text-[14px] text-secondary-normal shrink-0">location_on</span>
                <span class="text-white/90 text-[9px] font-black tracking-widest truncate uppercase">{{ (t.events.cardJoined || '{count} join').replace('{count}', event.participants_count || 0) }}</span>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-white/5 mt-auto">
                <div class="flex flex-col">
                    <span class="text-[9px] text-white/20 font-black uppercase tracking-widest mb-1">{{ t.events.timeLabel }}</span>
                    <span class="text-xs text-white/70 font-bold tracking-tighter">{{ startTime }} - {{ endTime }}</span>   
                </div>

                <div v-if="mode === 'manage'" class="flex items-center gap-1">
                    <button @click.stop="emit('edit', event)" class="p-2 text-white/20 hover:text-success-normal transition-colors active:scale-90 cursor-pointer flex items-center justify-center">
                        <span class="material-symbols-outlined !text-xl">edit</span>
                    </button>
                    <button @click.stop="emit('delete', event)" class="p-2 text-white/20 hover:text-red-400 transition-colors active:scale-90 cursor-pointer flex items-center justify-center">
                        <span class="material-symbols-outlined !text-xl">delete</span>
                    </button>
                </div>

                <!-- ACCIONES MODO PÚBLICO (ENTRAR) -->
                <div v-else class="flex items-center gap-3">
                    <button @click.stop="emit('details', event)" 
                        class="px-6 py-2 cursor-pointer rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 active:scale-95 shadow-lg bg-secondary-normal text-white border border-white/10 hover:bg-secondary-normal-hover">
                        {{ t.events.viewDetails }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.event-card-unified {
    width: 100%;
    max-width: 400px;
    box-shadow: 0 20px 40px -20px rgba(0,0,0,0.5);
}
@media (max-width: 640px) {
    .event-card-unified {
        max-width: none;
    }
}
</style>
