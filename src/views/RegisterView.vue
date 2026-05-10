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
    import { useIsMobile } from '@/composables/useIsMobile';

    const { isMobile } = useIsMobile()

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

        // ─── loadCenters definido UNA SOLA VEZ (fuera del click handler) ───
        const educationLevelSelect = document.getElementById('educationLevelSelect');
        const institutionSelect = document.getElementById('institutionSelect');
        const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

        async function loadCenters(type) {
            institutionSelect.innerHTML = '<option value="">Cargando...</option>';
            try {
                const response = await fetch(`${apiBase}/educational-centers?type=${type}`);
                const centers = await response.json();
                institutionSelect.innerHTML = '';
                if (!centers.length) {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'No hay centros disponibles para este nivel';
                    institutionSelect.appendChild(opt);
                    return;
                }
                centers.forEach(center => {
                    const option = document.createElement('option');
                    option.value = center.id;
                    option.textContent = center.name;
                    institutionSelect.appendChild(option);
                });
            } catch (e) {
                institutionSelect.innerHTML = '<option value="">Error al cargar centros</option>';
            }
        }

        // Listener registrado UNA SOLA VEZ
        educationLevelSelect.addEventListener('change', function () {
            loadCenters(this.value);
        });

        // ─── resto del estado del formulario ───
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
                if (regex) validateExpresion(e.target, regex);
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

        registerForm.addEventListener('click', function(e) { e.preventDefault(); });
        registerButton.addEventListener('click', function() { validateForm(); });

        function showWarning(msg) {
            const el = document.getElementById('formErrWarning');
            const p = el.querySelector('p') || el;
            p.textContent = msg;
            el.classList.add('opacity-100');
            setTimeout(() => el.classList.remove('opacity-100'), 3000);
        }

        function validateInputs(inputs) {
            for (let i = 0; i < inputs.length; i++) {
                if (inputs[i].classList.contains('border-red-500') || inputs[i].value === '') {
                    showWarning('Faltan datos o hay datos erróneos');
                    return false;
                }
            }
            return true;
        }

        function buildEIPayload() {
            const selectedOpt = institutionSelect.options[institutionSelect.selectedIndex];
            return {
                education_level:       EIInput[0].value,
                institution_name:      selectedOpt ? selectedOpt.textContent : '',
                educational_center_id: selectedOpt ? selectedOpt.value : '',
            };
        }

        async function sendVerificationCode() {
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

            let payload = { ...allRolesValueList };
            if (formPath === 'EU') {
                payload = { ...payload, ...studentTeacheEuValueList };
            } else if (formPath === 'Student' || formPath === 'Teacher') {
                payload = { ...payload, ...studentTeacheEuValueList, ...buildEIPayload() };
            } else if (formPath === 'EI') {
                payload = { ...payload, ...buildEIPayload() };
            }

            try {
                const response = await fetch(`${apiBase}/send-code`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
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

        async function validateForm() {
            if (!codeInput.value || codeInput.value.trim() === '') {
                showWarning('Por favor, ingresa el código de verificación');
                return;
            }
            if (!/^\d{6}$/.test(codeInput.value)) {
                showWarning('El código debe contener exactamente 6 dígitos');
                return;
            }

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

            let payload = { ...allRolesValueList };
            if (formPath === 'EU') {
                payload = { ...payload, ...studentTeacheEuValueList };
            } else if (formPath === 'Student' || formPath === 'Teacher') {
                payload = { ...payload, ...studentTeacheEuValueList, ...buildEIPayload() };
            } else if (formPath === 'EI') {
                payload = { ...payload, ...buildEIPayload() };
            }
            payload.verification_code = codeInput.value;

            try {
                const response = await fetch(`${apiBase}/register`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();
                if (data.status === 'success') {
                    if (data.user && data.access_token) login(data.user, data.access_token);
                    router.push('/home');
                } else {
                    showWarning(data.message || 'Código de verificación incorrecto o expirado');
                }
            } catch (error) {
                console.error(error);
                showWarning('Error al procesar el registro. Intenta de nuevo.');
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
                    nextButton.classList.replace('bottom-15', 'bottom-13');
                    registerForm.classList.add('h-70');
                    loadCenters(educationLevelSelect.value); // Carga inicial al mostrar el paso

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
                    studentTeacheEuForm.classList.replace('mb-20', 'mb-24');
                    nextButton.id = "studentButton";

                } else if (nextButton.id === "studentButton") {
                    if (!validateInputs(studentTeacheEuInput)) return;
                    studentTeacheEuForm.classList.replace('flex', 'hidden');
                    EIForm.classList.replace('hidden', 'flex');
                    nextButton.id = "validateEmail";
                    nextButton.classList.replace('bottom-15', 'bottom-13');
                    registerForm.classList.add('h-70');
                    loadCenters(educationLevelSelect.value); // Carga inicial al mostrar el paso

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
                    studentTeacheEuForm.classList.replace('mb-20', 'mb-24');
                    nextButton.id = "teacherButton";

                } else if (nextButton.id === "teacherButton") {
                    if (!validateInputs(studentTeacheEuInput)) return;
                    studentTeacheEuForm.classList.replace('flex', 'hidden');
                    EIForm.classList.replace('hidden', 'flex');
                    nextButton.id = "validateEmail";
                    nextButton.classList.replace('bottom-15', 'bottom-13');
                    registerForm.classList.add('h-70');
                    loadCenters(educationLevelSelect.value); // Carga inicial al mostrar el paso

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
                    studentTeacheEuForm.classList.replace('mb-20', 'mb-24');
                    nextButton.id = "validateEmail";

                } else if (nextButton.id === "validateEmail") {
                    if (!validateInputs(studentTeacheEuInput)) return;
                    studentTeacheEuForm.classList.replace('flex', 'hidden');
                    validateEmailForm.classList.replace('hidden', 'flex');
                    registerButton.classList.replace('hidden', 'flex');
                    registerButton.classList.replace('hidden', 'flex');
                    nextButton.classList.add('hidden');
                    sendVerificationCode();
                }
            }
        });
    });
