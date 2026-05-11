<script setup>
import { ref, computed } from 'vue'
import { useTranslations } from '@/composables/useTranslations'
import { user as authUser } from '@/stores/auth'
import { useApi } from '@/composables/useApi'

const { t } = useTranslations()
const { loading, post } = useApi()

const emit = defineEmits(['kahootCreated', 'close'])

const props = defineProps({
    meetingId: { type: [String, Number], required: true },
})

const step = ref('upload')
const pdfFile = ref(null)
const numQuestions = ref(10)
const timePerQuestion = ref(30)
const questions = ref([])
const title = ref('')
const generating = ref(false)
const errorMsg = ref('')
const editingQuestionIndex = ref(-1)

const questionsWithCorrect = computed(() => {
    return questions.value.map(q => {
        const correctIndex = q.answers.findIndex((a, i) => i === q.correct)
        return { ...q, correctLabel: correctIndex >= 0 ? q.answers[correctIndex] : '' }
    })
})

function handleFileChange(e) {
    const file = e.target.files[0]
    if (!file) return
    if (file.type !== 'application/pdf') {
        errorMsg.value = 'Solo se permiten archivos PDF'
        return
    }
    pdfFile.value = file
    errorMsg.value = ''
}

async function generateQuestions() {
    if (!pdfFile.value) {
        errorMsg.value = t.value.kahoot.selectPdfFirst
        return
    }

    generating.value = true
    errorMsg.value = ''

    try {
        const reader = new FileReader()
        const base64 = await new Promise((resolve, reject) => {
            reader.onload = () => {
                const result = reader.result.split(',')[1]
                resolve(result)
            }
            reader.onerror = reject
            reader.readAsDataURL(pdfFile.value)
        })

        const response = await post('events/generate-kahoot', {
            pdf_base64: base64,
            num_questions: numQuestions.value,
        })

        questions.value = response.questions.map(q => ({
            ...q,
            answers: q.answers.slice(0, 4),
        }))
        step.value = 'review'
    } catch (err) {
        errorMsg.value = err.message || t.value.kahoot.errorGenerating
    } finally {
        generating.value = false
    }
}

function addQuestion() {
    questions.value.push({
        question: '',
        answers: ['', '', '', ''],
        correct: 0,
    })
}

function removeQuestion(index) {
    questions.value.splice(index, 1)
}

function markCorrect(index, answerIndex) {
    questions.value[index].correct = answerIndex
}

async function createKahoot() {
    if (questions.value.length === 0) return

    try {
        const response = await post(`meetings/${props.meetingId}/kahoot`, {
            title: title.value || 'Kahoot',
            questions: questions.value,
            time_per_question: timePerQuestion.value,
        })
        emit('kahootCreated', response)
    } catch (err) {
        errorMsg.value = err.message
    }
}

function startKahoot() {
    createKahoot()
}
</script>

