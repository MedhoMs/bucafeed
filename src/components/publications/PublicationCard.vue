<script setup>
/**
 * PublicationCard.vue - Plantilla maestra unificada para publicaciones en TelamoNet (Estilo Foro)
 */
import { computed } from 'vue'
import { useTranslations } from '../../composables/useTranslations'
import { user } from '../../stores/auth'

const { t } = useTranslations()

const props = defineProps({
    publication: { type: Object, required: true },
    mode: { type: String, default: 'public' }, // 'public' o 'manage'
    loading: { type: Boolean, default: false }
})

const emit = defineEmits(['edit', 'delete', 'details'])

const formattedDate = computed(() => {
    try {
        if (!props.publication?.created_at) return ''
        const d = new Date(props.publication.created_at)
        if (isNaN(d.getTime())) return props.publication.created_at
        return d.toISOString().split('T')[0]
    } catch (e) {
        return props.publication?.created_at || '---'
    }
})

const canManagePublication = computed(() => {
    if (!user.value) return false;
    const role = String(user.value.role || '').toLowerCase();
    const allowedRoles = ['admin', 'ei', 'administrador', 'staff'];
    
    if (role === 'ei') {
        return user.value.educational_center_id === props.publication.educational_center_id;
    }
    
    return allowedRoles.includes(role);
});
</script>

<template>
    <div class="post-card text-left w-full transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl flex flex-col md:flex-row gap-6">
        
        <!-- COLUMNA DE CONTENIDO (IZQUIERDA EN DESKTOP) -->
        <div class="flex-1 flex flex-col justify-between min-w-0">
            <div>
                <!-- HEADER INFO (Centro y Fecha) -->
                <div class="flex items-center justify-between gap-3 mb-4 w-full">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-secondary-normal/30 border border-white/10 flex items-center justify-center font-black text-white shadow-md">
                            {{ publication.center_name?.charAt(0) || 'P' }}
                        </div>
                        <div class="flex flex-col pr-6">
                            <span class="text-sm font-bold text-white truncate max-w-[240px] md:max-w-[320px]" :title="publication.center_name">
                                {{ publication.center_name }}
                            </span>
                            <span class="text-xs text-white/40 uppercase tracking-wider">
                                Novedades • {{ formattedDate }}
                            </span>
                        </div>
                    </div>

                    <!-- BOTÓN ELIMINAR (INTEGRADO EN HEADER) -->
                    <button 
                        v-if="canManagePublication" 
                        @click.stop="emit('delete', publication)" 
                        class="p-2.5 rounded-xl bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-all active:scale-90 z-10 cursor-pointer"
                        title="Eliminar Publicación"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </button>
                </div>

                <!-- TÍTULO Y CONTENIDO -->
                <h2 class="post-title text-white leading-snug" :title="publication.title">{{ publication.title }}</h2>
                <p class="text-white/80 mt-2 text-sm line-clamp-3 leading-relaxed">{{ publication.description }}</p>

                <!-- PREVIEW DE IMAGEN MÓVIL (SOLO VISIBLE EN MÓVIL) -->
                <div v-if="publication.image_url" class="block md:hidden mt-4 w-full h-48 rounded-xl overflow-hidden border border-white/10 shadow-md">
                    <img :src="publication.image_url" alt="Preview" class="w-full h-full object-cover" />
                </div>
            </div>

            <!-- FOOTER -->
            <div class="post-footer mt-6 flex items-center justify-between pt-4 border-t border-white/5 w-full">
                <div class="flex flex-col">
                    <span class="text-[9px] text-white/20 font-black uppercase tracking-widest mb-1">Sección</span>
                    <span class="text-xs text-white/70 font-bold tracking-tighter">Novedades</span>   
                </div>

                <div v-if="mode === 'manage'" class="flex items-center gap-2">
                    <button @click.stop="emit('edit', publication)" class="px-6 py-2 bg-white/5 hover:bg-white/10 border border-white/10 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 active:scale-95 shadow-lg flex items-center gap-2 cursor-pointer" title="Editar Publicación">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                        Editar
                    </button>
                </div>

                <!-- ACCIONES MODO PÚBLICO (DETALLES) -->
                <div v-else class="flex items-center gap-3">
                    <button @click.stop="emit('details', publication)" 
                        class="px-6 py-2 cursor-pointer rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 active:scale-95 shadow-lg bg-secondary-normal text-white border border-white/10 hover:bg-secondary-normal-hover">
                        {{ t.publications?.viewDetails || 'Leer más' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- COLUMNA DE IMAGEN DESKTOP (SOLO VISIBLE EN DESKTOP) -->
        <div v-if="publication.image_url" class="hidden md:block md:w-[32%] lg:w-[26%] rounded-2xl overflow-hidden border border-white/10 shadow-lg flex-shrink-0 relative self-stretch min-h-[180px]">
            <img :src="publication.image_url" alt="Preview" class="absolute inset-0 w-full h-full object-cover transition-all duration-500 hover:scale-105" />
        </div>

    </div>
</template>

<style scoped>
    .post-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.07) 0%, rgba(255, 255, 255, 0.03) 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 24px;
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
        width: 100%;
        backdrop-filter: blur(16px);
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
    }

    .post-card:hover {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.04) 100%);
        border-color: rgba(255, 255, 255, 0.15);
        box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.3), 0 0 1px 1px rgba(255, 255, 255, 0.1) inset;
    }

    .post-title {
        font-size: 24px;
        font-weight: 900;
        letter-spacing: normal;
        margin-top: 8px;
    }
</style>
