<!--Vista de formulario de recuperación de contraseña-->
<script setup>
    import ButtonForm from '@/components/buttons/ButtonForm.vue'
    import { useTranslations } from '@/composables/useTranslations'
    import { useRouter } from 'vue-router'
    import { ref, onMounted } from 'vue'
    import { useIsMobile } from '@/composables/useIsMobile'

    const { t } = useTranslations()
    const router = useRouter()
    const { isMobile } = useIsMobile()

    // ── Estado ────────────────────────────────────────────────────────────────
    const step         = ref(1)          // 1 = pedir email | 2 = código + nueva pass
    const email        = ref('')
    const code         = ref('')
    const newPassword  = ref('')
    const confirmPass  = ref('')
    const errorMsg     = ref('')
    const successMsg   = ref('')
    const loading      = ref(false)

    const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

    // ── Paso 1: enviar código al correo ──────────────────────────────────────
    async function sendCode() {
        errorMsg.value   = ''
        successMsg.value = ''

        if (!email.value) {
            errorMsg.value = 'Por favor, introduce tu email'
            return
        }

        loading.value = true
        try {
            const res  = await fetch(`${apiBase}/forgot-password`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: email.value }),
            })
            const data = await res.json()

            if (data.status === 'success') {
                successMsg.value = 'Código enviado a tu correo. Revisa tu bandeja de entrada.'
                step.value = 2
            } else {
                errorMsg.value = data.message || 'Error al enviar el código'
            }
        } catch {
            errorMsg.value = 'Error de conexión, inténtalo de nuevo'
        } finally {
            loading.value = false
        }
    }

    // ── Paso 2: verificar código y cambiar contraseña ────────────────────────
    async function resetPassword() {
        errorMsg.value   = ''
        successMsg.value = ''

        if (!code.value || !newPassword.value || !confirmPass.value) {
            errorMsg.value = 'Por favor, rellena todos los campos'
            return
        }
        if (newPassword.value.length < 8) {
            errorMsg.value = 'La contraseña debe tener al menos 8 caracteres'
            return
        }
        if (newPassword.value !== confirmPass.value) {
            errorMsg.value = 'Las contraseñas no coinciden'
            return
        }

        loading.value = true
        try {
            const res  = await fetch(`${apiBase}/reset-password`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    email:              email.value,
                    verification_code:  code.value,
                    password:           newPassword.value,
                    password_confirmation: confirmPass.value,
                }),
            })
            const data = await res.json()

            if (data.status === 'success') {
                successMsg.value = '¡Contraseña actualizada! Redirigiendo al inicio de sesión…'
                setTimeout(() => router.push('/login'), 2000)
            } else {
                errorMsg.value = data.message || 'Error al cambiar la contraseña'
            }
        } catch {
            errorMsg.value = 'Error de conexión, inténtalo de nuevo'
        } finally {
            loading.value = false
        }
    }
</script>

