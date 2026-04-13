import { ref, computed } from 'vue'
import translations from '../translations'

const currentLocale = ref('es') // idioma por defecto

export const useTranslations = () => {
    const t = computed(() => translations[currentLocale.value])
    
    const setLocale = (lang) => {
        currentLocale.value = lang
    }
    
    const locale = computed({
        get: () => currentLocale.value,
        set: (val) => currentLocale.value = val
    })
    
    return {
        t,
        locale,
        setLocale
    }
}


