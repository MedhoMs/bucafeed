<script setup>
import { computed } from 'vue'
import ManagementSection from '@/components/layouts/ManagementSection.vue'
import ManagementCard from '@/components/layouts/ManagementCard.vue'
import PrimaryButton from '@/components/common/PrimaryButton.vue'

const props = defineProps({
    cycles: { type: Array, required: true },
    center: { type: Object, default: () => ({ type: 'HE' }) }
})
defineEmits(['openModal'])

const title = computed(() => {
    if (props.center?.type === 'PE') return 'Cursos Académicos'
    const category = props.center?.category?.toLowerCase() || ''
    if (props.center?.type === 'HE' && (category === 'university' || category === 'universidad')) return 'Grados Universitarios'
    return 'Ciclos Formativos'
})
</script>

<template>
    <div class="text-white">
        <div class="flex items-center justify-between gap-4 mb-12">
            <ManagementSection :title="`${title} | ${cycles.length}`" />
            
            <PrimaryButton class="cursor-pointer" 
                text="Añadir Ciclos" 
                icon="plus" 
                @click="$emit('openModal', 'enroll_cycles')" 
            />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <ManagementCard v-for="item in cycles" :key="item.id" class="p-5 hover:bg-[#406071]/10">
                <h3 class="font-black text-xs text-white/90 uppercase mb-2">{{ item.name }}</h3>
                <p class="text-[10px] text-white/20 font-bold mb-4 uppercase tracking-tighter">{{ item.area }}</p>
                <div class="flex flex-wrap gap-2">
                    <span v-for="tag in item.tags" :key="tag.id" class="bg-[#406071]/20 text-white/60 text-[8px] font-black px-3 py-1 rounded-lg uppercase tracking-widest border border-white/5">
                        {{ tag.name }}
                    </span>
                </div>
            </ManagementCard>
        </div>
    </div>
</template>