<template>
    <div class="flex flex-col h-full text-white overflow-hidden">
        <div class="flex items-center justify-between shrink-0 mb-4">
            <h2 class="text-2xl font-bold">{{ t.kahoot.createTitle }}</h2>
            <button @click="$emit('close')"
                class="text-white/60 hover:text-white transition-colors cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M18 6l-12 12" />
                    <path d="M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div v-if="errorMsg" class="bg-red-500/20 border border-red-500 text-red-200 p-3 rounded-lg mb-4 text-sm">
            {{ errorMsg }}
        </div>

        <!-- Upload Step -->
        <div v-if="step === 'upload'" class="flex-1 flex flex-col overflow-y-auto pr-2 custom-scrollbar">
            <div
                class="border-2 border-dashed border-white/20 rounded-xl p-8 text-center hover:border-[#1f5252] transition-colors mb-4">
                <input type="file" accept="application/pdf" @change="handleFileChange" class="hidden" id="pdfUpload" />
                <label for="pdfUpload" class="cursor-pointer flex flex-col items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" class="text-white/40">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                        <path d="M12 11v6" />
                        <path d="M9 14l3 3l3 -3" />
                    </svg>
                    <p class="text-white/60">
                        {{ pdfFile ? pdfFile.name : t.kahoot.selectPdf }}
                    </p>
                    <p v-if="!pdfFile" class="text-xs text-white/40">{{ t.kahoot.uploadPdfHint }}</p>
                </label>
            </div>

            <div class="mb-4">
                <label class="block text-sm text-white/70 mb-1">{{ t.kahoot.numQuestions }}</label>
                <input type="number" v-model.number="numQuestions" min="1" max="30"
                    class="w-full bg-[#1e2e38] border border-white/10 rounded-lg p-2 text-white outline-hidden focus:border-[#1f5252]" />
            </div>

            <div class="mb-4">
                <label class="block text-sm text-white/70 mb-1">{{ t.kahoot.timePerQuestion }}</label>
                <input type="number" v-model.number="timePerQuestion" min="5" max="120"
                    class="w-full bg-[#1e2e38] border border-white/10 rounded-lg p-2 text-white outline-hidden focus:border-[#1f5252]" />
            </div>

            <button @click="generateQuestions" :disabled="generating || !pdfFile"
                class="w-full bg-[#1f5252] hover:bg-[#2a6a6a] disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-xl transition-colors cursor-pointer">
                <span v-if="generating" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                            fill="none" />
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    {{ t.kahoot.generating }}
                </span>
                <span v-else>{{ t.kahoot.generateQuestions }}</span>
            </button>
        </div>

        <!-- Review Step -->
        <div v-if="step === 'review'" class="flex-1 flex flex-col overflow-hidden">
            <p class="text-sm text-white/60 mb-2">
                {{ t.kahoot.questionsGenerated.replace('{count}', questions.length) }}
            </p>

            <div class="flex items-center gap-2 mb-4">
                <input type="text" v-model="title" placeholder="Título del Kahoot"
                    class="flex-1 bg-[#1e2e38] border border-white/10 rounded-lg p-2 text-white outline-hidden focus:border-[#1f5252]" />
                <button @click="addQuestion"
                    class="bg-[#1f5252] hover:bg-[#2a6a6a] text-white px-3 py-2 rounded-lg text-sm transition-colors cursor-pointer">
                    + Añadir
                </button>
            </div>

            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar space-y-4">
                <div v-for="(q, qi) in questions" :key="qi"
                    class="bg-[#1e2e38] rounded-xl p-4 border border-white/5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold text-[#1f5252]">{{ t.kahoot.questionLabel.replace('{index}', qi + 1) }}</span>
                        <button @click="removeQuestion(qi)"
                            class="text-red-400 hover:text-red-300 text-sm cursor-pointer">
                            Eliminar
                        </button>
                    </div>

                    <input type="text" v-model="q.question" :placeholder="'Pregunta ' + (qi + 1)"
                        class="w-full bg-[#152027] border border-white/10 rounded-lg p-2 mb-3 text-white text-sm outline-hidden focus:border-[#1f5252]" />

                    <div class="grid grid-cols-2 gap-2">
                        <div v-for="(a, ai) in q.answers" :key="ai"
                            @click="markCorrect(qi, ai)"
                            class="flex items-center gap-2 p-2 rounded-lg bg-[#152027] border cursor-pointer transition-colors"
                            :class="q.correct === ai ? 'border-[#1f5252] bg-[#1f5252]/20' : 'border-white/5 hover:border-white/20'">
                            <div
                                class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                                :class="q.correct === ai ? 'border-[#1f5252] bg-[#1f5252]' : 'border-white/30'">
                                <div v-if="q.correct === ai" class="w-2 h-2 rounded-full bg-white"></div>
                            </div>
                            <input type="text" v-model="q.answers[ai]"
                                :placeholder="t.kahoot.answerLabel.replace('{index}', ai + 1)"
                                @click.stop
                                class="w-full bg-transparent text-white text-sm outline-hidden placeholder-white/30" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 mt-4 shrink-0">
                <button @click="step = 'upload'"
                    class="flex-1 bg-white/10 hover:bg-white/20 text-white py-3 px-6 rounded-xl transition-colors cursor-pointer">
                    Volver
                </button>
                <button @click="startKahoot" :disabled="questions.length === 0"
                    class="flex-1 bg-[#1f5252] hover:bg-[#2a6a6a] disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-xl transition-colors cursor-pointer">
                    {{ t.kahoot.startKahoot }} ({{ questions.length }})
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
    display: block;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
