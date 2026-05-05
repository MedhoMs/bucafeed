<script setup>
import NavBar from '@/components/NavBar/NavBar.vue'

defineProps({
    loading: { type: Boolean, default: false },
    hasData: { type: Boolean, default: true },
    noDataIcon: { type: String, default: '🏫' },
    noDataMessage: { type: String, default: 'Sin datos disponibles' },
    toast: { type: Object, default: () => ({ show: false, msg: '', type: 'success' }) }
})
</script>

<template>
    <div class="min-h-screen">
        <NavBar />
        <main class="lg:pl-75 pt-16 lg:pt-0 flex min-h-screen">
            <section class="text-white w-full max-w-screen-2xl mx-auto px-6 py-10 lg:px-14">
    
                <div v-if="toast.show" :class="['fixed top-6 left-1/2 -translate-x-1/2 z-[200] px-6 py-3 rounded-xl shadow-2xl font-semibold text-sm', toast.type === 'error' ? 'bg-red-500 text-white' : 'bg-emerald-500 text-white']">
                    {{ toast.msg }}
                </div>
    
                <div v-if="loading" class="flex items-center justify-center min-h-[60vh]">
                    <div class="w-12 h-12 border-4 border-emerald-400/30 border-t-emerald-400 rounded-full animate-spin"></div>
                </div>
    
                <template v-else-if="hasData">
                    <slot></slot>
                </template>
    
                <div v-else class="flex flex-col items-center justify-center min-h-[60vh]">
                    <p class="text-4xl mb-4">{{ noDataIcon }}</p>
                    <p class="text-white/40 text-center" v-html="noDataMessage"></p>
                </div>
    
            </section>
        </main>
    </div>
</template>
