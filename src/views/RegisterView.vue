<!--Vista de formulario de registro-->
<script setup>
    import FormNavBar from '@/components/NavBar/FormNavBar.vue';
    import ButtonForm from '@/components/buttons/ButtonForm.vue';
    import { useTranslations } from '@/composables/useTranslations'
    import axios from 'axios';
    const { t } = useTranslations()
    import { onMounted, ref } from 'vue';

    onMounted(() => {
        const registerForm = document.getElementById('registerForm');
        const nextButton = document.getElementById('nextButton');
        const allRolesForm = document.getElementById('allRolesForm');
        const studentTeacheEuForm = document.getElementById('studentTeacheEuForm');
        const EIForm = document.getElementById('EIForm');
        const selectRole = document.getElementById('selectRole');
        const registerButton = document.getElementById('registerButton');
        const textInputs = document.querySelectorAll('input');

        const allRolesInput = document.querySelectorAll('.allRolesInput');
        const studentTeacheEuInput = document.querySelectorAll('.studentTeacheEuInput');
        const EIInput = document.querySelectorAll('.EIInput');

        let formPath = '';
        let errorText;

        const patterns = {
            "name-register-form": /^[A-ZaÉÍouÑ][a-zaéíouñ]{5,12}$/,
            "surname-register-form": /^[A-ZaÉÍouÑ][a-zaéíouñ]{2,20}\s[A-ZaÉÍouÑ][a-zaéíouñ]{2,20}$/,
            "dni-register-form": /^(\d{8}[A-Z]|[XYZ]\d{7}[A-Z])$/,
            "email-register-form": /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
            "password-register-form": /^[A-Za-z0-9]{8,}$/
        };

        textInputs.forEach((input) => {
            input.addEventListener("keyup", (e) => {
                const name = e.target.name;

                //Cojo el patron que corresponde al nombre del input
                const regex = patterns[name];

                //Si existe un patron asociado, valido el campo
                if (regex) {
                    validateExpresion(e.target, regex);
                }
            });
        });

        function validateExpresion(input, pattern) {

            errorText = input.nextElementSibling;

            //Compruebo si el valor cumple el patron
            if (pattern.test(input.value)) {
                input.classList.add('valido');
                errorText.hidden = true;
            } else {
                input.classList.add('invalido');
                errorText.classList.add('text-red-500');
                errorText.hidden = false;
            }
        }

        registerForm.addEventListener('click', function(e){
            e.preventDefault();
        });

        registerButton.addEventListener('click', function() {
            validateForm(formPath);
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
                registerButton.classList.remove('hidden');
                registerButton.classList.add('flex');
                nextButton.classList.add('hidden');
            }
        });

        async function validateForm() {
            const allRolesValueList = { 
                email: allRolesInput[0].value, 
                password: allRolesInput[1].value, 
                role: allRolesInput[2].value,
            };

            const studentTeacheEuValueList = {
                name: studentTeacheEuInput[0].value,
                last_name: studentTeacheEuInput[1].value,
                dni: studentTeacheEuInput[2].value,
            };

            const EIValueList = {
                education_level: EIInput[0].value,
                institution_name: EIInput[1].value,
            };

            //Creo un payload base con lo que tienen que tener todos los usuarios
            let payload = { ...allRolesValueList }; //... Copia todo el contenido de un array y lo introduce en una variable

            if (formPath === 'EU') {
                payload = { ...payload, ...studentTeacheEuValueList }; //payload = lo que habia en el payload base + el array correspondiente
            } else if (formPath === 'Student' || formPath === 'Teacher') {
                payload = { ...payload, ...studentTeacheEuValueList, ...EIValueList };
            } else if (formPath === 'EI') {
                payload = { ...payload, ...EIValueList };
            }

            try {
                const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
                const response = await fetch(`${apiBase}/register`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();
                if (data.status === 'success') {
                    router.push('/home');
                }
                console.log(data);
            } catch (error) {
                console.error(error);
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
        <h1 class="text-6xl mb-[70px] text-white [text-shadow:-3px_3px_1px_black]">{{ t.register.title }}</h1>
        <div class="flex justify-center items-center mb-[150px]" id="form-container">
            <div class="flex flex-col justify-center items-center h-[400px] w-[400px] p-[10px] text-white rounded-bl-xl rounded-tl-xl" id="side-panel">
                <p class="text-center text-[29px] font-bold mb-[30px] [text-shadow:-2px_2px_1px_black]" id="welcome">{{ t.register.welcome }}<span class="text-[#a0c4d4]">{{ t.nav.website }}</span></p>
                <img class="w-[90px] h-[100px]" src="/src/assets/logo/logoTelamon.png" alt="">
                <p class="text-center text-[20px] font-bold mt-[30px] [text-shadow:-2px_2px_1px_black]" id="eslogan">{{ t.register.eslogan }}{{ t.nav.website }}</p>
            </div>
            <form id="registerForm" class="relative flex flex-col justify-center h-[400px] w-[400px] p-[10px] pl-[20px] pr-[20px] bg-white rounded-br-xl rounded-tr-xl" method="post">
                
                <section id="allRolesForm" class="forms flex flex-col mb-32">
                    <label class="font-bold mt-[30px]" for="username-register-form" id="username-register-label">{{ t.register.email }}</label>
                    <span class=" text-xs">{{ t.register.emailSpan }}</span>
                    <input type="text" class="allRolesInput outline-none border-b border-black mb-[30px] p-[2px] text-xl" maxlength="50" autocomplete="off" id="email-register-form" name="email-register-form" :placeholder="t.register.placeholderEmail" required>
                    <p hidden class="absolute top-[110px] left-[135px] font-bold">Email inválido</p>
                    <label class="font-bold" for="password-register-form" id="password-register-label">{{ t.register.password }}</label>
                    <input type="password" class="allRolesInput outline-none border-b border-black mb-[30px] p-[2px] text-xl" maxlength="20" autocomplete="off" id="password-register-form" name="password-register-form" :placeholder="t.register.placeholderPassword" required>
                    <p hidden class="absolute top-[195px] left-[110px] text-[15px] font-bold">Al menos 8 carácteres</p>
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
                    <input type="text" class="studentTeacheEuInput outline-none border-b border-black mb-[30px] p-[2px] text-xl" maxlength="50" autocomplete="off" id="name-register-form" name="name-register-form" :placeholder="t.register.placeholderEmail" required></input>
                    <p hidden>El nombre debe tener entre 5 y 12 letras</p>
                    <label class="font-bold" for="student-name" id="studentName">Apellidos</label>
                    <input type="text" class="studentTeacheEuInput outline-none border-b border-black mb-[30px] p-[2px] text-xl" maxlength="50" autocomplete="off" id="surname-register-form" name="surname-register-form" :placeholder="t.register.placeholderEmail" required></input>
                    <p hidden>Deben haber 2 apellidos</p>
                    <label class="font-bold" for="student-name" id="studentName">DNI/NIE</label>
                    <input type="text" class="studentTeacheEuInput outline-none border-b border-black mb-[30px] p-[2px] text-xl" maxlength="50" autocomplete="off" id="dni-register-form" name="dni-register-form" :placeholder="t.register.placeholderEmail" required></input>
                    <p hidden>DNI/NIE inválido</p>
                </section>

                <section id="EIForm" class="forms flex-col mb-20 hidden">
                    <label class="font-bold" for="choose-center">Nivel de Enseñanza</label>
                    <select id="educationLevelSelect" class="EIInput border-b border-black pb-1">
                        <option value="PE">Educación Primaria</option>
                        <option value="SE">Educación Secundaria</option>
                        <option value="College">Universidad</option>
                        <option value="FP">Formación Profesional</option>
                    </select>
                    <label class="mt-8 font-bold">¿Cuál es su Institución Educativa?</label>
                    <select id="institutionSelect" class="EIInput border-b border-black pb-1">
                        <option value="">Selecciona un nivel primero</option>
                    </select>

                </section>

                <button id="registerButton" class="absolute bottom-[80px] right-12 text-center hidden">
                    <ButtonForm :value="t.register.submit"></ButtonForm>
                </button>

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
</style> 