<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useTranslations } from '@/composables/useTranslations'
import { useApi } from '@/composables/useApi'

const { t } = useTranslations()
const { post } = useApi()

const emit = defineEmits(['answered', 'error'])

const props = defineProps({
    sessionId: { type: [String, Number], required: true },
    question: { type: Object, required: true },
    timePerQuestion: { type: Number, default: 30 },
    totalQuestions: { type: Number, default: 0 },
    isTeacher: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
})

const selectedAnswer = ref(-1)
const answered = ref(false)
const answerResult = ref(null)
const timeLeft = ref(props.timePerQuestion)
const timerInterval = ref(null)

function resetAndStartTimer() {
    stopTimer()
    selectedAnswer.value = -1
    answered.value = false
    answerResult.value = null
    if (!props.isTeacher) {
        timeLeft.value = props.timePerQuestion
        startTimer()
    }
}

onMounted(() => {
    resetAndStartTimer()
})

watch(() => props.question?.index, () => {
    resetAndStartTimer()
})

const progressPercent = computed(() => {
    return (timeLeft.value / props.timePerQuestion) * 100
})

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
            if (!answered.value && !props.isTeacher) {
                submitAnswer(-1, 0)
            }
        }
    }, 1000)
}

function stopTimer() {
    if (timerInterval.value) {
        clearInterval(timerInterval.value)
        timerInterval.value = null
    }
}

async function selectAnswer(answerIndex) {
    if (answered.value || props.disabled || props.isTeacher) return
    selectedAnswer.value = answerIndex
    await submitAnswer(answerIndex, timeLeft.value)
}

async function submitAnswer(answerIndex, timeRemaining) {
    answered.value = true
    stopTimer()

    try {
        const result = await post(`kahoot/${props.sessionId}/answer`, {
            question_index: props.question.index,
            selected_answer: answerIndex,
            time_remaining: timeRemaining,
        })
        answerResult.value = result
        emit('answered', result)
    } catch (err) {
        emit('error', err.message)
        answered.value = false
    }
}

onUnmounted(() => {
    stopTimer()
})
</script>

<template>
    <div v-if="question" class="flex flex-col h-full">
        <!-- Timer Bar -->
        <div class="w-full bg-[#1e2e38] rounded-full h-3 mb-4 overflow-hidden shrink-0">
            <div class="h-full rounded-full transition-all duration-1000 ease-linear"
                :class="timerColor"
                :style="{ width: progressPercent + '%' }">
            </div>
        </div>

        <!-- Question Header -->
        <div class="flex items-center justify-between mb-4 shrink-0">
            <span class="text-sm text-white/60">
                {{ t.kahoot.question }} {{ question.index + 1 }} {{ t.kahoot.of }} {{ totalQuestions || '?' }}
            </span>
            <span class="text-sm font-bold"
                :class="timeLeft <= 5 ? 'text-red-400' : 'text-white/60'">
                {{ timeLeft }}s
            </span>
        </div>

        <!-- Question Text -->
        <div class="bg-[#1e2e38] rounded-xl p-6 mb-6 text-center shrink-0">
            <p class="text-xl lg:text-2xl font-bold">{{ question.question }}</p>
        </div>

        <!-- Answer Options -->
        <div class="grid grid-cols-2 gap-3 flex-1">
            <button v-for="(answer, ai) in question.answers" :key="ai"
                @click="selectAnswer(ai)"
                :disabled="answered || disabled || isTeacher"
                class="relative rounded-xl p-4 text-white font-medium text-center transition-all duration-300 cursor-pointer disabled:cursor-default"
                :class="[
                    answered
                        ? answerResult && answerResult.correct_answer === ai
                            ? 'bg-green-600 scale-95'
                            : selectedAnswer === ai && answerResult && !answerResult.is_correct
                                ? 'bg-red-600 scale-95'
                                : 'bg-white/5'
                        : selectedAnswer === ai
                            ? 'bg-[#1f5252] scale-95 ring-2 ring-[#1f5252]'
                            : 'bg-[#1e2e38] hover:bg-[#2a4a5a] hover:scale-[1.02]',
                    (answered || disabled || isTeacher) ? 'opacity-70' : ''
                ]">
                <span class="block text-xs text-white/50 mb-1">
                    {{ ['A', 'B', 'C', 'D'][ai] }}
                </span>
                <span class="block">{{ answer }}</span>

                <div v-if="answered && answerResult"
                    class="absolute -top-2 -right-2 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                    :class="answerResult.correct_answer === ai ? 'bg-green-500' : (selectedAnswer === ai ? 'bg-red-500' : 'bg-white/20')">
                    <template v-if="answerResult.correct_answer === ai">&#10003;</template>
                    <template v-else-if="selectedAnswer === ai">&#10007;</template>
                </div>
            </button>
        </div>

        <!-- Result Feedback -->
        <div v-if="answered && answerResult" class="mt-4 p-4 rounded-xl text-center shrink-0"
            :class="answerResult.is_correct ? 'bg-green-500/20 border border-green-500' : 'bg-red-500/20 border border-red-500'">
            <p class="text-xl font-bold" :class="answerResult.is_correct ? 'text-green-300' : 'text-red-300'">
                {{ answerResult.is_correct ? t.kahoot.correct : t.kahoot.incorrect }}
            </p>
            <p class="text-sm text-white/70 mt-1">
                {{ t.kahoot.yourScore }}: +{{ answerResult.score }}
            </p>
            <p class="text-sm text-white/50 mt-1">
                {{ t.kahoot.totalScore }}: {{ answerResult.total_score }}
            </p>
        </div>

        <!-- Teacher Status -->
        <div v-if="isTeacher && answered" class="mt-4 p-3 bg-[#1e2e38] rounded-xl text-center shrink-0">
            <p class="text-sm text-white/60">{{ t.kahoot.waitingForQuestion }}</p>
        </div>
    </div>
</template>
