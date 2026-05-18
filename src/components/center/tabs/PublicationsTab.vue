<script setup>
/**
 * PublicationsTab.vue - Gestión de Publicaciones para administradores de centros
 */
import { ref, computed } from 'vue'
import PublicationCard from '@/components/publications/PublicationCard.vue'
import PrimaryButton from '@/components/common/PrimaryButton.vue'

const props = defineProps({
    publications: { type: Array, default: () => [] }
})

const emit = defineEmits(['openModal', 'deleteItem'])

const search = ref('')
const filteredPublications = computed(() => {
    if (!search.value.trim()) return props.publications
    const s = search.value.toLowerCase()
    return props.publications.filter(p => 
        p.title.toLowerCase().includes(s) || 
        p.description.toLowerCase().includes(s)
    )
})
</script>

<template>
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-700">
        <!-- Header Unificado con Búsqueda y Acción -->
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center mb-10 px-2">
            <div class="flex-1 w-full max-w-2xl">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none text-white/20 group-focus-within:text-cyan-400 transition-colors">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Buscar publicaciones por título o descripción..." 
                        class="w-full bg-white/5 border border-white/10 rounded-[22px] py-4.5 pl-14 pr-6 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-500/50 focus:bg-white/10 transition-all font-bold tracking-tight shadow-inner"
                    >
                </div>
            </div>

            <PrimaryButton class="cursor-pointer" 
                text="Nueva Publicación" 
                icon="plus"
                @click="emit('openModal', 'publication')"
            />
        </div>

        <!-- Lista de Publicaciones -->
        <div v-if="filteredPublications.length > 0" class="flex flex-col gap-6 w-full pb-10">
            <PublicationCard 
                v-for="pub in filteredPublications" 
                :key="pub.id" 
                :publication="pub" 
                mode="manage"
                @edit="emit('openModal', 'edit_publication', pub)"
                @delete="emit('deleteItem', 'publication', pub)"
            />
        </div>

        <!-- Estado vacío -->
        <div v-else class="text-center py-20 bg-white/5 rounded-3xl border border-dashed border-white/10">
            <div class="w-16 h-16 bg-[#406071]/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-white/5">
                 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#406071" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line><line x1="3" y1="15" x2="21" y2="15"></line></svg>
            </div>
            <p class="text-white/20 text-[10px] font-black uppercase tracking-[0.2em]">No hay publicaciones activas</p>
            <p class="text-white/10 text-[9px] font-bold mt-1 uppercase">Crea tu primera publicación para compartir noticias con la comunidad</p>
        </div>
    </div>
</template>
