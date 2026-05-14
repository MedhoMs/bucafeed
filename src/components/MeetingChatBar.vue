<script setup>
import { useRoute } from 'vue-router';
import { ref, onMounted, nextTick, computed, onUnmounted } from 'vue'
import { useTranslations } from '@/composables/useTranslations'
import { useApi } from '@/composables/useApi'
import { useSocket } from '@/composables/useSocket'
import { user as authUser } from '@/stores/auth'
import TextChatBar from './TextChatBar.vue';
import KahootMessage from './KahootMessage.vue';

const { get, post } = useApi()
const route = useRoute()
const chatContainer = ref(null)

const props = defineProps({
    meeting: { type: Object, default: null },
    activeCallId: { type: String, default: null }
});

const emit = defineEmits(['joinCall']);

const meetingId = computed(() => route.params.id);
const canCreateKahoot = computed(() => authUser.value && ['Teacher', 'Admin', 'EI'].includes(authUser.value.role));

const { connect: connectSocket, emit: emitSocket, on: onSocket, off: offSocket, disconnect: disconnectSocket } = useSocket()
const messages = ref([])

function scrollToBottom() {
    nextTick(() => {
        if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    })
}

async function loadMessages() {
    try {
        const data = await get(`meetings/${meetingId.value}/messages`)
        const loaded = (data || []).map(m => ({
            id: m.id,
            type: m.message_type || m.type,
            content: m.content,
            file_name: m.file_name,
            metadata: m.metadata,
            sender: m.sender,
            userName: m.user_name,
            status: m.metadata?.status || 'pending',
            sessionId: m.metadata?.session_id,
            totalQuestions: m.metadata?.total_questions || 0,
            timePerQuestion: m.metadata?.time_per_question || 30,
            currentQuestionIndex: m.metadata?.current_question_index || 0,
        }))
        for (const msg of loaded) {
            if (msg.type === 'kahoot' && msg.sessionId) {
                try {
                    const sessionData = await get(`kahoot/${msg.sessionId}/current`)
                    if (sessionData.finished) { msg.status = 'finished' }
                    else if (sessionData.question) { msg.status = 'playing'; msg.totalQuestions = sessionData.total_questions || msg.totalQuestions }
                } catch { /* */ }
            }
        }
        messages.value = loaded
        scrollToBottom()
    } catch { /* */ }
}

async function saveMessage(type, content, extra = {}) {
    try {
        const payload = { type: type, content: content || '', file_name: extra.fileName || null, metadata: extra.metadata || null }
        const result = await post(`meetings/${meetingId.value}/messages`, payload)
        const msg = {
            id: result.id, type: result.message_type || result.type || type, content: result.content || content,
            file_name: result.file_name || extra.fileName || null, metadata: result.metadata || extra.metadata || null,
            sender: result.sender, userName: result.user_name || authUser.value?.name || 'Yo',
            status: extra.metadata?.status || 'pending', sessionId: extra.metadata?.session_id,
            totalQuestions: extra.metadata?.total_questions || 0, timePerQuestion: extra.metadata?.time_per_question || 30,
            currentQuestionIndex: extra.metadata?.current_question_index || 0,
        }
        messages.value.push(msg)
        scrollToBottom()
        emitSocket('chat:message', `meeting-${meetingId.value}`, msg)
        return msg
    } catch (err) { console.error('Error saving message:', err) }
}

async function handleSendMessage(msgObj) {
    if (msgObj.type === 'image' || msgObj.type === 'pdf') {
        try {
            const response = await fetch(msgObj.content)
            const blob = await response.blob()
            const formData = new FormData()
            formData.append('file', blob, msgObj.fileName || 'file')
            const uploadResult = await post('upload', formData)
            await saveMessage(msgObj.type, uploadResult.url, { fileName: msgObj.fileName })
        } catch (err) {
            errorMsg.value = 'Error al subir archivo: ' + (err.message || 'desconocido')
        }
    } else {
        await saveMessage(msgObj.type, msgObj.content, { fileName: msgObj.fileName })
    }
}

// Kahoot Creator
const showKahootCreator = ref(false)
const pdfFile = ref(null)
const numQuestions = ref(10)
const timePerQuestion = ref(30)
const questions = ref([])
const title = ref('')
const generating = ref(false)
const errorMsg = ref('')

