<script setup>
import ManagementSection from '@/components/layouts/ManagementSection.vue'
import ManagementCard from '@/components/layouts/ManagementCard.vue'
import PrimaryButton from '@/components/common/PrimaryButton.vue'
import { useRouter } from 'vue-router'
import { computed } from 'vue'
import { useTranslations } from '@/composables/useTranslations'

const { t } = useTranslations()
const router = useRouter()

const props = defineProps({
    admins: { type: Array, required: true },
    teachers: { type: Array, required: true },
    students: { type: Array, required: true },
    pendingStudents: { type: Array, default: () => [] },
    pendingTeachers: { type: Array, default: () => [] }
})
const emit = defineEmits(['openModal', 'verifyStudent', 'verifyTeacher'])

const allPending = computed(() => {
    return [
        ...(props.pendingTeachers || []).map(t => ({ ...t, isTeacher: true })),
        ...(props.pendingStudents || []).map(s => ({ ...s, isTeacher: false }))
    ]
})

const normalSections = computed(() => [
    { t: t.value.manager?.people?.sections?.admins || 'Dirección', d: props.admins },
    { t: t.value.manager?.people?.sections?.teachers || 'Cuerpo Docente', d: props.teachers },
    { t: t.value.manager?.people?.sections?.students || 'Alumnado', d: props.students }
])

const goToProfile = (id) => {
    router.push(`/profile/${id}`)
}
</script>

<template>
    <div class="text-white">
        <div class="flex items-center justify-end mb-8">
            <PrimaryButton class="cursor-pointer" 
                :text="t.manager?.people?.enrollPeople || 'Matricular Personas'" 
                icon="plus" 
                @click="$emit('openModal', 'enroll_users')" 
            />
        </div>

        <!-- ── Sección: Pendientes de Verificación ── -->
        <div v-if="allPending && allPending.length > 0" class="mb-12">
            <ManagementSection :title="`${t.manager?.people?.pendingVerification || 'Pendientes de Verificación'} | ${allPending.length}`" />

            <!-- Banner informativo -->
            <div class="flex items-center gap-3 mb-5 bg-amber-500/10 border border-amber-500/25 rounded-xl px-5 py-3.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400 shrink-0">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <p class="text-amber-300/80 text-xs font-bold uppercase tracking-wider">
                    {{ t.manager?.people?.pendingAlertText || 'Estos usuarios han solicitado acceso y esperan verificación. Accede a su perfil para comprobar su identidad antes de validarlos.' }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <ManagementCard 
                    v-for="item in allPending" 
                    :key="item.id" 
                    class="p-5 border border-amber-500/20 bg-amber-500/5 hover:bg-amber-500/10 transition-colors relative overflow-hidden"
                >
                    <!-- Indicador de pendiente -->
                    <div class="absolute top-3 right-3">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-500/20 border border-amber-500/30 text-amber-300 text-[9px] font-black uppercase tracking-widest">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                            {{ t.manager?.people?.pendingStatus || 'Pendiente' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-4 mb-4">
                        <!-- Avatar -->
                        <button 
                            @click="goToProfile(item.id)"
                            class="w-11 h-11 rounded-full flex items-center justify-center text-sm font-black text-white/90 bg-amber-500/20 border-2 border-amber-500/30 hover:border-amber-400 transition-all shrink-0 cursor-pointer"
                            :title="t.manager?.people?.viewProfileOf?.replace('{name}', item.name) || `Ver perfil de ${item.name}`"
                        >
                            {{ item.name?.charAt(0) }}{{ item.last_name?.charAt(0) }}
                        </button>
                        <div class="min-w-0 flex-1">
                            <p class="font-black text-xs text-white/90 uppercase tracking-tight truncate">
                                {{ item.name }} {{ item.last_name || '' }}
                            </p>
                            <p class="text-[9px] text-amber-400/70 font-black uppercase tracking-tighter mt-0.5 animate-pulse">
                                {{ item.isTeacher ? (t.manager?.people?.teacherRole || 'Profesor') : (t.manager?.people?.studentRole || 'Alumno') }} <span class="text-white/40 font-normal">| {{ item.education_level || 'Sin nivel' }}</span>
                            </p>
                            <p class="text-[9px] text-white/20 font-bold uppercase tracking-tighter mt-0.5 truncate">
                                {{ item.email }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-3 pt-3 border-t border-white/5">
                        <!-- Ver perfil -->
                        <button 
                            @click="goToProfile(item.id)"
                            class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-white/70 hover:text-white transition-all text-[10px] font-black uppercase tracking-widest cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            {{ t.manager?.people?.viewProfile || 'Ver Perfil' }}
                        </button>
                        <!-- Verificar -->
                        <button 
                            @click="$emit(item.isTeacher ? 'verifyTeacher' : 'verifyStudent', item.id)"
                            class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-emerald-500/15 hover:bg-emerald-500/30 border border-emerald-500/25 hover:border-emerald-400/50 text-emerald-400 hover:text-emerald-300 transition-all text-[10px] font-black uppercase tracking-widest active:scale-95 cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5"/>
                            </svg>
                            {{ t.manager?.people?.verify || 'Verificar' }}
                        </button>
                    </div>
                </ManagementCard>
            </div>
        </div>

        <!-- ── Secciones normales: Dirección, Cuerpo Docente, Alumnado ── -->
        <div v-for="sect in normalSections" :key="sect.t" class="mb-12">
            
            <ManagementSection :title="`${sect.t} | ${sect.d.length}`" />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <ManagementCard v-for="item in sect.d" :key="item.id" class="p-5 hover:bg-[#406071]/10">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-black text-white/80 bg-black/20 border border-white/5">
                            {{ item.name?.charAt(0) }}
                        </div>
                        <div>
                            <p class="font-black text-xs text-white/90 uppercase tracking-tight">{{ item.name }} {{ item.last_name || '' }}</p>
                            <p class="text-[9px] text-[#406071] font-black uppercase tracking-tighter mt-1">{{ item.role_name || item.role }}</p>
                            <p class="text-[9px] text-white/20 font-bold uppercase tracking-tighter mt-0.5">{{ item.email }}</p>
                        </div>
                    </div>
                </ManagementCard>
            </div>
        </div>
    </div>
</template>
