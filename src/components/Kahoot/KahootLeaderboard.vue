<script setup>
import { ref, computed, watch } from 'vue'
import { useTranslations } from '@/composables/useTranslations'
import { useApi } from '@/composables/useApi'
import { user as authUser } from '@/stores/auth'

const { t } = useTranslations()
const { get } = useApi()

const props = defineProps({
    sessionId: { type: [String, Number], required: true },
    isFinal: { type: Boolean, default: false },
    autoRefresh: { type: Boolean, default: false },
})

const emit = defineEmits(['next', 'end'])

const leaderboard = ref([])
const totalQuestions = ref(0)
const loading = ref(false)
const refreshInterval = ref(null)

const myEntry = ref(null)
const podium = computed(() => leaderboard.value.slice(0, 3))
const rest = computed(() => leaderboard.value.slice(3))

async function fetchLeaderboard() {
    loading.value = true
    try {
        const data = await get(`kahoot/${props.sessionId}/leaderboard`)
        leaderboard.value = data.leaderboard || []
        totalQuestions.value = data.total_questions || 0

        myEntry.value = leaderboard.value.find(e => e.user_id === authUser.value?.id) || null
    } catch (err) {
        console.error('Error fetching leaderboard:', err)
    } finally {
        loading.value = false
    }
}

function startAutoRefresh() {
    stopAutoRefresh()
    fetchLeaderboard()
    refreshInterval.value = setInterval(fetchLeaderboard, 3000)
}

function stopAutoRefresh() {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value)
        refreshInterval.value = null
    }
}

watch(() => props.sessionId, () => {
    if (props.autoRefresh) {
        startAutoRefresh()
    } else {
        fetchLeaderboard()
    }
}, { immediate: true })

watch(() => props.autoRefresh, (val) => {
    if (val) startAutoRefresh()
    else {
        stopAutoRefresh()
        fetchLeaderboard()
    }
})
</script>

<template>
    <div class="flex flex-col h-full text-white">
        <h2 class="text-2xl font-bold text-center mb-4 shrink-0">
            {{ isFinal ? t.kahoot.finalResults : t.kahoot.leaderboard }}
        </h2>

        <!-- My Score (for students) -->
        <div v-if="myEntry && !isFinal" class="bg-[#1f5252]/30 border border-[#1f5252] rounded-xl p-4 mb-4 shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-white/70">{{ t.kahoot.yourScore }}</p>
                    <p class="text-2xl font-bold">{{ myEntry.total_score }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-white/70">{{ t.kahoot.position }}</p>
                    <p class="text-2xl font-bold">#{{ leaderboard.indexOf(myEntry) + 1 }}</p>
                </div>
            </div>
            <div class="flex gap-4 mt-2 text-sm text-white/60">
                <span>{{ t.kahoot.correctAnswers }}: {{ myEntry.correct_count }}/{{ totalQuestions }}</span>
                <span>{{ t.kahoot.answered }}: {{ myEntry.answered }}/{{ totalQuestions }}</span>
            </div>
        </div>

        <!-- Podium -->
        <div v-if="podium.length > 0" class="flex items-end justify-center gap-3 mb-6 shrink-0">
            <!-- 2nd -->
            <div v-if="podium[1]" class="flex flex-col items-center">
                <div class="w-12 h-12 rounded-full bg-gray-500 flex items-center justify-center text-xl font-bold mb-1">2</div>
                <p class="text-xs font-medium truncatemax-w-20 text-center">{{ podium[1].username }}</p>
                <p class="text-xs text-white/60">{{ podium[1].total_score }}</p>
            </div>
            <!-- 1st -->
            <div v-if="podium[0]" class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-full bg-yellow-500 flex items-center justify-center text-2xl font-bold mb-1">1</div>
                <p class="text-sm font-bold truncate max-w-24 text-center">{{ podium[0].username }}</p>
                <p class="text-sm text-yellow-400">{{ podium[0].total_score }}</p>
            </div>
            <!-- 3rd -->
            <div v-if="podium[2]" class="flex flex-col items-center">
                <div class="w-12 h-12 rounded-full bg-orange-600 flex items-center justify-center text-xl font-bold mb-1">3</div>
                <p class="text-xs font-medium truncate max-w-20 text-center">{{ podium[2].username }}</p>
                <p class="text-xs text-white/60">{{ podium[2].total_score }}</p>
            </div>
        </div>

        <!-- Full Leaderboard -->
        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
            <div v-if="loading && leaderboard.length === 0" class="text-center text-white/40 py-8">
                <svg class="animate-spin h-8 w-8 mx-auto mb-2" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                Cargando...
            </div>

            <div v-for="(entry, i) in leaderboard" :key="entry.user_id"
                class="flex items-center justify-between p-3 rounded-xl mb-2 transition-colors"
                :class="[
                    entry.user_id === authUser?.id ? 'bg-[#1f5252]/20 border border-[#1f5252]/40' : 'bg-[#1e2e38]',
                    isFinal && i < 3 ? 'border-l-4 border-l-yellow-500' : ''
                ]">
                <div class="flex items-center gap-3">
                    <span class="w-8 text-center font-bold text-lg"
                        :class="{ 'text-yellow-400': i === 0, 'text-gray-400': i === 1, 'text-orange-400': i === 2, 'text-white/40': i > 2 }">
                        {{ i + 1 }}
                    </span>
                    <div>
                        <p class="font-medium text-sm">{{ entry.username }}</p>
                        <p v-if="isFinal" class="text-xs text-white/50">
                            {{ entry.correct_count }}/{{ totalQuestions }} {{ t.kahoot.correctAnswers.toLowerCase() }}
                        </p>
                    </div>
                </div>
                <span class="font-bold text-lg">{{ entry.total_score }}</span>
            </div>

            <p v-if="!loading && leaderboard.length === 0" class="text-center text-white/40 py-8">
                {{ t.kahoot.noParticipants }}
            </p>
        </div>

        <!-- Actions -->
        <div v-if="isFinal" class="mt-4 shrink-0">
            <button @click="$emit('end')"
                class="w-full bg-[#1f5252] hover:bg-[#2a6a6a] text-white font-bold py-3 px-6 rounded-xl transition-colors cursor-pointer">
                {{ t.kahoot.switchToChat }}
            </button>
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
