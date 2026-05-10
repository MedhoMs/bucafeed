import { ref, computed } from 'vue'
import translations from '../translations'

const currentLocale = ref(localStorage.getItem('telamonet_locale') || 'es')

export const useTranslations = () => {
    const t = computed(() => translations[currentLocale.value])
    
    const setLocale = (lang) => {
        currentLocale.value = lang
        localStorage.setItem('telamonet_locale', lang)
    }
    
    const locale = computed({
        get: () => currentLocale.value,
        set: (val) => {
            currentLocale.value = val
            localStorage.setItem('telamonet_locale', val)
        }
    })
    
    return {
        t,
        locale,
        setLocale
    }
}