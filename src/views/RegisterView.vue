<!--Vista de formulario de registro-->
<script setup>
    import FormNavBar from '../components/FormNavBar.vue';
    import ButtonForm from '../components/buttons/ButtonForm.vue';
    import { useTranslations } from '../composables/useTranslations'
    const { t } = useTranslations() //Variable para llamar al archivo de traduccion

    import { onMounted, ref } from 'vue';

    onMounted(() => {
        const registerForm = document.getElementById('registerForm');
        const nextButton = document.getElementById('nextButton');
        const allRolesForm = document.getElementById('allRolesForm');
        const studentTeacheEuForm = document.getElementById('studentTeacheEuForm');
        const EIForm = document.getElementById('EIForm');
        const selectRole = document.getElementById('selectRole');
        const registerButton = document.getElementById('registerButton');
        const forms = document.getElementById('forms');

        let formPath = '';

        registerForm.addEventListener('click', function(e){
            e.preventDefault();
        });

        nextButton.addEventListener('click', function() {
            if (selectRole.value === "EI") {
                formPath = "EI";

                allRolesForm.classList.remove('flex');
                allRolesForm.classList.add('hidden');
                EIForm.classList.remove('hidden');
                EIForm.classList.add('flex');
                registerButton.classList.remove('hidden');
                registerButton.classList.add('flex');
                nextButton.classList.add('hidden');

            } else if (selectRole.value === "Student") {
                formPath = "Student"; 

                allRolesForm.classList.remove('flex');
                allRolesForm.classList.add('hidden');
                studentTeacheEuForm.classList.remove('hidden');
                studentTeacheEuForm.classList.add('flex');

                if (nextButton.id === "studentButton") {
                    studentTeacheEuForm.classList.add('hidden');
                    studentTeacheEuForm.classList.remove('flex');
                    EIForm.classList.remove('hidden');
                    EIForm.classList.add('flex');
                    registerButton.classList.remove('hidden');
                    registerButton.classList.add('flex');
                    nextButton.classList.add('hidden');
                }
                nextButton.id = "studentButton";

            } else if (selectRole.value === "Teacher") {
                formPath = "Teacher";

                allRolesForm.classList.remove('flex');
                allRolesForm.classList.add('hidden');
                studentTeacheEuForm.classList.remove('hidden');
                studentTeacheEuForm.classList.add('flex');

                if (nextButton.id === "teacherButton") {
                    studentTeacheEuForm.classList.add('hidden');
                    studentTeacheEuForm.classList.remove('flex');
                    EIForm.classList.remove('hidden');
                    EIForm.classList.add('flex');
                    registerButton.classList.remove('hidden');
                    registerButton.classList.add('flex');
                    nextButton.classList.add('hidden');
                }
                nextButton.id = "teacherButton";

            } else if (selectRole.value === "EU") {
                formPath = "EU";

                allRolesForm.classList.remove('flex');
                allRolesForm.classList.add('hidden');
                studentTeacheEuForm.classList.remove('hidden');
                studentTeacheEuForm.classList.add('flex');
                nextButton.id = "button1"; 
            }
        });
    });
</script>

