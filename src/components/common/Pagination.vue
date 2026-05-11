<script setup>
import { computed } from 'vue';

const props = defineProps({
    currentPage: {
        type: Number,
        required: true
    },
    lastPage: {
        type: Number,
        required: true
    },
    maxLength: {
        type: Number,
        default: 7
    }
});

const emit = defineEmits(['change']);

const pages = computed(() => {
    const res = [];
    if (props.lastPage <= props.maxLength) {
        for (let i = 1; i <= props.lastPage; i++) res.push(i);
    } else {
        const sideWidth = props.maxLength % 2 === 0 ? props.maxLength / 2 : (props.maxLength - 1) / 2;
        const leftWidth = (props.maxLength - 1) / 2;
        const rightWidth = (props.maxLength - 1) / 2;

        if (props.currentPage <= props.maxLength - sideWidth - 1) {
            for (let i = 1; i <= props.maxLength - 1; i++) res.push(i);
            res.push('...');
            res.push(props.lastPage);
        } else if (props.currentPage >= props.lastPage - sideWidth) {
            res.push(1);
            res.push('...');
            for (let i = props.lastPage - props.maxLength + 2; i <= props.lastPage; i++) res.push(i);
        } else {
            res.push(1);
            res.push('...');
            for (let i = props.currentPage - leftWidth + 2; i <= props.currentPage + rightWidth - 2; i++) res.push(i);
            res.push('...');
            res.push(props.lastPage);
        }
    }
    return res;
});

const changePage = (page) => {
    if (page === '...' || page === props.currentPage || page < 1 || page > props.lastPage) return;
    emit('change', page);
};
</script>

<template>
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center gap-1 sm:gap-2 mt-8">
        <!-- Previous Page Link -->
        <button 
            @click="changePage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="w-10 h-10 flex items-center justify-center transition-all disabled:opacity-10 disabled:cursor-default"
            :class="currentPage === 1 ? 'text-white/20' : 'text-white/60 hover:text-white hover:bg-white/5 rounded-xl'"
            aria-label="Previous page"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>

        <!-- Pagination Elements -->
        <template v-for="(page, index) in pages" :key="index">
            <span v-if="page === '...'" class="px-3 py-2 text-white/20 select-none">
                {{ page }}
            </span>
            <button 
                v-else
                @click="changePage(page)"
                :aria-current="page === currentPage ? 'page' : undefined"
                class="w-10 h-10 flex items-center justify-center text-sm transition-all rounded-2xl border border-white/10"
                :class="[
                    page === currentPage 
                        ? 'bg-[#0f2828] text-white font-black shadow-lg' 
                        : 'bg-transparent font-bold text-white/40 hover:text-white hover:bg-white/5 border-transparent'
                ]"
            >
                {{ page }}
            </button>
        </template>

        <!-- Next Page Link -->
        <button 
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === lastPage"
            class="w-10 h-10 flex items-center justify-center transition-all disabled:opacity-10 disabled:cursor-default"
            :class="currentPage === lastPage ? 'text-white/20' : 'text-white/60 hover:text-white hover:bg-white/5 rounded-xl'"
            aria-label="Next page"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </button>
    </nav>
</template>