</script>

<template>
    <main class="relative flex flex-col justify-center items-center min-h-screen bg-black/50">

        <span id="formErrWarning" class="absolute opacity-0 top-5 p-2 bg-red-300/80 border-2 rounded-xl border-red-500/80 transition-all duration-300">
            <p class="font-bold text-white text-shadow-sm">Faltan datos o hay datos erroneos</p>
        </span>

        <h1 class="text-3xl lg:text-6xl my-4 lg:mt-0 lg:mb-17.5 font-bold text-white text-shadow-lg">{{ t.register.title }}</h1>
        <div class="flex flex-col lg:flex-row justify-center items-center mb-37.5" id="form-container">
            <div class="flex flex-col lg:justify-center items-center lg:h-100 w-90 lg:w-100 p-2.5 text-white rounded-tl-xl rounded-tr-xl lg:rounded-bl-xl lg:rounded-tl-xl lg:rounded-tr-none" id="side-panel">
                <p class="text-center text-2xl lg:text-3xl font-bold mb-7.5 text-shadow-md" id="welcome">{{ t.register.welcome }}<span class="text-[#a0c4d4]">{{ t.nav.website }}</span></p>
                <img class="w-22.5 h-25" src="/src/assets/logo/logoTelamon.png" alt="">
                <p class="text-center text-lg lg:text-xl font-bold mt-7.5 text-shadow-md" id="eslogan">{{ t.register.eslogan }}{{ t.nav.website }}</p>
            </div>
            <form id="registerForm" class="relative flex flex-col justify-center lg:h-100 w-90 lg:w-100 p-2.5 pl-5 pr-5 bg-white rounded-br-xl rounded-bl-xl lg:rounded-br-xl lg:rounded-tr-xl lg:rounded-bl-none" method="post">
                
                <section id="allRolesForm" class="forms flex flex-col mb-32">
                    <label class="font-bold lg:mt-7.5" for="username-register-form" id="username-register-label">{{ t.register.email }}</label>
                    <span class=" text-xs">{{ t.register.emailSpan }}</span>
                    <input type="text" class="allRolesInput outline-hidden border-b border-black mb-7.5 p-0.5 text-lg lg:text-xl" maxlength="50" autocomplete="off" id="email-register-form" name="email-register-form" :placeholder="t.register.placeholderEmail" required>
                    <p hidden class="absolute top-27.5 left-33.75 font-semibold">Email inválido</p>
                    <label class="font-bold" for="password-register-form" id="password-register-label">{{ t.register.password }}</label>
                    <input type="password" class="allRolesInput outline-hidden border-b border-black mb-7.5 p-0.5 text-lg lg:text-xl" maxlength="20" autocomplete="off" id="password-register-form" name="password-register-form" :placeholder="t.register.placeholderPassword" required>
                    <p hidden class="absolute top-48.75 left-27.5 text-sm font-semibold">Al menos 8 carácteres</p>
                    <label class="font-bold" for="who-register-form" id="who-register-label">{{ t.register.whoAreYou }}</label>
                    <select name="selectRole" id="selectRole" class="allRolesInput border-b border-black pb-1" required>
                        <option value="EI">{{ t.register.educationalInstitution }}</option>
                        <option value="Student">{{ t.register.student }}</option>
                        <option value="Teacher">{{ t.register.teacher }}</option>
                        <option value="EU">{{ t.register.externalUser }}</option>
                    </select>
                </section>

                <section id="studentTeacheEuForm" class="forms flex-col mb-20 hidden">
                    <label class="font-bold" for="student-name" id="studentName">{{ t.register.name }}</label>
                    <input type="text" class="studentTeacheEuInput outline-hidden border-b border-black mb-7.5 p-0.5 text-lg lg:text-xl" maxlength="50" autocomplete="off" id="name-register-form" name="name-register-form" :placeholder="t.register.placeholderName" required></input>
                    <p hidden class="absolute top-18 left-6 lg:top-22.5 lg:left-7.5 text-sm font-semibold">El nombre debe tener entre 2 y 12 letras</p>
                    <label class="font-bold" for="student-name" id="studentName">{{ t.register.surnames }}</label>
                    <input type="text" class="studentTeacheEuInput outline-hidden border-b border-black mb-7.5 p-0.5 text-lg lg:text-xl" maxlength="50" autocomplete="off" id="surname-register-form" name="surname-register-form" :placeholder="t.register.placeholderSurnames" required></input>
                    <p hidden class="absolute top-40 left-25 text-sm font-semibold">Deben haber 2 apellidos</p>
                    <label class="font-bold" for="student-name" id="studentName">{{ t.register.DNI }}</label>
                    <input type="text" class="studentTeacheEuInput outline-hidden border-b border-black mb-7.5 p-0.5 text-lg lg:text-xl" maxlength="50" autocomplete="off" id="dni-register-form" name="dni-register-form" :placeholder="t.register.placeholderDNI" required></input>
                    <p hidden class="absolute top-61.75 left-31.25 text-sm font-semibold">DNI/NIE inválido</p>
                </section>

                <section id="EIForm" class="forms flex-col mb-20 hidden">
                    <label class="font-bold" for="choose-center">{{ t.register.educationLevel }}</label>
                    <select id="educationLevelSelect" class="EIInput border-b border-black pb-1">
                        <option value="PE">{{ t.register.primary }}</option>
                        <option value="SE">{{ t.register.secondary }}</option>
                        <option value="UR">{{ t.register.college }}</option>
                        <option value="FP">{{ t.register.fp }}</option>
                    </select>
                    <label class="mt-8 font-bold">{{ t.register.yourEducationalInstitution }}</label>
                    <select id="institutionSelect" class="EIInput border-b border-black pb-1">
                        <option value="">Selecciona un nivel primero</option>
                    </select>
                </section>

                <section id="validateEmailForm" class="forms hidden flex-col items-center mb-16">
                    <p id="validateEmailText" class="mb-5 text-center font-bold">Hemos enviado un código de verificación a tu correo</p>
                    <input type="text" class="validateEmailInput outline-hidden border-b border-black mb-7.5 p-0.5 w-32 text-center text-xl" maxlength="6" autocomplete="off" id="code-register-form" name="code-register-form" required></input>
                </section>

                <button id="registerButton" class="absolute bottom-15 lg:bottom-20 right-8 lg:right-12 text-center hidden">
                    <ButtonForm :value="t.register.submit"></ButtonForm>
                </button>

                <ButtonForm id="nextButton" value="Siguiente" class="absolute bottom-15 right-8 lg:bottom-15 lg:right-12.5 border rounded-lg"></ButtonForm>

                <RouterLink to="/login" class="absolute bottom-5 right-12 lg:right-16 flex justify-center items-center text-sm gap-1 text-[#4a4a4a] font-bold transition-all duration-200 ease-in-out hover:brightness-200" id="redirect-login">{{ t.register.haveAccount }}<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></RouterLink>

            </form>
            
        </div>
    </main>
</template>

<style scoped>
    #side-panel {
        background: linear-gradient(140deg, #326465,#1d2e3e);
    }
</style>