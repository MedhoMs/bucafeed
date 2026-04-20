<script setup>
import ManagementSection from '@/components/layouts/ManagementSection.vue'
import ManagementCard from '@/components/layouts/ManagementCard.vue'

defineProps({
    admins: { type: Array, required: true },
    teachers: { type: Array, required: true },
    students: { type: Array, required: true }
})
</script>

<template>
    <div class="text-white">
        <div v-for="sect in [{t:'Dirección', d:admins}, {t:'Cuerpo Docente', d:teachers}, {t:'Alumnado', d:students}]" :key="sect.t" class="mb-12">
            
            <ManagementSection :title="`${sect.t} | ${sect.d.length}`" />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <ManagementCard v-for="item in sect.d" :key="item.id" class="p-5 hover:bg-[#406071]/10">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-black text-white/80 bg-black/20 border border-white/5">
                            {{ item.name?.charAt(0) }}
                        </div>
                        <div>
                            <p class="font-black text-xs text-white/90 uppercase tracking-tight">{{ item.name }} {{ item.last_name || '' }}</p>
                            <p class="text-[9px] text-white/20 font-bold uppercase tracking-tighter mt-1">{{ item.email }}</p>
                        </div>
                    </div>
                </ManagementCard>
            </div>
        </div>
    </div>
</template>
