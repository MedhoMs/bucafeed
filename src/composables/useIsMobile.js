import { ref } from 'vue'

//Estado global compartido entre componentes
const isMobile = ref(true)
let isInitialized = false

function updateIsMobile() {
    if (typeof window === 'undefined') return
    isMobile.value = window.innerWidth < 1024
}

export function useIsMobile() {
    //Solo registramos un listener global para evitar estados erraticos
    //cuando el composable se usa en varios componentes.
    if (!isInitialized && typeof window !== 'undefined') {
        updateIsMobile()
        window.addEventListener('resize', updateIsMobile)
        isInitialized = true
    }

    return { isMobile }
}
