<script setup>
    import NavBar from '../components/NavBar.vue';
    import SearchBar from '../components/SearchBar.vue';
    import SideBar from '../components/SideBar.vue';
    
    import { useTranslations } from '../composables/useTranslations'
    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
</script>

<template>
    <main class="flex min-h-screen justify-between">
        <NavBar></NavBar>
        <section class="text-white w-[1500px] mr-14 pt-8 pb-10">
            <span class="text-[40px] font-bold">Charlas Disponibles</span>
            <SearchBar></SearchBar>
            <div id="mainBody" class="flex flex-col gap-4 justify-center items-center min-h-[92.9vh] text-[20px] mt-12">
                <!-- meeting 1 -->
                <div class="meeting-card">
                    <span class="font-bold">Nombre del meeting</span>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bold mr-2">Profesor:</span>
                        <span>Juanra</span>
                    </div>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bol mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <span>Descripción del meeting</span>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- meeting 2 -->
                <div class="meeting-card">
                    <span class="font-bold">Nombre del meeting</span>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bold mr-2">Profesor:</span>
                        <span>Juanra</span>
                    </div>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bol mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <span>Descripción del meeting</span>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- meeting 3 -->
                <div class="meeting-card">
                    <span class="font-bold">Nombre del meeting</span>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bold mr-2">Profesor:</span>
                        <span>Juanra</span>
                    </div>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bol mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <span>Descripción del meeting</span>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- meeting 4 -->
                <div class="meeting-card">
                    <span class="font-bold">Nombre del meeting</span>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bold mr-2">Profesor:</span>
                        <span>Juanra</span>
                    </div>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bol mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <span>Descripción del meeting</span>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- meeting 5 -->
                <div class="meeting-card">
                    <span class="font-bold">Nombre del meeting</span>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bold mr-2">Profesor:</span>
                        <span>Juanra</span>
                    </div>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bol mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <span>Descripción del meeting</span>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- meeting 6 -->
                <div class="meeting-card">
                    <span class="font-bold">Nombre del meeting</span>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bold mr-2">Profesor:</span>
                        <span>Juanra</span>
                    </div>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bol mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <span>Descripción del meeting</span>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- meeting 7 -->
                <div class="meeting-card">
                    <span class="font-bold">Nombre del meeting</span>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bold mr-2">Profesor:</span>
                        <span>Juanra</span>
                    </div>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bol mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <span>Descripción del meeting</span>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- meeting 8 -->
                <div class="meeting-card">
                    <span class="font-bold">Nombre del meeting</span>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bold mr-2">Profesor:</span>
                        <span>Juanra</span>
                    </div>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bol mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <span>Descripción del meeting</span>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- meeting 9 -->
                <div class="meeting-card">
                    <span class="font-bold">Nombre del meeting</span>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bold mr-2">Profesor:</span>
                        <span>Juanra</span>
                    </div>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bol mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <span>Descripción del meeting</span>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- meeting 10 -->
                <div class="meeting-card">
                    <span class="font-bold">Nombre del meeting</span>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bold mr-2">Profesor:</span>
                        <span>Juanra</span>
                    </div>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bol mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <span>Descripción del meeting</span>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- meeting 11 -->
                <div class="meeting-card">
                    <span class="font-bold">Nombre del meeting</span>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bold mr-2">Profesor:</span>
                        <span>Juanra</span>
                    </div>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bol mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <span>Descripción del meeting</span>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- meeting 12 -->
                <div class="meeting-card">
                    <span class="font-bold">Nombre del meeting</span>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bold mr-2">Profesor:</span>
                        <span>Juanra</span>
                    </div>
                    <div class="flex mt-2 mb-4">
                        <span class="font-bol mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <span>Descripción del meeting</span>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>
            </div>
        </section>
    </main>
</template>

<style scoped>
    #mainBody {
        display: grid;
        grid-template-columns: auto auto auto auto;
        gap: 40px;
    }

    .meeting-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        width: 325px;
        height: 375px;
        border-radius: 20px;
        padding: 20px;
        position: relative;
        overflow: hidden;
    }

    .meeting-card button {
        position: absolute;
        bottom: 20px;
        right: 20px;
        border-radius: 20px;
        width: 100px;
        font-size: 16px;
    }

    .meeting-card button:hover {
        background-color: #0a1e35;
        cursor: pointer;
    }
</style>