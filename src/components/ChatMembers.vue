<script setup>
import { useTranslations } from "../composables/useTranslations";
const { t } = useTranslations(); // Variable para llamar al archivo de traduccion
</script>

<template>
    <aside class="flex justify-end w-40 lg:w-1/5 h-screen shrink-0">
        <div class="flex self-center h-[90%] ml-4 bg-white w-0.5 shrink-0"></div>

        <div class="flex flex-col lg:w-full h-full items-center text-white p-5 overflow-hidden">

            <div class="mr-auto w-full shrink-0">
                <p class="text-lg lg:text-2xl font-bold self-start mb-2">Profesor</p>
                <div class="flex items-center w-full rounded-3xl p-3 mb-5 hover:bg-[#2a4a5a] hover:cursor-pointer">
                    <img src="../assets/logo/logoTelamon.png" alt=""
                        class="hidden lg:block w-10 h-10 rounded-full object-cover border-2 border-white shadow-xs mr-2 shrink-0" />
                    <p class="text-base lg:text-xl">Profesor</p>
                </div>
            </div>

            <div class="flex h-0.5 w-full bg-white shrink-0"></div>

            <p class="text-lg lg:text-2xl font-bold self-start mt-5 mb-2 shrink-0">Alumnos</p>

            <div class="flex flex-col mr-auto w-full flex-1 overflow-y-auto pb-4 pr-2">
                <div v-for="n in 11" :key="n"
                    class="flex items-center w-full rounded-3xl p-3 mb-5 shrink-0 hover:bg-[#2a4a5a] cursor-pointer">
                    <img src="../assets/logo/logoTelamon.png" alt=""
                        class="hidden lg:block w-10 h-10 rounded-full object-cover border-2 border-white shadow-xs mr-2 shrink-0" />
                    <p class="text-base lg:text-xl">Alumno</p>
                </div>
            </div>

        </div>
    </aside>
</template>

<style scoped>

</style>
