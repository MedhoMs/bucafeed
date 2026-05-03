<script setup>
import EmojiPicker from 'vue3-emoji-picker';
import 'vue3-emoji-picker/css';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    customClass: {
        type: String,
        default: 'right-4 lg:right-auto lg:left-1/2 lg:-translate-x-1/2'
    }
});

const emit = defineEmits(['select', 'close', 'update:show']);

const onSelectEmoji = (emoji) => {
    emit('select', emoji);
};

const close = () => {
    emit('update:show', false);
    emit('close');
};
</script>

<template>
    <div v-if="show" :class="['absolute bottom-full mb-2 z-50 shadow-2xl custom-emoji-picker-container', customClass]">
        <EmojiPicker 
            :native="true" 
            @select="onSelectEmoji" 
            theme="dark" 
        />
        <button @click="close" class="absolute -top-3 -right-3 bg-[#406071] rounded-full p-1 shadow-lg hover:bg-[#447c9a] text-white border-2 border-[#0f2828] transition-colors">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
    </div>
</template>

<style scoped>
.custom-emoji-picker-container {
    animation: fade-in 0.2s ease-out forwards;
}

@keyframes fade-in {
    from { 
        opacity: 0; 
        transform: translateY(10px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

/* Specific styling for the third party component */
:deep(.v3-emoji-picker) {
    background: linear-gradient(180deg, #1f5252 0%, #0f2828 100%) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 1rem !important;
}

:deep(.v3-emoji-picker .v3-search input) {
    background: rgba(255, 255, 255, 0.05) !important;
    color: white !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

:deep(.v3-emoji-picker .v3-groups button:hover) {
    background: rgba(255, 255, 255, 0.1) !important;
}

:deep(.v3-emoji-picker .v3-icon.v3-icon-active) {
    color: #a0c4d4 !important;
}

:deep(.v3-emoji-picker .v3-emojis button:hover) {
    background: #406071 !important;
    border-radius: 8px;
}
</style>