function toggleKahootCreator() { showKahootCreator.value = !showKahootCreator.value; errorMsg.value = '' }
function handleFileChange(e) {
    const file = e.target.files[0]; if (!file) return
    if (file.type !== 'application/pdf') { errorMsg.value = 'Solo PDF'; return }
    pdfFile.value = file; errorMsg.value = ''
}

async function generateQuestions() {
    if (!pdfFile.value) { errorMsg.value = 'Selecciona un PDF'; return }
    generating.value = true; errorMsg.value = ''
    try {
        const reader = new FileReader()
        const base64 = await new Promise((resolve, reject) => {
            reader.onload = () => resolve(reader.result.split(',')[1])
            reader.onerror = reject
            reader.readAsDataURL(pdfFile.value)
        })
        const response = await post('events/generate-kahoot', { pdf_base64: base64, num_questions: numQuestions.value })
        questions.value = response.questions.map(q => ({ ...q, answers: q.answers.slice(0, 4) }))
    } catch (err) { errorMsg.value = err.message || 'Error al generar preguntas' }
    finally { generating.value = false }
}

function removeQuestion(i) { questions.value.splice(i, 1) }
function markCorrect(qi, ai) { questions.value[qi].correct = ai }
function addQuestion() { questions.value.push({ question: '', answers: ['', '', '', ''], correct: 0 }) }

async function sendKahoot() {
    if (questions.value.length === 0) return
    try {
        const session = await post(`meetings/${meetingId.value}/kahoot`, {
            title: title.value || 'Kahoot', questions: questions.value, time_per_question: timePerQuestion.value,
        })
        await saveMessage('kahoot', '', { metadata: { session_id: session.id, status: 'pending', total_questions: questions.value.length, time_per_question: timePerQuestion.value } })
        showKahootCreator.value = false; questions.value = []; pdfFile.value = null
    } catch (err) { errorMsg.value = err.message }
}

const kahootAnsweredCount = ref({})

async function startKahoot(sessionId) {
    try {
        const data = await post(`kahoot/${sessionId}/start`)
        kahootAnsweredCount.value[sessionId] = 0
        const msg = messages.value.find(m => m.sessionId === sessionId)
        if (msg) { msg.status = 'playing'; msg.totalQuestions = data.total_questions }
        emitSocket('kahoot:started', `meeting-${meetingId.value}`, { sessionId })
    } catch (err) { errorMsg.value = err.message }
}

function handleKahootAnswer(sessionId) {
    emitSocket('kahoot:player-answered', `meeting-${meetingId.value}`, { sessionId, userId: authUser.value?.id })
}

async function nextQuestion(sessionId) {
    try {
        const data = await post(`kahoot/${sessionId}/next`)
        const msg = messages.value.find(m => m.sessionId === sessionId)
        const roomId = `meeting-${meetingId.value}`
        if (data.finished) { if (msg) msg.status = 'finished'; emitSocket('kahoot:ended', roomId, { sessionId }) }
        else { if (msg) msg.currentQuestionIndex = data.question.index; emitSocket('kahoot:next-question', roomId, { sessionId, questionIndex: data.question.index }) }
    } catch (err) { console.error(err) }
}

function setupSocket() {
    const roomId = `meeting-${meetingId.value}`
    const userData = authUser.value ? { userId: authUser.value.id, username: authUser.value.name, role: authUser.value.role } : null
    connectSocket(roomId, userData)

    onSocket('chat:message', (data) => {
        if (data.sender !== authUser.value?.id && !messages.value.find(m => m.id === data.id)) {
            // Normalize message type for frontend
            const normalizedData = { ...data, type: data.message_type || data.type };
            messages.value.push(normalizedData); scrollToBottom()
        }
    })
    onSocket('kahoot:started', (data) => {
        const msg = messages.value.find(m => m.sessionId === data.sessionId)
        if (msg) { msg.status = 'playing' }
    })
    onSocket('kahoot:next-question', (data) => {
        const msg = messages.value.find(m => m.sessionId === data.sessionId)
        if (msg) { msg.currentQuestionIndex = data.questionIndex }
    })
    onSocket('kahoot:ended', (data) => {
        const msg = messages.value.find(m => m.sessionId === data.sessionId)
        if (msg) { msg.status = 'finished' }
    })
    onSocket('kahoot:player-answered', (data) => {
        if (data.sessionId) { kahootAnsweredCount.value[data.sessionId] = (kahootAnsweredCount.value[data.sessionId] || 0) + 1 }
    })
}

