<script setup>
/**
 * EventCard.vue - Plantilla maestra unificada para eventos en TelamoNet
 */
import { computed } from 'vue'

const props = defineProps({
    event: { type: Object, required: true },
    mode: { type: String, default: 'public' }, // 'public' o 'manage'
    loading: { type: Boolean, default: false }
})

const emit = defineEmits(['edit', 'delete', 'details'])

const formattedDate = computed(() => {
    try {
        if (!props.event?.date) return 'Sin fecha'
        const d = new Date(props.event.date)
        if (isNaN(d.getTime())) return props.event.date
        return d.toISOString().split('T')[0]
    } catch (e) {
        return props.event?.date || '---'
    }
})

const startTime = computed(() => props.event.start_time?.substring(0, 5) || '00:00')
const endTime = computed(() => props.event.end_time?.substring(0, 5) || '00:00')
</script>

<template>
    <div @click="emit('details', event)" class="event-card-unified relative bg-[#1a2d37] border border-white/10 rounded-[2rem] overflow-hidden cursor-pointer hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
        
        <!-- HEADER / IMAGEN -->
        <div class="relative h-44 overflow-hidden">
            <img v-if="event.image_url" :src="event.image_url" class="w-full h-full object-cover opacity-60">
            <div v-else class="w-full h-full bg-[#406071]/20 flex items-center justify-center">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1" class="opacity-10"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
            </div>
            
            <!-- Badge Participantes (Solo en modo public) -->
            <div v-if="mode === 'public'" class="absolute top-4 right-4 bg-black/40 px-2 py-1 rounded-lg border border-white/10 text-[9px] font-black text-white/60">
                Se han unido {{ event.participants_count || 0 }} alumnos a este evento
            </div>
        </div>

        <!-- CONTENIDO -->
        <div class="p-6">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-cyan-400 text-[9px] font-black uppercase tracking-[0.15em] bg-cyan-400/10 px-2 py-1 rounded-lg">{{ formattedDate }}</span>
                <span v-if="mode === 'public'" class="text-white/20 text-[9px] font-black uppercase tracking-widest">• {{ event.center_name }}</span>
            </div>
            
            <h3 class="text-white font-black uppercase text-base tracking-tight mb-2 truncate">{{ event.title }}</h3>
            <p class="text-white/30 text-[11px] font-bold leading-relaxed line-clamp-2 mb-4">{{ event.description }}</p>
            
            <div class="flex items-center gap-1.5 mb-5 opacity-80">
                <span class="material-symbols-outlined !text-[14px] text-cyan-400 shrink-0">location_on</span>
                <span class="text-white/60 text-[9px] font-black tracking-widest truncate uppercase">{{ event.location || 'CIFP Zonzamas, Arrecife' }}</span>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-white/5 mt-auto">
                <div class="flex flex-col">
                    <span class="text-[9px] text-white/20 font-black uppercase tracking-widest mb-1">Hora</span>
                    <span class="text-xs text-white/70 font-bold tracking-tighter">{{ startTime }} - {{ endTime }}</span>
                </div>

                <!-- ACCIONES MODO GESTIÓN (ADMIN) -->
                <div v-if="mode === 'manage'" class="flex items-center gap-1">
                    <button @click.stop="emit('edit', event)" class="p-2 text-white/20 hover:text-cyan-400 transition-colors active:scale-90 cursor-pointer">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button @click.stop="emit('delete', event)" class="p-2 text-white/20 hover:text-red-400 transition-colors active:scale-90 cursor-pointer">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                </div>

                <!-- ACCIONES MODO PÚBLICO (ENTRAR) -->
                <div v-else>
                    <button @click.stop="emit('details', event)" 
                        class="px-6 py-2 cursor-pointer rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 active:scale-95 shadow-lg bg-[#1a2d42] text-white/80 border border-white/5 hover:bg-white/5 hover:text-white">
                        Ver Detalles
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
