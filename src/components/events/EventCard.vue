<script setup>
/**
 * EventCard.vue - Plantilla maestra unificada para eventos en TelamoNet
 */
import { ref, computed } from 'vue'
import { useTranslations } from '../../composables/useTranslations'
import { user } from '../../stores/auth'

const { t } = useTranslations()

const props = defineProps({
    event: { type: Object, required: true },
    mode: { type: String, default: 'public' }, // 'public' o 'manage'
    loading: { type: Boolean, default: false }
})

const emit = defineEmits(['edit', 'delete', 'details'])

const imageError = ref(false)

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
    const role = String(user.value.role || '').toLowerCase();
    const allowedRoles = ['admin', 'ei', 'administrador', 'staff'];
    return allowedRoles.includes(role);
});
</script>

<template>
    <div class="event-card-unified relative bg-[#0a1a1a]/60 border border-white/10 rounded-[2rem] overflow-hidden hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 backdrop-blur-md">
        
        <!-- HEADER / IMAGEN -->
        <div class="relative h-44 overflow-hidden group/header">
            <img v-if="event.image_url && !imageError" :src="event.image_url" @error="imageError = true" class="w-full h-full object-cover">
            <div v-else class="w-full h-full bg-secondary-normal/20 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#B7B7B7"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm40-80h480L570-480 450-320l-90-120-120 160Zm-40 80v-560 560Z"/></svg>
            </div>

            <!-- BOTÓN ELIMINAR (TOP RIGHT) -->
            <button 
                v-if="canManageEvent" 
                @click.stop="emit('delete', event)" 
                class="absolute top-3 right-3 z-50 p-2.5 bg-[#0a1a1a]/60 hover:bg-red-500 border border-white/10 rounded-2xl transition-all duration-300 backdrop-blur-md cursor-pointer shadow-2xl active:scale-90"
                title="Eliminar Evento"
            >
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#ffffff">
                    <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                </svg>
            </button>
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
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF"><path d="M536.5-503.5Q560-527 560-560t-23.5-56.5Q513-640 480-640t-56.5 23.5Q400-593 400-560t23.5 56.5Q447-480 480-480t56.5-23.5ZM480-186q122-112 181-203.5T720-552q0-109-69.5-178.5T480-800q-101 0-170.5 69.5T240-552q0 71 59 162.5T480-186Zm0 106Q319-217 239.5-334.5T160-552q0-150 96.5-239T480-880q127 0 223.5 89T800-552q0 100-79.5 217.5T480-80Zm0-480Z"/></svg>
                <span class="text-white/90 text-[9px] font-black tracking-widest truncate uppercase">{{ event.location || 'CIFP Zonzamas, Arrecife' }}</span>
            </div>

            <div class="flex items-center gap-1.5 mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" viewBox="0 -960 960 960" fill="#e3e3e3"><path d="M80-160v-112q0-33 17-62t47-44q51-26 115-44t141-18q30 0 58.5 3t55.5 9l-70 70q-11-2-21.5-2H400q-71 0-127.5 17T180-306q-9 5-14.5 14t-5.5 20v32h250l80 80H80Zm542 16L484-282l56-56 82 82 202-202 56 56-258 258ZM287-527q-47-47-47-113t47-113q47-47 113-47t113 47q47 47 47 113t-47 113q-47 47-113 47t-113-47Zm123 287Zm46.5-343.5Q480-607 480-640t-23.5-56.5Q433-720 400-720t-56.5 23.5Q320-673 320-640t23.5 56.5Q367-560 400-560t56.5-23.5ZM400-640Z"/></svg>
                <span class="text-white/90 text-[9px] font-black tracking-widest truncate uppercase">{{ (t.events.cardJoined || '{count} join').replace('{count}', event.participants_count || 0) }}</span>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-white/5 mt-auto">
                <div class="flex flex-col">
                    <span class="text-[9px] text-white/20 font-black uppercase tracking-widest mb-1">{{ t.events.timeLabel }}</span>
                    <span class="text-xs text-white/70 font-bold tracking-tighter">{{ startTime }} - {{ endTime }}</span>   
                </div>

                <div v-if="mode === 'manage'" class="flex items-center gap-1">
                    <button @click.stop="emit('edit', event)" class="p-2 text-white/20 hover:text-success-normal transition-colors active:scale-90 cursor-pointer flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF"><path d="M216-216h51l375-375-51-51-375 375v51Zm-72 72v-153l498-498q11-11 23.84-16 12.83-5 27-5 14.16 0 27.16 5t24 16l51 51q11 11 16 24t5 26.54q0 14.45-5.02 27.54T795-642L297-144H144Zm600-549-51-51 51 51Zm-127.95 76.95L591-642l51 51-25.95-25.05Z"/></svg>
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
