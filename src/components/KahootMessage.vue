<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useApi } from '@/composables/useApi'

const { get, post } = useApi()

const props = defineProps({
    sessionId: { type: [String, Number], required: true },
    status: { type: String, default: 'pending' },
    totalQuestions: { type: Number, default: 0 },
    timePerQuestion: { type: Number, default: 30 },
    questionIndex: { type: Number, default: 0 },
    isTeacher: { type: Boolean, default: false },
    playersAnswered: { type: Number, default: 0 },
})

const emit = defineEmits(['start', 'next', 'answered'])

const currentQuestion = ref(null)
const selectedAnswer = ref(-1)
const answered = ref(false)
const answerResult = ref(null)
const userPrevAnswer = ref(null)
const timeLeft = ref(props.timePerQuestion)
const timerInterval = ref(null)
const showFinalResults = ref(false)
const leaderboard = ref([])
const loading = ref(false)
const error = ref('')

const timerPercent = computed(() => (timeLeft.value / props.timePerQuestion) * 100)
const timerColor = computed(() => {
    if (timeLeft.value > props.timePerQuestion * 0.5) return 'bg-green-500'
    if (timeLeft.value > props.timePerQuestion * 0.25) return 'bg-yellow-500'
    return 'bg-red-500'
})

function startTimer() {
    stopTimer()
    timeLeft.value = props.timePerQuestion
    timerInterval.value = setInterval(() => {
        timeLeft.value--
        if (timeLeft.value <= 0) {
            stopTimer()
            if (props.isTeacher) {
                handleNext()
            } else if (!answered.value) {
                submitAnswer(-1)
            }
        }
    }, 1000)
}

function stopTimer() {
    if (timerInterval.value) { clearInterval(timerInterval.value); timerInterval.value = null }
}

async function loadQuestion() {
    if (!props.sessionId) return
    loading.value = true
    error.value = ''
    try {
        const data = await get(`kahoot/${props.sessionId}/current`)
        if (data.finished) {
            currentQuestion.value = null
            return
        }
        if (data.question) {
            currentQuestion.value = {
                index: data.question.index,
                question: data.question.question,
                answers: data.question.answers,
                correct: data.question.correct,
                totalQuestions: data.total_questions || props.totalQuestions,
            }
            // Check if user already answered this question
            if (data.user_answer) {
                userPrevAnswer.value = data.user_answer
                selectedAnswer.value = data.user_answer.selected_answer
                answered.value = true
                answerResult.value = {
                    is_correct: data.user_answer.is_correct,
                    correct_answer: data.question.correct,
                    score: data.user_answer.score,
                    total_score: data.user_answer.total_score,
                }
            } else {
                userPrevAnswer.value = null
                selectedAnswer.value = -1
                answered.value = false
                answerResult.value = null
                startTimer() // Start timer for both teacher and students
            }
        }
    } catch {
        currentQuestion.value = null
    } finally { loading.value = false }
}

async function selectAnswer(answerIndex) {
    if (answered.value || props.isTeacher || userPrevAnswer.value) return
    selectedAnswer.value = answerIndex
    await submitAnswer(answerIndex)
}

async function submitAnswer(answerIndex) {
    answered.value = true
    stopTimer()
    try {
        answerResult.value = await post(`kahoot/${props.sessionId}/answer`, {
            question_index: currentQuestion.value?.index ?? 0,
            selected_answer: answerIndex,
            time_remaining: timeLeft.value,
        })
        emit('answered')
    } catch (err) { error.value = err.message; answered.value = false }
}

async function showResults() {
    try {
        const data = await get(`kahoot/${props.sessionId}/results`)
        leaderboard.value = data.leaderboard || []
        showFinalResults.value = true
    } catch { leaderboard.value = [] }
}

function handleNext() {
    stopTimer()
    emit('next')
}

// Reload when status, sessionId, or questionIndex changes
watch(() => props.status, (val) => {
    if (val === 'playing') loadQuestion()
    if (val === 'finished') { stopTimer(); showFinalResults.value = false }
})

watch(() => props.sessionId, () => {
    if (props.status === 'playing') loadQuestion()
})

watch(() => props.questionIndex, () => {
    if (props.status === 'playing') loadQuestion()
})

onMounted(() => {
    if (props.status === 'playing') loadQuestion()
})
onUnmounted(() => stopTimer())
</script>