onMounted(async () => { await loadMessages(); setupSocket() })
onUnmounted(() => disconnectSocket())
</script>

<template>
    <div class="flex flex-col w-full h-full overflow-hidden">
        <div ref="chatContainer" class="flex flex-col flex-1 min-h-0 pb-4 pr-2 overflow-y-auto scroll-smooth">
            <div class="flex flex-col justify-end min-h-max space-y-4 pt-4">
                <div v-if="errorMsg" class="bg-red-500/20 border border-red-500 text-red-200 p-3 rounded-lg text-sm">
                    {{ errorMsg }}
                    <button @click="errorMsg = ''" class="ml-2 text-red-300 hover:text-red-100 cursor-pointer">✕</button>
                </div>

                <template v-for="(msg, index) in messages" :key="msg.id || index">
                    <div v-if="msg.type === 'kahoot'" class="self-start max-w-3/4 min-w-0 w-full">
                        <p class="text-xs text-gray-400 mb-1 ml-2">{{ msg.userName || 'Profesor' }}</p>
                        <div class="bg-gradient-to-br from-[#1f5252] to-[#0f2828] rounded-2xl p-5 text-white shadow-md border border-[#2a6a6a]">
                            <KahootMessage
                                :sessionId="msg.sessionId" :status="msg.status" :totalQuestions="msg.totalQuestions"
                                :timePerQuestion="msg.timePerQuestion" :questionIndex="msg.currentQuestionIndex || 0"
                                :isTeacher="canCreateKahoot" :playersAnswered="kahootAnsweredCount[msg.sessionId] || 0"
                                @start="startKahoot(msg.sessionId)" @next="nextQuestion(msg.sessionId)"
                                @answered="handleKahootAnswer(msg.sessionId)" />
                        </div>
                    </div>

                    <div v-else
                        :class="[Number(msg.sender) === Number(authUser?.id) ? 'self-end flex flex-col items-end' : 'self-start', 'max-w-3/5 min-w-0']">
                        <p class="text-xs text-gray-400 mb-1" :class="Number(msg.sender) === Number(authUser?.id) ? 'mr-2' : 'ml-2'">
                            {{ msg.userName || (Number(msg.sender) === Number(authUser?.id) ? 'Yo' : 'Otro') }}
                        </p>
                        <div :class="[Number(msg.sender) === Number(authUser?.id) ? 'bg-[#2a4a5a]' : 'bg-[#1e2e38]', 'rounded-2xl p-4 text-white shadow-md']">
                            <p v-if="msg.type === 'text'" class="break-all whitespace-pre-wrap">{{ msg.content }}</p>
                            <img v-else-if="msg.type === 'image'" :src="msg.content" class="w-full max-w-80 max-h-64 object-contain rounded-lg cursor-pointer bg-black/20" @click="window.open(msg.content, '_blank')" />
                            <a v-else-if="msg.type === 'pdf'" :href="msg.content" target="_blank" class="flex items-center gap-3 bg-[#152027] p-3 rounded-xl no-underline text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ff4444" stroke-width="2"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/></svg>
                                <span class="text-sm truncate">{{ msg.file_name || 'PDF' }}</span>
                            </a>

                            <div v-else-if="msg.type === 'call'" class="flex flex-col gap-3 p-1">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 10l4.553 -2.276a1 1 0 0 1 1.447 .894v6.764a1 1 0 0 1 -1.447 .894l-4.553 -2.276v-4z"/><path d="M3 6m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold">{{ Number(msg.sender) === Number(authUser?.id) ? 'Has iniciado una llamada grupal' : 'Sesión de video disponible' }}</p>
                                        <p class="text-[10px] opacity-60">¿Deseas entrar a la reunión de video?</p>
                                    </div>
                                </div>
                                <button @click="emit('joinCall', msg.content)" class="w-full py-3 px-6 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5l10 -10"/></svg>
                                    Unirse a la sesión
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div v-if="showKahootCreator" class="bg-[#1e2e38] rounded-2xl p-4 mb-3 border border-[#2a4a5a] shrink-0 max-h-72 overflow-y-auto custom-scrollbar">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-white text-sm">Crear Kahoot</h3>
                <button @click="showKahootCreator = false" class="text-white/50 hover:text-white cursor-pointer"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg></button>
            </div>

            <template v-if="questions.length > 0">
                <p class="text-xs text-white/60 mb-2">{{ questions.length }} preguntas</p>
                <div class="space-y-1 mb-2">
                    <div v-for="(q, qi) in questions" :key="qi" class="bg-[#152027] rounded-lg p-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-[#1f5252]">P{{ qi + 1 }}</span>
                            <button @click="removeQuestion(qi)" class="text-red-400 text-xs cursor-pointer">✕</button>
                        </div>
                        <input type="text" v-model="q.question" class="w-full bg-transparent text-white text-xs outline-hidden mb-1" />
                        <div class="grid grid-cols-2 gap-1">
                            <div v-for="(a, ai) in q.answers" :key="ai" @click="markCorrect(qi, ai)"
                                class="flex items-center gap-1 p-1 rounded text-xs cursor-pointer"
                                :class="q.correct === ai ? 'bg-[#1f5252]/40 border border-[#1f5252]' : 'bg-white/5'">
                                <span class="w-2.5 h-2.5 rounded-full border flex items-center justify-center shrink-0" :class="q.correct === ai ? 'bg-[#1f5252]' : ''"><span v-if="q.correct === ai" class="w-1 h-1 rounded-full bg-white"></span></span>
                                <input type="text" v-model="q.answers[ai]" @click.stop class="w-full bg-transparent text-white outline-hidden text-xs" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <input type="text" v-model="title" placeholder="Título" class="flex-1 bg-[#152027] border border-white/10 rounded-lg px-2 py-1.5 text-white text-xs outline-hidden" />
                    <button @click="sendKahoot" class="bg-[#1f5252] hover:bg-[#2a6a6a] text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors cursor-pointer">Enviar</button>
                </div>
            </template>

            <template v-else>
                <div class="border-2 border-dashed border-white/20 rounded-xl p-3 text-center mb-2">
                    <input type="file" accept="application/pdf" @change="handleFileChange" class="hidden" id="pdfUpload" />
                    <label for="pdfUpload" class="cursor-pointer flex flex-col items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-white/40"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M12 11v6"/><path d="M9 14l3 3l3 -3"/></svg>
                        <span class="text-xs text-white/60">{{ pdfFile ? pdfFile.name : 'PDF del temario' }}</span>
                    </label>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <input type="number" v-model.number="numQuestions" min="1" max="30" placeholder="Nº preguntas" class="w-20 bg-[#152027] border border-white/10 rounded-lg px-2 py-1 text-white text-xs outline-hidden" />
                    <input type="number" v-model.number="timePerQuestion" min="5" max="120" placeholder="Tiempo (s)" class="w-20 bg-[#152027] border border-white/10 rounded-lg px-2 py-1 text-white text-xs outline-hidden" />
                </div>
                <button @click="generateQuestions" :disabled="generating || !pdfFile" class="w-full bg-[#1f5252] hover:bg-[#2a6a6a] disabled:opacity-50 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition-colors cursor-pointer">{{ generating ? 'Generando...' : 'Generar con IA' }}</button>
            </template>
        </div>

        <div class="pt-2 shrink-0">
            <div class="flex items-center gap-2 mb-2">
                <button v-if="canCreateKahoot" @click="toggleKahootCreator"
                    class="flex items-center gap-1 text-xs px-3 py-1.5 rounded-full transition-colors cursor-pointer"
                    :class="showKahootCreator ? 'bg-[#1f5252] text-white' : 'bg-white/10 text-white/70 hover:bg-white/20'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4"/><path d="M13 7l4 4l6 -6"/></svg>
                    {{ showKahootCreator ? 'Cerrar' : 'Kahoot' }}
                </button>
            </div>
            <TextChatBar @sendMessage="handleSendMessage" />
        </div>
    </div>
</template>

<style scoped>
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
.custom-scrollbar::-webkit-scrollbar { width: 4px; display: block; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
</style>
