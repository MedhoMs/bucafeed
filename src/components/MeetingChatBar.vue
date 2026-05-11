<script setup>
import { useRoute } from 'vue-router';
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue'
import { io } from 'socket.io-client';
import { useTranslations } from '@/composables/useTranslations'
import { useApi } from '@/composables/useApi';
import { user } from '@/stores/auth';
import TextChatBar from './TextChatBar.vue';

const { get, post } = useApi();
const socket = ref(null);

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

const messages = ref([])

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight
        }
    })
}

const fetchMessages = async () => {
    if (!meetingId.value) return;
    try {
        const response = await get(`meetings/${meetingId.value}/mensajes`);
        if (response && response.mensajes) {
            messages.value = response.mensajes.map(m => ({
                id: m.id,
                content: m.contenido,
                type: 'text',
                sender: m.usuario.rol.toLowerCase().includes('profesor') || m.usuario.rol.toLowerCase().includes('teacher') ? 'teacher' : 'student',
                userName: `${m.usuario.nombre} ${m.usuario.apellido}`,
                userId: m.usuario.id,
                timestamp: m.fecha_hora
            }));
            scrollToBottom();
        }
    } catch (error) {
        console.error('Error fetching messages:', error);
    }
};

onMounted(() => {
    fetchMessages();

    // Configuration Socket.io
    const socketUrl = import.meta.env.VITE_SOCKET_URL || 'http://localhost:3000';
    socket.value = io(socketUrl);

    socket.value.on('connect', () => {
        console.log('Conectado al servidor de sockets');
        socket.value.emit('join-room', `meeting_${meetingId.value}`, user.value?.id);
    });

    socket.value.on('receive-message', (data) => {
        if (data.userId !== user.value?.id) {
            messages.value.push({
                content: data.message,
                type: data.type || 'text',
                sender: data.role.toLowerCase().includes('profesor') || data.role.toLowerCase().includes('teacher') ? 'teacher' : 'student',
                userName: data.userName,
                userId: data.userId,
                fileName: data.fileName
            });
            scrollToBottom();
        }
    });
})

onUnmounted(() => {
    if (socket.value) {
        socket.value.disconnect();
    }
});

async function handleSendMessage(msgObj) {
    if (!user.value) return;

    const role = user.value.role?.toLowerCase() || 'student';
    const isTeacher = role.includes('profesor') || role.includes('teacher');
    
    try {
        const response = await post(`meetings/${meetingId.value}/mensajes`, {
            contenido: msgObj.content,
            id_usuario: user.value.id
        });

        if (response) {
            const newMessage = {
                content: msgObj.content,
                type: msgObj.type,
                fileName: msgObj.fileName,
                sender: isTeacher ? 'teacher' : 'student',
                userName: `${user.value.name} ${user.value.last_name || ''}`,
                userId: user.value.id
            };
            
            messages.value.push(newMessage);

            socket.value.emit('send-message', {
                roomId: `meeting_${meetingId.value}`,
                message: msgObj.content,
                userId: user.value.id,
                userName: `${user.value.name} ${user.value.last_name || ''}`,
                role: role,
                type: msgObj.type,
                fileName: msgObj.fileName
            });

            scrollToBottom();
        }
    } catch (error) {
        console.error('Error sending message:', error);
    }
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
                            {{ msg.userName || (msg.sender === 'teacher' ? (meetingId ? `${meetingTeacher}` : 'Profesor') : 'Alumno') }}
                        </p>

                        <div
                            :class="[msg.sender === 'teacher' ? 'bg-[#1e2e38]' : 'bg-[#2a4a5a]', 'rounded-2xl p-4 text-white shadow-md w-fit max-w-full overflow-hidden']">
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