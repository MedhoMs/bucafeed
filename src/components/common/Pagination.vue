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
    <nav v-if="lastPage > 1" role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center gap-1 sm:gap-2 mt-8">
        <!-- Previous Page Link -->
        <button 
            @click="changePage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="p-2 transition-colors group disabled:opacity-20 disabled:cursor-default"
            :class="currentPage === 1 ? 'text-white/20' : 'text-white/60 hover:text-white'"
            aria-label="Previous page"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
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
                class="px-3.5 py-2 text-sm transition-all rounded-xl"
                :class="[
                    page === currentPage 
                        ? 'z-10 bg-[#0f2828] hover:bg-[#507a8f] text-white font-black border border-[#179cf0]/30' 
                        : 'font-bold text-white/40 hover:text-white hover:bg-white/5'
                ]"
            >
                {{ page }}
            </button>
        </template>

        <!-- Next Page Link -->
        <button 
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === lastPage"
            class="p-2 transition-colors group disabled:opacity-20 disabled:cursor-default"
            :class="currentPage === lastPage ? 'text-white/20' : 'text-white/60 hover:text-white'"
            aria-label="Next page"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </button>
    </nav>
</template>
