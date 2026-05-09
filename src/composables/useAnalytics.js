import { ref, onMounted } from 'vue'
import { loadAnalytics } from '@/utils/analytics'

const isTrackingEnabled = ref(localStorage.getItem('analytics-consent') === 'true')

export const useAnalytics = () => {

    const toggleTracking = () => {
        const newValue = !isTrackingEnabled.value
        isTrackingEnabled.value = newValue
        localStorage.setItem('analytics-consent', newValue.toString())

        if (newValue) {
            loadAnalytics()
        } else {
            // No official unload, reload page to stop tracking
            window.location.reload()
        }
    }

    return {
        isTrackingEnabled,
        toggleTracking
    }
}
