//Archivo de configuracion de idiomas
import { createI18n } from 'vue-i18n'
import es from './locales/es.json'
import en from './locales/en.json'

const i18n = createI18n({
  legacy: false, // Usa Composition API
  locale: 'es', // idioma por defecto
  fallbackLocale: 'en', // idioma de respaldo
  messages: {
    es,
    en
  }
})

export default i18n
