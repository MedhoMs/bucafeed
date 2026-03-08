<!--Componente de barra de navegacion-->
<script setup>
import { ref } from 'vue'
import { useTranslations } from '@/composables/useTranslations'

const { t, locale, setLocale } = useTranslations()

// Variable reactiva para controlar visibilidad
const showLanguages = ref(false)

const changeLanguage = (lang) => {
    setLocale(lang)
    showLanguages.value = false // Ocultar después de seleccionar
}

const languageSelector = () => {
    showLanguages.value = !showLanguages.value
}

</script>

<template>
    <nav>
        <div id="language-selector">
            <div @click="languageSelector" id="selector-text-svg">
                <p>{{ t.nav.selectLanguage }}</p>
                <svg width="20" height="20" viewBox="0 0 12 12"class="chevron":class="{ 'rotated': showLanguages }">
                    <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="1.5"/>
                </svg>
            </div>
     
            <!--:class="{ 'show': showLanguages }" se usa para atribuirle el valor false o true, y que se aplique el hidden (mirar script setup)-->
            <div id="languages-buttons" :class="{ 'show': showLanguages }">
                <div class="language-flag-container" @click="changeLanguage('es')" >
                    <p id="spanish-selector" class="language-button">Español</p>
                    <img src="../../assets/flags-icons/spain.png" alt="Spain Flag">
                </div>
                
                <div class="language-flag-container" @click="changeLanguage('en')">
                    <p id="english-selector" class="language-button">English</p>
                    <img src="../../assets/flags-icons/united-kingdom.png" alt="United Kingdom Flag">
                </div>
            </div>
        </div>
    </nav>
</template>

<style scoped>
    nav {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    #language-selector {
        display: flex;
        justify-content: center;
        flex-direction: column;
        border: 1px solid black;
        border-radius: 10px;
        overflow: hidden;
        background: -webkit-linear-gradient(140deg, #326465,#1d2e3e);/* Chrome 10-25, Safari 5.1-6 */
        background: linear-gradient(140deg, #326465,#1d2e3e);/* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
    }

    #selector-text-svg {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 5px;
        color: white;
    }

    #selector-text-svg p {
        font-size: 20px;
        padding: 5px;
    }

    #selector-text-svg:hover {
        cursor: pointer;
    }

    /* Animación de la flecha */
    .chevron {
        transform: rotate(-90deg);
        transition: transform 0.3s ease;
    }

    .chevron.rotated {
        transform: rotate(0deg);
    }

    #languages-buttons {
        display: flex;
        flex-direction: column;
        background-color: aqua;
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: max-height 0.4s ease, opacity 0.3s ease, padding 0.4s ease;
        padding: 0;
        background: -webkit-linear-gradient(140deg, #326465,#1d2e3e);/* Chrome 10-25, Safari 5.1-6 */
        background: linear-gradient(140deg, #326465,#1d2e3e);/* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
    }

    #languages-buttons.show {
        max-height: 200px;
        opacity: 1;
    }

    .language-flag-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        gap: 10px;
        transition: transform 0.2s ease-in-out;
    }

    .language-flag-container:hover {
        cursor: pointer;
        transform: scale(1.05);
    }

    .language-button {
        text-align: center;
        color: white;
        font-weight: bold;
        padding: 10px 0px;
    }

    img {
        width: 30px;
        height: 30px;
    }
</style>