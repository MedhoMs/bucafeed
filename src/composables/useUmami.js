import { ref, onMounted } from 'vue'
import { loadUmami } from '@/utils/umami'

const isTrackingEnabled = ref(localStorage.getItem('umami-consent') === 'true')

export const useUmami = () => {
    
    const toggleTracking = () => {
        const newValue = !isTrackingEnabled.value
        isTrackingEnabled.value = newValue
        localStorage.setItem('umami-consent', newValue.toString())
        
        if (newValue) {
            loadUmami()
        } else {
            // Umami no tiene un "unload" oficial por script, 
            // pero al refrescar o navegar ya no se cargará si el consent es false.
            window.location.reload() 
        }
    }

    return {
        isTrackingEnabled,
        toggleTracking
    }
}
