<script setup>
import ManagementSection from '@/components/layouts/ManagementSection.vue'
import ManagementCard from '@/components/layouts/ManagementCard.vue'

defineProps({
    groups: { type: Array, required: true },
    expandedGroup: { type: Number, default: null },
    confirmingDelete: { type: Number, default: null }
})

defineEmits(['update:expandedGroup', 'update:confirmingDelete', 'openModal', 'deleteItem', 'getTeacherName'])
</script>

<template>
    <div class="text-white">
        <ManagementSection title="Gestión de Grupos" addButtonText="NUEVO GRUPO" @add="$emit('openModal', 'create')" />
        
        <ManagementCard v-for="g in groups" :key="g.id" class="mb-4">
            <div @click="$emit('update:expandedGroup', expandedGroup === g.id ? null : g.id)" class="p-5 flex justify-between items-center cursor-pointer">
                <div class="flex items-center gap-5">
                    <div class="w-12 h-12 rounded-xl bg-black/20 flex items-center justify-center text-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-lg text-white/90 uppercase tracking-tighter">{{ g.name }}</h3>
                        <p class="text-[10px] text-white/20 font-black uppercase tracking-[0.1em]">
                            {{ g.cycle?.name || 'S/C' }} · {{ g.students?.length || 0 }} ALUMNOS
                        </p>
                    </div>
                </div>
                <div :class="['text-white/20 transition-transform duration-500', expandedGroup === g.id && 'rotate-180']">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </div>
            </div>
            
            <div v-if="expandedGroup === g.id" class="px-5 pb-6 space-y-6 bg-black/10 border-t border-white/5 pt-5">
                <div class="flex flex-wrap gap-2">
                    <button v-for="a in [{t:'Cambiar Tutor', id:'tutor'}, {t:'Añadir Alumnos', id:'students'}, {t:'Asignar Materia', id:'subject'}]" :key="a.id" @click.stop="$emit('openModal', a.id, g)" class="text-[9px] font-black uppercase tracking-widest bg-white/5 hover:bg-[#406071] px-4 py-2 rounded-xl border border-white/5 transition-all">
                        {{ a.t }}
                    </button>
                    <button v-if="confirmingDelete !== g.id" @click.stop="$emit('update:confirmingDelete', g.id)" class="ml-auto text-[9px] font-black uppercase tracking-widest text-red-500/50 hover:text-red-500">Eliminar</button>
                    <div v-else class="ml-auto flex gap-4 items-center">
                        <span class="text-[9px] text-red-500 font-black tracking-widest">¿ELIMINAR?</span>
                        <button @click.stop="$emit('deleteItem', 'group', g)" class="text-[9px] font-black text-red-500 underline underline-offset-4">SÍ</button>
                        <button @click.stop="$emit('update:confirmingDelete', null)" class="text-[9px] text-white/30 font-black">NO</button>
                    </div>
                </div>

                <div v-for="s in [{t:'Listado de Alumnos',d:g.students,id:'student'}, {t:'Asignaturas y Docencia',d:g.subjects_with_teachers,id:'subject'}]" :key="s.t">
                    <p class="text-[9px] uppercase font-black text-white/10 tracking-[0.2em] mb-4 border-b border-white/5 pb-2">{{ s.t }}</p>
                    <div v-if="!s.d?.length" class="text-[10px] font-bold text-white/10 italic">Sección vacía</div>
                    <div v-else class="flex flex-wrap gap-2">
                        <div v-for="i in s.d" :key="i.id" class="bg-black/20 border border-white/5 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-tight flex items-center gap-3">
                            <span class="text-white/60">
                                <svg v-if="s.id=='subject'" class="inline-block mr-2 opacity-30" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                {{ i.name }}
                            </span>
                            <span v-if="s.id=='subject'" class="text-white/20 font-medium italic">— {{ $emit('getTeacherName', i.pivot?.user_id) }}</span>
                            <button @click.stop="$emit('deleteItem', s.id, g, i.id)" class="text-red-500/20 hover:text-red-500 transition-colors">✕</button>
                        </div>
                    </div>
                </div>
            </div>
        </ManagementCard>
    </div>
</template>