<template>
    <main class="relative flex flex-col justify-center items-center min-h-screen bg-black/50">

        <h1 class="text-3xl lg:text-6xl my-4 lg:mt-0 lg:mb-17.5 font-bold text-white text-shadow-lg">
            RECUPERAR CONTRASEÑA
        </h1>

        <div class="flex flex-col lg:flex-row justify-center items-center mb-37.5" id="form-container">

            <!-- Panel lateral (móvil, arriba) -->
            <div v-if="isMobile"
                class="flex flex-col lg:justify-center items-center lg:h-100 w-90 lg:w-100 p-2.5 text-white rounded-tl-xl rounded-tr-xl lg:rounded-bl-xl lg:rounded-tl-xl bg-[linear-gradient(140deg,#326465,#1d2e3e)]"
                id="side-panel">
                <p class="text-center text-2xl lg:text-3xl font-bold mb-7.5 text-shadow-md" id="welcome">{{ t.login.welcome }}</p>
                <img class="w-22.5 h-25" src="/src/assets/logo/logoTelamon.png" alt="">
                <p class="text-center text-2xl lg:text-xl font-bold mt-7.5 ml-2.5 mr-2.5 text-shadow-md" id="eslogan">{{ t.login.eslogan }}</p>
            </div>

            <!-- Formulario principal -->
            <form
                class="relative flex flex-col justify-center lg:h-auto w-90 lg:w-100 p-5 bg-white rounded-bl-xl rounded-br-xl lg:rounded-tr-xl lg:rounded-bl-none"
                @submit.prevent>

                <!-- Mensajes de feedback -->
                <p v-if="errorMsg"   class="text-red-500   text-sm font-semibold text-center mb-3 px-2">{{ errorMsg }}</p>
                <p v-if="successMsg" class="text-green-600 text-sm font-semibold text-center mb-3 px-2">{{ successMsg }}</p>

                <!-- ── PASO 1: email ── -->
                <template v-if="step === 1">
                    <label class="font-bold mt-4" for="recover-email">Correo electrónico</label>
                    <span class="text-xs mb-1">Introduce el email asociado a tu cuenta</span>
                    <input
                        id="recover-email"
                        v-model="email"
                        type="email"
                        class="outline-none border-0 border-b border-black mb-8 p-0.5 text-xl"
                        placeholder="usuario@ejemplo.com"
                        maxlength="100"
                        required>

                    <button type="button" :disabled="loading" @click="sendCode" class="text-center mb-4">
                        <ButtonForm :value="loading ? 'Enviando…' : 'Enviar código'" />
                    </button>
                </template>

                <!-- ── PASO 2: código + nueva contraseña ── -->
                <template v-if="step === 2">
                    <label class="font-bold mt-4" for="recover-code">Código de verificación</label>
                    <span class="text-xs mb-1">Revisa tu bandeja de entrada (o spam)</span>
                    <input
                        id="recover-code"
                        v-model="code"
                        type="text"
                        inputmode="numeric"
                        class="outline-none border-0 border-b border-black mb-6 p-0.5 text-xl tracking-widest"
                        placeholder="123456"
                        maxlength="6"
                        required>

                    <label class="font-bold" for="recover-new-pass">Nueva contraseña</label>
                    <span class="text-xs mb-1">Mínimo 8 caracteres</span>
                    <input
                        id="recover-new-pass"
                        v-model="newPassword"
                        type="password"
                        class="outline-none border-0 border-b border-black mb-6 p-0.5 text-xl"
                        placeholder="••••••••"
                        maxlength="50"
                        required>

                    <label class="font-bold" for="recover-confirm-pass">Confirmar contraseña</label>
                    <input
                        id="recover-confirm-pass"
                        v-model="confirmPass"
                        type="password"
                        class="outline-none border-0 border-b border-black mb-8 p-0.5 text-xl"
                        placeholder="••••••••"
                        maxlength="50"
                        required>

                    <button type="button" :disabled="loading" @click="resetPassword" class="text-center mb-4">
                        <ButtonForm :value="loading ? 'Guardando…' : 'Cambiar contraseña'" />
                    </button>

                    <!-- Reenviar código -->
                    <button type="button" @click="step = 1; errorMsg = ''; successMsg = ''"
                        class="text-sm text-gray-500 hover:text-black hover:underline transition-all duration-200 text-center mt-1 mb-2">
                        ← Volver e introducir otro email
                    </button>
                </template>

                <!-- Enlace al login -->
                <RouterLink to="/login"
                    class="flex justify-center items-center text-sm gap-1 text-[#4a4a4a] font-bold mt-4 mb-2 transition-all duration-200 ease-in-out hover:brightness-200">
                    <p class="lg:text-md text-sm">¿Ya recuerdas tu contraseña? Inicia sesión</p>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </RouterLink>
            </form>

            <!-- Panel lateral (escritorio, derecha) -->
            <div v-if="!isMobile"
                class="flex flex-col lg:justify-center items-center lg:h-auto w-90 lg:w-100 p-2.5 text-white rounded-tl-xl rounded-tr-xl lg:rounded-br-xl lg:rounded-tr-xl lg:rounded-tl-none bg-[linear-gradient(140deg,#326465,#1d2e3e)]"
                id="side-panel">
                <p class="text-center text-2xl lg:text-3xl font-bold mb-7.5 text-shadow-md" id="welcome">{{ t.login.welcome }}</p>
                <img class="w-22.5 h-25" src="/src/assets/logo/logoTelamon.png" alt="">
                <p class="text-center text-2xl lg:text-xl font-bold mt-7.5 ml-2.5 mr-2.5 text-shadow-md" id="eslogan">{{ t.login.eslogan }}</p>
            </div>
        </div>
    </main>
</template>

<style scoped>
</style>