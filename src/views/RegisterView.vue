<!--Vista de formulario de registro-->
<script setup>
    import FormNavBar from '@/components/NavBar/FormNavBar.vue';
    import ButtonForm from '@/components/buttons/ButtonForm.vue';
    import { useTranslations } from '@/composables/useTranslations'
    import { useRouter } from 'vue-router';
    const { t } = useTranslations()
    const router = useRouter();
    import { onMounted, ref } from 'vue';
    import { login } from '@/stores/auth';

    onMounted(() => {
        const registerForm = document.getElementById('registerForm');
        const nextButton = document.getElementById('nextButton');
        const allRolesForm = document.getElementById('allRolesForm');
        const studentTeacheEuForm = document.getElementById('studentTeacheEuForm');
        const EIForm = document.getElementById('EIForm');
        const validateEmailForm = document.getElementById('validateEmailForm');
        const validateEmailText = document.getElementById('validateEmailText');
        const selectRole = document.getElementById('selectRole');
        const registerButton = document.getElementById('registerButton');
        const textInputs = document.querySelectorAll('input');

        const allRolesInput = document.querySelectorAll('.allRolesInput');
        const studentTeacheEuInput = document.querySelectorAll('.studentTeacheEuInput');
        const EIInput = document.querySelectorAll('.EIInput');
        const codeInput = document.getElementById('code-register-form');

        let formPath = '';
        let errorText;

        const patterns = {
            "name-register-form": /^[A-ZÁÉÍÓÚÑ][a-záéíóúüñ]{1,12}$/,
            "surname-register-form": /^[A-ZÁÉÍÓÚÑ][a-záéíóúüñ]{2,20}\s[A-ZÁÉÍÓÚÑ][a-záéíóúüñ]{2,20}$/,
            "dni-register-form": /^(\d{8}[A-Z]|[XYZ]\d{7}[A-Z])$/,
            "email-register-form": /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
            "password-register-form": /^[A-Za-z0-9]{8,}$/
        };

        textInputs.forEach((input) => {
            input.addEventListener("keyup", (e) => {
                const name = e.target.name;
                const regex = patterns[name];
                if (regex) {
                    validateExpresion(e.target, regex);
                }
            });
        });

        function validateExpresion(input, pattern) {
            errorText = input.nextElementSibling;
            if (pattern.test(input.value)) {
                input.classList.add('valido');
                input.classList.remove('border-red-500');
                input.classList.add('border-green-500');
                errorText.hidden = true;
            } else {
                input.classList.add('invalido');
                errorText.classList.add('text-red-500');
                input.classList.add('border-red-500');
                errorText.hidden = false;
            }
        }

        registerForm.addEventListener('click', function(e){
            e.preventDefault();
        });

        registerButton.addEventListener('click', function() {
            validateForm();
        });

        function validateInputs(inputs) {
            for (let i = 0; i < inputs.length; i++) {
                if (inputs[i].classList.contains('border-red-500') || inputs[i].value === '') {
                    let formErrWarning = document.getElementById('formErrWarning');
                    formErrWarning.classList.toggle('opacity-100');
                    setTimeout(() => {
                        formErrWarning.classList.toggle('opacity-100');
                    }, 3000);
                    return false;
                }
            }
            return true;
        }

        //Llamo a /api/send-code, guardo el payload en Laravel cache y envia el email
        async function sendVerificationCode() {
            const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

            const allRolesValueList = {
                email:    allRolesInput[0].value,
                password: allRolesInput[1].value,
                role:     allRolesInput[2].value,
            };
            const studentTeacheEuValueList = {
                name:      studentTeacheEuInput[0].value,
                last_name: studentTeacheEuInput[1].value,
                dni:       studentTeacheEuInput[2].value,
            };
            const EIValueList = {
                education_level:  EIInput[0].value,
                institution_name: EIInput[1].value,
            };

            let payload = { ...allRolesValueList };
            if (formPath === 'EU') {
                payload = { ...payload, ...studentTeacheEuValueList };
            } else if (formPath === 'Student' || formPath === 'Teacher') {
                payload = { ...payload, ...studentTeacheEuValueList, ...EIValueList };
            } else if (formPath === 'EI') {
                payload = { ...payload, ...EIValueList };
            }

            try {
                const response = await fetch(`${apiBase}/send-code`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();

                if (data.status === 'success') {
                    validateEmailText.textContent = `Hemos enviado un código de verificación a ${allRolesInput[0].value}`;
                } else {
                    console.error('Error al enviar el código:', data);
                }
            } catch (error) {
                console.error('Error de red:', error);
            }
        }

        //Envio el payload completo + el codigo a /api/register
        async function validateForm() {
            // Validar que el código no esté vacío y tenga 6 dígitos
            if (!codeInput.value || codeInput.value.trim() === '') {
                let formErrWarning = document.getElementById('formErrWarning');
                formErrWarning.textContent = 'Por favor, ingresa el código de verificación';
                formErrWarning.classList.toggle('opacity-100');
                setTimeout(() => {
                    formErrWarning.classList.toggle('opacity-100');
                }, 3000);
                return;
            }

            if (!/^\d{6}$/.test(codeInput.value)) {
                let formErrWarning = document.getElementById('formErrWarning');
                formErrWarning.textContent = 'El código debe contener exactamente 6 dígitos';
                formErrWarning.classList.toggle('opacity-100');
                setTimeout(() => {
                    formErrWarning.classList.toggle('opacity-100');
                }, 3000);
                return;
            }

            const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

            const allRolesValueList = {
                email:    allRolesInput[0].value,
                password: allRolesInput[1].value,
                role:     allRolesInput[2].value,
            };
            const studentTeacheEuValueList = {
                name:      studentTeacheEuInput[0].value,
                last_name: studentTeacheEuInput[1].value,
                dni:       studentTeacheEuInput[2].value,
            };
            const EIValueList = {
                education_level:  EIInput[0].value,
                institution_name: EIInput[1].value,
            };

            let payload = { ...allRolesValueList };
            if (formPath === 'EU') {
                payload = { ...payload, ...studentTeacheEuValueList };
            } else if (formPath === 'Student' || formPath === 'Teacher') {
                payload = { ...payload, ...studentTeacheEuValueList, ...EIValueList };
            } else if (formPath === 'EI') {
                payload = { ...payload, ...EIValueList };
            }

            //Añado el codigo de verificacion introducido por el usuario
            payload.verification_code = codeInput.value;

            try {
                const response = await fetch(`${apiBase}/register`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();
                if (data.status === 'success') {
                    if (data.user && data.access_token) {
                        login(data.user, data.access_token);
                    }
                    router.push('/home');
                } else {
                    let formErrWarning = document.getElementById('formErrWarning');
                    formErrWarning.textContent = data.message || 'Código de verificación incorrecto o expirado';
                    formErrWarning.classList.toggle('opacity-100');
                    setTimeout(() => {
                        formErrWarning.classList.toggle('opacity-100');
                    }, 3000);
                }
                console.log(data);
            } catch (error) {
                console.error(error);
                let formErrWarning = document.getElementById('formErrWarning');
                formErrWarning.textContent = 'Error al procesar el registro. Intenta de nuevo.';
                formErrWarning.classList.toggle('opacity-100');
                setTimeout(() => {
                    formErrWarning.classList.toggle('opacity-100');
                }, 3000);
            }
        }

        nextButton.addEventListener('click', function () {

            if (selectRole.value === "EI") {
                formPath = "EI";

                if (nextButton.id === "nextButton") {
                    if (!validateInputs(allRolesInput)) return;
                    allRolesForm.classList.replace('flex', 'hidden');
                    EIForm.classList.replace('hidden', 'flex');
                    nextButton.id = "validateEmail";

                } else if (nextButton.id === "validateEmail") {
                    EIForm.classList.replace('flex', 'hidden');
                    validateEmailForm.classList.replace('hidden', 'flex');
                    registerButton.classList.replace('hidden', 'flex');
                    nextButton.classList.add('hidden');
                    sendVerificationCode();
                }

            } else if (selectRole.value === "Student") {
                formPath = "Student";

                if (nextButton.id === "nextButton") {
                    if (!validateInputs(allRolesInput)) return;
                    allRolesForm.classList.replace('flex', 'hidden');
                    studentTeacheEuForm.classList.replace('hidden', 'flex');
                    nextButton.id = "studentButton";

                } else if (nextButton.id === "studentButton") {
                    if (!validateInputs(studentTeacheEuInput)) return;
                    studentTeacheEuForm.classList.replace('flex', 'hidden');
                    EIForm.classList.replace('hidden', 'flex');
                    nextButton.id = "validateEmail";

                } else if (nextButton.id === "validateEmail") {
                    EIForm.classList.replace('flex', 'hidden');
                    validateEmailForm.classList.replace('hidden', 'flex');
                    registerButton.classList.replace('hidden', 'flex');
                    nextButton.classList.add('hidden');
                    sendVerificationCode();
                }

            } else if (selectRole.value === "Teacher") {
                formPath = "Teacher";

                if (nextButton.id === "nextButton") {
                    if (!validateInputs(allRolesInput)) return;
                    allRolesForm.classList.replace('flex', 'hidden');
                    studentTeacheEuForm.classList.replace('hidden', 'flex');
                    nextButton.id = "teacherButton";

                } else if (nextButton.id === "teacherButton") {
                    if (!validateInputs(studentTeacheEuInput)) return;
                    studentTeacheEuForm.classList.replace('flex', 'hidden');
                    EIForm.classList.replace('hidden', 'flex');
                    nextButton.id = "validateEmail";

                } else if (nextButton.id === "validateEmail") {
                    EIForm.classList.replace('flex', 'hidden');
                    validateEmailForm.classList.replace('hidden', 'flex');
                    registerButton.classList.replace('hidden', 'flex');
                    nextButton.classList.add('hidden');
                    sendVerificationCode();
                }

            } else if (selectRole.value === "EU") {
                formPath = "EU";

                if (nextButton.id === "nextButton") {
                    if (!validateInputs(allRolesInput)) return;
                    allRolesForm.classList.replace('flex', 'hidden');
                    studentTeacheEuForm.classList.replace('hidden', 'flex');
                    nextButton.id = "validateEmail";

                } else if (nextButton.id === "validateEmail") {
                    if (!validateInputs(studentTeacheEuInput)) return;
                    studentTeacheEuForm.classList.replace('flex', 'hidden');
                    validateEmailForm.classList.replace('hidden', 'flex');
                    registerButton.classList.replace('hidden', 'flex');
                    nextButton.classList.add('hidden');
                    sendVerificationCode();
                }
            }
            const educationLevelSelect = document.getElementById('educationLevelSelect');
            const institutionSelect = document.getElementById('institutionSelect');
            const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

            async function loadCenters(type) {
                const response = await fetch(`${apiBase}/educational-centers?type=${type}`);
                const centers = await response.json();
                institutionSelect.innerHTML = '';
                centers.forEach(center => {
                    const option = document.createElement('option');
                    option.value = center.id;
                    option.textContent = center.name;
                    institutionSelect.appendChild(option);
                });
            }

            educationLevelSelect.addEventListener('change', function () {
                loadCenters(this.value);
            });

            // Cargar centros del nivel por defecto al mostrar el formulario
            loadCenters(educationLevelSelect.value);
        });
    });
</script>

<template>
    <main class="relative flex flex-col justify-center items-center min-h-screen bg-black/50">
        <FormNavBar></FormNavBar>

        <span id="formErrWarning" class="absolute opacity-0 top-5 p-2 bg-red-300/80 border-2 rounded-xl border-red-500/80 transition-all duration-300">
            <p class="font-bold text-white [text-shadow:-1px_1px_1px_black]">Faltan datos o hay datos erroneos</p>
        </span>

        <h1 class="text-6xl mb-17.5 text-white [text-shadow:-3px_3px_1px_black]">{{ t.register.title }}</h1>
        <div class="flex justify-center items-center mb-37.5" id="form-container">
            <div class="flex flex-col justify-center items-center h-100 w-100 p-2.5 text-white rounded-bl-xl rounded-tl-xl" id="side-panel">
                <p class="text-center text-[29px] font-bold mb-7.5 [text-shadow:-2px_2px_1px_black]" id="welcome">{{ t.register.welcome }}<span class="text-[#a0c4d4]">{{ t.nav.website }}</span></p>
                <img class="w-22.5 h-25" src="/src/assets/logo/logoTelamon.png" alt="">
                <p class="text-center text-[20px] font-bold mt-7.5 [text-shadow:-2px_2px_1px_black]" id="eslogan">{{ t.register.eslogan }}{{ t.nav.website }}</p>
            </div>
            <form id="registerForm" class="relative flex flex-col justify-center h-100 w-100 p-2.5 pl-5 pr-5 bg-white rounded-br-xl rounded-tr-xl" method="post">
                
                <section id="allRolesForm" class="forms flex flex-col mb-32">
                    <label class="font-bold mt-7.5" for="username-register-form" id="username-register-label">{{ t.register.email }}</label>
                    <span class=" text-xs">{{ t.register.emailSpan }}</span>
                    <input type="text" class="allRolesInput outline-hidden border-b border-black mb-7.5 p-0.5 text-xl" maxlength="50" autocomplete="off" id="email-register-form" name="email-register-form" :placeholder="t.register.placeholderEmail" required>
                    <p hidden class="absolute top-27.5 left-33.75 font-semibold">Email inválido</p>
                    <label class="font-bold" for="password-register-form" id="password-register-label">{{ t.register.password }}</label>
                    <input type="password" class="allRolesInput outline-hidden border-b border-black mb-7.5 p-0.5 text-xl" maxlength="20" autocomplete="off" id="password-register-form" name="password-register-form" :placeholder="t.register.placeholderPassword" required>
                    <p hidden class="absolute top-48.75 left-27.5 text-[15px] font-semibold">Al menos 8 carácteres</p>
                    <label class="font-bold" for="who-register-form" id="who-register-label">¿Quién eres?</label>
                    <select name="selectRole" id="selectRole" class="allRolesInput border-b border-black pb-1" required>
                        <option value="EI">Institución Educativa</option>
                        <option value="Student">Estudiante</option>
                        <option value="Teacher">Profesor</option>
                        <option value="EU">Usuario Ajeno</option>
                    </select>
                </section>

                <section id="studentTeacheEuForm" class="forms flex-col mb-20 hidden">
                    <label class="font-bold" for="student-name" id="studentName">Nombre</label>
                    <input type="text" class="studentTeacheEuInput outline-hidden border-b border-black mb-7.5 p-0.5 text-xl" maxlength="50" autocomplete="off" id="name-register-form" name="name-register-form" :placeholder="t.register.placeholderEmail" required></input>
                    <p hidden class="absolute top-22.5 left-7.5 font-semibold">El nombre debe tener entre 2 y 12 letras</p>
                    <label class="font-bold" for="student-name" id="studentName">Apellidos</label>
                    <input type="text" class="studentTeacheEuInput outline-hidden border-b border-black mb-7.5 p-0.5 text-xl" maxlength="50" autocomplete="off" id="surname-register-form" name="surname-register-form" :placeholder="t.register.placeholderEmail" required></input>
                    <p hidden class="absolute top-45 left-25 text-[15px] font-semibold">Deben haber 2 apellidos</p>
                    <label class="font-bold" for="student-name" id="studentName">DNI/NIE</label>
                    <input type="text" class="studentTeacheEuInput outline-hidden border-b border-black mb-7.5 p-0.5 text-xl" maxlength="50" autocomplete="off" id="dni-register-form" name="dni-register-form" :placeholder="t.register.placeholderEmail" required></input>
                    <p hidden class="absolute top-66.25 left-31.25 text-[15px] font-semibold">DNI/NIE inválido</p>
                </section>

                <section id="EIForm" class="forms flex-col mb-20 hidden">
                    <label class="font-bold" for="choose-center">Nivel de Enseñanza</label>
                    <select id="educationLevelSelect" class="EIInput border-b border-black pb-1">
                        <option value="PE">Educación Primaria</option>
                        <option value="SE">Educación Secundaria</option>
                        <option value="UR">Universidad</option>
                        <option value="FP">Formación Profesional</option>
                    </select>
                    <label class="mt-8 font-bold">¿Cuál es su Institución Educativa?</label>
                    <select id="institutionSelect" class="EIInput border-b border-black pb-1">
                        <option value="">Selecciona un nivel primero</option>
                    </select>
                </section>

                <section id="validateEmailForm" class="forms hidden flex-col items-center mb-16">
                    <p id="validateEmailText" class="mb-5 text-center font-bold">Hemos enviado un código de verificación a tu correo</p>
                    <input type="text" class="validateEmailInput outline-hidden border-b border-black mb-7.5 p-0.5 w-32 text-center text-xl" maxlength="6" autocomplete="off" id="code-register-form" name="code-register-form" required></input>
                </section>

                <button id="registerButton" class="absolute bottom-20 right-12 text-center hidden">
                    <ButtonForm :value="t.register.submit"></ButtonForm>
                </button>

                <ButtonForm id="nextButton" value="Siguiente" class="absolute bottom-15 right-12.5 border rounded-lg"></ButtonForm>

                <RouterLink to="/login" class="absolute bottom-5 right-16 flex justify-center items-center text-[15px] gap-1 text-[#4a4a4a] font-bold transition-all duration-200 ease-in-out hover:brightness-200" id="redirect-login">{{ t.register.haveAccount }}<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></RouterLink>

            </form>
            
        </div>
    </main>
</template>

<style scoped>
    #side-panel {
        background: linear-gradient(140deg, #326465,#1d2e3e);
    }
</style>