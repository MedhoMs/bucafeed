<script setup>
    import { ref, onMounted, nextTick } from 'vue'
    import { useTranslations } from '@/composables/useTranslations'
    import TextChatBar from './TextChatBar.vue';
    
    const { t } = useTranslations()
    const chatContainer = ref(null)
    const messages = ref([
        { text: 'Hola, ¿cómo estás?', sender: 'teacher' },
        { text: 'Bien, gracias!', sender: 'student' },
        { text: '¿Tienes alguna duda?', sender: 'teacher' },
        { text: 'Sí, no entiendo el ejercicio 3', sender: 'student' },
    ])
    
    const scrollToBottom = () => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight
        }
    }

    onMounted(() => {
        scrollToBottom()
    })
    
    async function handleSendMessage(msg) {
        messages.value.push({ text: msg, sender: 'student' })
        await nextTick()
        scrollToBottom()
    }

</script>

<template>
    <div class="flex flex-col w-full h-full overflow-hidden">
        <div ref="chatContainer" class="flex flex-col flex-1 min-h-0 pb-4 pr-2 overflow-y-auto scroll-smooth">
            <div class="flex flex-col justify-end min-h-max space-y-4 pt-4">
                <template v-for="(msg, index) in messages" :key="index">
                    <div v-if="msg.sender === 'teacher'" class="max-w-[85%] self-start">
                        <p class="ml-2 mb-1 text-xs text-gray-400">Profesor</p>
                        <p class="rounded-2xl bg-[#1e2e38] p-4 text-white shadow-md">{{ msg.text }}</p>
                    </div>

                    <div v-else class="max-w-[85%] self-end flex flex-col items-end">
                        <p class="mr-2 mb-1 text-xs text-gray-400">Alumno</p>
                        <p class="rounded-2xl bg-[#2a4a5a] p-4 text-white shadow-md">{{ msg.text }}</p>
                    </div>
                </template>
            </div>
        </div>

        <div class="pt-2 shrink-0">
            <TextChatBar @sendMessage="handleSendMessage" />
        </div>
    </div>
</template>

<style scoped>
/* Estilización básica del scrollbar */
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>