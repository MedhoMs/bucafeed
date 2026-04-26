<script setup>
defineProps({
    hover: { type: Boolean, default: true },
    glass: { type: Boolean, default: true }
})
</script>

<template>
    <div :class="[
        'border border-white/10 rounded-2xl overflow-hidden shadow-xl transition-all',
        glass ? 'bg-white/5' : 'bg-[#162a2a]',
        hover ? 'hover:border-white/20' : ''
    ]">
        <slot></slot>
    </div>
</template>