<template>
    <div>
        <!-- Pending -->
        <template v-if="status === 'pending'">
            <div class="flex items-center gap-3 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2"><path d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4"/><path d="M13 7l4 4l6 -6"/></svg>
                <div>
                    <p class="font-bold">Kahoot</p>
                    <p class="text-xs text-white/60">{{ totalQuestions }} preguntas - Esperando inicio</p>
                </div>
            </div>
            <button v-if="isTeacher" @click="$emit('start')"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-xl text-sm transition-colors cursor-pointer">
                Iniciar Kahoot
            </button>
            <p v-else class="text-xs text-white/50 text-center">Esperando a que el profesor inicie...</p>
        </template>

        <!-- Playing -->
        <template v-if="status === 'playing'">
            <template v-if="currentQuestion">
                <div class="w-full bg-white/10 rounded-full h-1.5 mb-2 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-1000 ease-linear" :class="timerColor" :style="{ width: timerPercent + '%' }"></div>
                </div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-green-300">Pregunta {{ currentQuestion.index + 1 }}/{{ currentQuestion.totalQuestions }}</span>
                    <span v-if="!answered" class="text-xs font-bold" :class="timeLeft <= 5 ? 'text-red-400' : 'text-white/50'">{{ timeLeft }}s</span>
                    <span v-else class="text-xs text-white/50">Respondida</span>
                </div>
                <p class="text-base font-bold mb-3">{{ currentQuestion.question }}</p>

                <div class="grid grid-cols-2 gap-2">
                    <button v-for="(answer, ai) in currentQuestion.answers" :key="ai"
                        @click="selectAnswer(ai)"
                        :disabled="answered || userPrevAnswer !== null || isTeacher"
                        class="rounded-xl p-3 text-white font-medium text-xs text-center transition-all duration-300 cursor-pointer disabled:cursor-default"
                        :class="[
                            answered || userPrevAnswer ? (
                                answerResult?.correct_answer === ai ? 'bg-green-600 scale-95' :
                                selectedAnswer === ai && !answerResult?.is_correct ? 'bg-red-600 scale-95' :
                                'bg-white/5'
                            ) : selectedAnswer === ai ? 'bg-[#1f5252] scale-95 ring-2 ring-[#1f5252]' : 'bg-[#1e2e38] hover:bg-[#2a4a5a]',
                            (answered || userPrevAnswer) ? 'opacity-80' : ''
                        ]">
                        <span class="block text-[10px] text-white/50 mb-0.5">{{ ['A','B','C','D'][ai] }}</span>
                        <span class="block leading-tight">{{ answer }}</span>
                    </button>
                </div>

                <div v-if="answered && answerResult" class="mt-2 p-2 rounded-xl text-center text-xs"
                    :class="answerResult.is_correct ? 'bg-green-500/20 border border-green-500' : 'bg-red-500/20 border border-red-500'">
                    <p class="font-bold" :class="answerResult.is_correct ? 'text-green-300' : 'text-red-300'">{{ answerResult.is_correct ? 'Correcto' : 'Incorrecto' }}</p>
                    <p class="text-white/70">+{{ answerResult.score }} pts</p>
                </div>

                <div v-if="userPrevAnswer && !answered" class="mt-2 p-2 rounded-xl text-center text-xs bg-yellow-500/20 border border-yellow-500">
                    <p class="text-yellow-300">Ya respondiste esta pregunta</p>
                </div>

                <div v-if="isTeacher" class="mt-2 flex items-center justify-between">
                    <span class="text-xs text-white/50">{{ playersAnswered }} respondieron</span>
                    <button @click="handleNext"
                        class="bg-[#1f5252] hover:bg-[#2a6a6a] text-white font-bold py-2 px-4 rounded-xl text-sm transition-colors cursor-pointer">
                        {{ timeLeft <= 0 ? 'Siguiente' : 'Saltar (' + timeLeft + 's)' }}
                    </button>
                </div>
            </template>
            <div v-else class="text-center text-white/50 py-4">
                <svg class="animate-spin h-5 w-5 mx-auto mb-1" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span class="text-xs">Cargando pregunta...</span>
            </div>
            <div v-if="error" class="mt-2 text-xs text-red-400">{{ error }}</div>
        </template>

        <!-- Finished -->
        <template v-if="status === 'finished'">
            <div v-if="!showFinalResults" class="text-center">
                <p class="text-base font-bold text-green-300 mb-2">Kahoot finalizado!</p>
                <button @click="showResults" class="bg-[#1f5252] hover:bg-[#2a6a6a] text-white font-bold py-2 px-5 rounded-xl text-sm transition-colors cursor-pointer">Ver resultados</button>
            </div>
            <div v-else>
                <p class="text-sm font-bold text-center mb-2">Resultados</p>
                <div class="space-y-1">
                    <div v-for="(entry, i) in leaderboard" :key="entry.user_id"
                        class="flex items-center justify-between p-1.5 rounded-lg text-xs bg-white/5">
                        <div class="flex items-center gap-1.5">
                            <span class="w-5 text-center font-bold" :class="{ 'text-yellow-400': i === 0, 'text-gray-400': i === 1, 'text-orange-400': i === 2 }">{{ i + 1 }}</span>
                            <span>{{ entry.username }}</span>
                        </div>
                        <span class="font-bold">{{ entry.total_score }}</span>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
