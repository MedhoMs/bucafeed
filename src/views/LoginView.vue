<!--Vista de formulario de login-->
<script setup>
    import FormNavBar from '@/components/NavBar/FormNavBar.vue';
    import ButtonForm from '@/components/buttons/ButtonForm.vue';
    import { useTranslations } from '@/composables/useTranslations'
    import { useRouter } from 'vue-router';
    import { onMounted } from 'vue';
    import { login } from '@/stores/auth';

    const { t } = useTranslations()
    const router = useRouter();

    onMounted(() => {
        const loginButton = document.getElementById('loginButton');
        const emailInput  = document.getElementById('username-register-form');
        const passInput   = document.getElementById('password-register-form');
        const errorMsg    = document.getElementById('loginErrorMsg');

        loginButton.addEventListener('click', async (e) => {
            e.preventDefault();

            // Validación básica de campos vacíos
            if (emailInput.value === '' || passInput.value === '') {
                errorMsg.textContent = 'Por favor, rellena todos los campos';
                errorMsg.classList.remove('hidden');
                return;
            }

            try {
                const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
                const response = await fetch(`${apiBase}/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        email:    emailInput.value,
                        password: passInput.value,
                    })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Guardar los datos del usuario en el estado
                    if (data.user && data.access_token) {
                        login(data.user, data.access_token);
                    }
                    router.push('/home');
                } else {
                    // Mostrar el mensaje de error que devuelve Laravel
                    errorMsg.textContent = data.message || 'Credenciales incorrectas';
                    errorMsg.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error de red:', error);
                errorMsg.textContent = 'Error de conexión, inténtalo de nuevo';
                errorMsg.classList.remove('hidden');
            }
        });
    });
</script>

<template>
    <main class="relative flex flex-col justify-center items-center min-h-screen bg-black/50">
        <FormNavBar></FormNavBar>
        <h1 class="text-6xl mb-17.5 text-white [text-shadow:-3px_3px_1px_black]">{{ t.login.title }}</h1>
        <div class="flex justify-center items-center mb-37.5" id="form-container">
            <form class="relative flex flex-col justify-center h-100 w-100 p-2.5 pl-5 pr-5 bg-white rounded-bl-xl rounded-tl-xl" method="post">

                <!-- Mensaje de error -->
                <p id="loginErrorMsg" class="hidden absolute top-4 left-0 right-0 text-center text-red-500 text-sm font-semibold px-4"></p>

                <label class="font-bold mt-7.5" for="username-register-form" id="username-register-label">{{ t.login.email }}</label>
                <span class="text-xs">{{ t.login.emailSpan }}</span>
                <input type="text" class="outline-hidden border-0 border-b border-black mb-7.5 p-0.5 text-[20px]" maxlength="50" id="username-register-form" name="username-register-form" :placeholder="t.login.placeholderEmail" required>

                <label class="font-bold" for="password-register-form" id="password-register-label">{{ t.login.password }}</label>
                <input type="password" class="outline-hidden border-0 border-b border-black mb-7.5 p-0.5 text-[20px]" maxlength="20" id="password-register-form" name="password-register-form" :placeholder="t.login.placeholderPassword" required>

                <button id="loginButton" class="text-center">
                    <ButtonForm :value="t.login.submit"></ButtonForm>
                </button>

                <RouterLink to="/" class="flex justify-center items-center text-[15px] gap-1 text-[#4a4a4a] font-bold mt-12.5 transition-all duration-200 ease-in-out hover:brightness-200" id="redirect-login">
                    {{ t.login.noAccount }}
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </RouterLink>
            </form>

            <div id="side-panel" class="flex flex-col justify-center items-center h-100 w-100 p-2.5 text-white rounded-br-xl rounded-tr-xl bg-[linear-gradient(140deg,#326465,#1d2e3e)]">
                <p class="text-center text-[29px] font-bold mb-7.5 [text-shadow:-2px_2px_1px_black]" id="welcome">{{ t.login.welcome }}</p>
                <img class="w-22.5 h-25" src="/src/assets/logo/logoTelamon.png" alt="">
                <p class="text-center text-[20px] font-bold mt-7.5 ml-2.5 mr-2.5 [text-shadow:-2px_2px_1px_black]" id="eslogan">{{ t.login.eslogan }}</p>
            </div>
        </div>
    </main>
</template>

<style scoped>
</style>