<script setup>
import { useRoute } from 'vue-router';
import { ref, onMounted, nextTick, computed } from 'vue'
import { useTranslations } from '@/composables/useTranslations'
import TextChatBar from './TextChatBar.vue';

const { t } = useTranslations()
const route = useRoute()
const chatContainer = ref(null)

const props = defineProps({
    meeting: {
        type: Object,
        default: null
    }
});

const meetingId = computed(() => route.params.id);
const meetingTeacher = computed(() => {
    if (props.meeting?.teacher) {
        return `${props.meeting.teacher.name} ${props.meeting.teacher.last_name || ''}`;
    }
    return route.params.teacher;
});

const messages = ref([
    { content: 'Hola, ¿cómo estás?', type: 'text', sender: 'teacher' },
    { content: 'Bien, gracias!', type: 'text', sender: 'student' },
    { content: '¿Tienes alguna duda?', type: 'text', sender: 'teacher' },
    { content: 'Sí, no entiendo el ejercicio 3', type: 'text', sender: 'student' },
])

const scrollToBottom = () => {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    }
}

onMounted(() => {
    scrollToBottom()
})

async function handleSendMessage(msgObj) {
    messages.value.push({
        content: msgObj.content,
        type: msgObj.type,
        fileName: msgObj.fileName,
        sender: 'student'
    })
    await nextTick()
    scrollToBottom()
}

</script>

<template>
    <div class="flex flex-col w-full h-full overflow-hidden">
        <div ref="chatContainer" class="flex flex-col flex-1 min-h-0 pb-4 pr-2 overflow-y-auto scroll-smooth">
            <div class="flex flex-col justify-end min-h-max space-y-4 pt-4">
                <template v-for="(msg, index) in messages" :key="index">
                    <div
                        :class="[msg.sender === 'teacher' ? 'self-start' : 'self-end flex flex-col items-end', 'max-w-3/5 min-w-0']">
                        <p :class="[msg.sender === 'teacher' ? 'ml-2' : 'mr-2', 'mb-1 text-xs text-gray-400']">
                            {{ msg.sender === 'teacher' ? (meetingId ? `${meetingTeacher}` : 'Profesor') : 'Alumno' }}
                        </p>

                        <div
                            :class="[msg.sender === 'teacher' ? 'bg-[#1e2e38]' : 'bg-[#2a4a5a]', 'rounded-2xl p-4 text-white shadow-md w-full overflow-hidden']">
                            <!-- Text Message -->
                            <p v-if="msg.type === 'text'" class="break-all whitespace-pre-wrap">{{ msg.content }}</p>

                            <!-- Image Message -->
                            <img v-else-if="msg.type === 'image'" :src="msg.content"
                                class="max-w-full rounded-lg cursor-pointer hover:opacity-90"
                                @click="window.open(msg.content, '_blank')" />

                            <!-- PDF Message -->
                            <a v-else-if="msg.type === 'pdf'" :href="msg.content" target="_blank"
                                class="flex items-center gap-3 bg-[#152027] hover:bg-[#0d151a] p-3 rounded-xl transition-colors no-underline text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                    fill="none" stroke="#ff4444" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                    <path d="M9 12l0 3" />
                                    <path d="M12 12l0 3" />
                                    <path d="M15 12l0 3" />
                                    <path d="M9 12l6 0" />
                                </svg>
                                <div class="flex flex-col overflow-hidden">
                                    <span class="text-sm font-bold truncate">{{ msg.fileName }}</span>
                                    <span class="text-xs text-gray-400">PDF Document</span>
                                </div>
                            </a>
                        </div>
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