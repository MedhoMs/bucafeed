<script setup>
import PrimaryButton from '@/components/common/PrimaryButton.vue'

defineProps({
    title: { type: String, required: true },
    addButtonText: { type: String, default: '' }
})
defineEmits(['add'])
</script>

<template>
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-8 pl-1">
        <h2 class="text-xl font-black text-white uppercase tracking-tighter">{{ title }}</h2>
        <PrimaryButton class="cursor-pointer" 
            v-if="addButtonText" 
            :text="addButtonText" 
            icon="plus"
            @click="$emit('add')" 
        />
    </div>
</template>