<template>
    <main class="relative flex flex-col justify-center items-center min-h-screen bg-black/50">
        <FormNavBar></FormNavBar>
        <h1 class="text-6xl mb-[70px] text-white [text-shadow:-3px_3px_1px_black]">{{ t.register.title }}</h1>
        <div class="flex justify-center items-center mb-[150px]" id="form-container">
            <div class="flex flex-col justify-center items-center h-[400px] w-[400px] p-[10px] text-white rounded-bl-xl rounded-tl-xl" id="side-panel">
                <p class="text-center text-[29px] font-bold mb-[30px] [text-shadow:-2px_2px_1px_black]" id="welcome">{{ t.register.welcome }}<span class="text-[#a0c4d4]">{{ t.nav.website }}</span></p>
                <img class="w-[90px] h-[100px]" src="/src/assets/logo/logoTelamon.png" alt="">
                <p class="text-center text-[20px] font-bold mt-[30px] [text-shadow:-2px_2px_1px_black]" id="eslogan">{{ t.register.eslogan }}{{ t.nav.website }}</p>
            </div>
            <form id="registerForm" class="relative flex flex-col justify-center h-[400px] w-[400px] p-[10px] pl-[20px] pr-[20px] bg-white rounded-br-xl rounded-tr-xl" method="post">
                
                <section id="allRolesForm" class="forms flex flex-col mb-32">
                    <label class="mt-[30px] mb" for="username-register-form" id="username-register-label">{{ t.register.email }}</label>
                    <span class=" text-xs">{{ t.register.emailSpan }}</span>
                    <input type="text" class="input-form" maxlength="50" autocomplete id="username-register-form" name="username-register-form" :placeholder="t.register.placeholderEmail" required>
                    <label for="password-register-form" id="password-register-label">{{ t.register.password }}</label>
                    <input type="password" class="input-form" maxlength="20" autocomplete id="password-register-form" name="password-register-form" :placeholder="t.register.placeholderPassword" required>
                    <label for="who-register-form" id="who-register-label">¿Quién eres?</label>
                    <select name="selectRole" id="selectRole" class="border-b border-black pb-1" required>
                        <option value="EI">Institución Educativa</option>
                        <option value="Student">Estudiante</option>
                        <option value="Teacher">Profesor</option>
                        <option value="EU">Usuario Ajeno</option>
                    </select>
                </section>

                <section id="studentTeacheEuForm" class="forms flex-col mb-20 hidden">
                    <label for="student-name" id="studentName">Nombre</label>
                    <input type="text" class="input-form" maxlength="50" autocomplete id="username-register-form" name="username-register-form" :placeholder="t.register.placeholderEmail" required></input>
                    <label for="student-name" id="studentName">Apellidos</label>
                    <input type="text" class="input-form" maxlength="50" autocomplete id="username-register-form" name="username-register-form" :placeholder="t.register.placeholderEmail" required></input>
                    <label for="student-name" id="studentName">DNI/NIE</label>
                    <input type="text" class="input-form" maxlength="50" autocomplete id="username-register-form" name="username-register-form" :placeholder="t.register.placeholderEmail" required></input>                    
                </section>

                <section id="EIForm" class="forms flex-col mb-20 hidden">
                    <label for="choose-center" id="chooseCenter">Nivel de Enseñanza</label>
                    <select name="" id="" class="">
                        <option value="PE">Educación Primaria</option>
                        <option value="SE">Educación Secundaria</option>
                        <option value="College">Universidad</option>
                        <option value="FP">Formación Profesional</option>
                    </select>
                    <label class="my-8" for="choose-location" id="chooseLocation">¿Cual es su Institucion Educativa?</label>
                    <select name="" id="" class="">
                        <option value="">Saldran todo de la base de datos</option>
                    </select>

                    <RouterLink to="/home" id="registerButton" class="absolute bottom-[80px] right-12 text-center hidden">
                        <ButtonForm :value="t.register.submit"></ButtonForm>
                    </RouterLink>
                </section>

                <ButtonForm id="nextButton" value="Siguiente" class="absolute bottom-[60px] right-[50px] border rounded-lg"></ButtonForm>

                <RouterLink to="/login" class="absolute bottom-[20px] right-16 flex justify-center items-center text-[15px] gap-1 text-[#4a4a4a] font-bold transition-all duration-200 ease-in-out hover:brightness-200" id="redirect-login">{{ t.register.haveAccount }}<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></RouterLink>

            </form>
            
        </div>
    </main>
</template>

<style scoped>
    #side-panel {
        background: linear-gradient(140deg, #326465,#1d2e3e);/* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
    }

    label {
        font-weight: bold;
    }

    .input-form {
        outline: none;
        border: none;
        border-bottom: 1px solid black;
        margin-bottom: 30px;
        padding: 2px;
        font-size: 20px;
    }
</style>