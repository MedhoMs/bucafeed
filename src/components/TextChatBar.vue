<script setup>
    import { ref } from 'vue'
    import { useTranslations } from '@/composables/useTranslations'
    
    const { t } = useTranslations()
    const message = ref('')
    
    const emit = defineEmits(['sendMessage'])

    function sendMessage() {
        const text = message.value.trim()
        if (!text) return
        emit('sendMessage', text)
        message.value = ''
    }
</script>

<template>
    <div class="flex my-3 w-full">
        <div class="flex items-center border border-white rounded-[20px] p-3.75 w-50 lg:w-full">
            <input 
                v-model="message"
                type="text" 
                id="chatBar" 
                class="ml-3 w-full outline-hidden text-base border-none bg-transparent flex-1 text-[#e7e9ea] placeholder-[#8b98a5] placeholder-font-normal" 
                :placeholder="t.nav.search || 'Escribir un mensaje'"
                @keydown.enter="sendMessage"
            />
        </div>
    </div>
</template>