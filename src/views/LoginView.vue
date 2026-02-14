<!--Vista de formulario de registro-->
<script setup>
    import FormNavBar from '../components/FormNavBar.vue';
    import ButtonForm from '../components/buttons/ButtonForm.vue';
    import { useTranslations } from '../composables/useTranslations'
    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
</script>

<template>
    <main class="relative flex flex-col justify-center items-center min-h-screen bg-black/50">
        <FormNavBar></FormNavBar>
        <h1 class="text-6xl mb-[70px] text-white [text-shadow:-3px_3px_1px_black]">{{ t.login.title }}</h1>
        <div class="flex justify-center items-center mb-[150px]" id="form-container">
            <form class="flex flex-col justify-center h-[400px] w-[400px] p-[10px] pl-[20px] pr-[20px] bg-white rounded-bl-xl rounded-tl-xl" method="post">
                <label class="mt-[60px] font-bold" for="username-register-form" id="username-register-label">{{ t.login.username }}</label>
                <input type="text" class="outline-none border-0 border-b-[1px] border-black mb-[30px] p-0.5 text-[20px]" maxlength="50" id="username-register-form" name="username-register-form" :placeholder="t.login.placeholderUsername" required>
                <label class="font-bold" for="password-register-form" id="password-register-label">{{ t.login.password }}</label>
                <input type="password" class="outline-none border-0 border-b-[1px] border-black mb-[30px] p-0.5 text-[20px]" maxlength="20" id="password-register-form" name="password-register-form" :placeholder="t.login.placeholderPassword" required>

                <RouterLink to="/home" class="text-center">
                    <ButtonForm :value="t.login.submit"></ButtonForm>
                </RouterLink>

                <RouterLink to="/" class="flex justify-center items-center text-[15px] gap-1 text-[#4a4a4a] font-bold mt-[50px] transition-all duration-200 ease-in-out hover:brightness-200" id="redirect-login">{{ t.login.noAccount }}<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></RouterLink>
            </form>
            <div id="side-panel" class="flex flex-col justify-center items-center h-[400px] w-[400px] p-[10px] text-white rounded-br-xl rounded-tr-xl bg-[linear-gradient(140deg,#326465,#1d2e3e)]">
                <p class="text-center text-[29px] font-bold mb-[30px] [text-shadow:-2px_2px_1px_black]" id="welcome">{{ t.login.welcome }}</p>
                <img class="w-[90px] h-[100px]" src="/src/assets/logo/logoTelamon.png" alt="">
                <p class="text-center text-[20px] font-bold mt-[30px] ml-[10px] mr-[10px] [text-shadow:-2px_2px_1px_black]" id="eslogan">{{ t.login.eslogan }}</p>
            </div>
        </div>
    </main>
</template>

<style scoped>

</style>