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
            <span class="text-[40px] font-bold">Eventos Disponibles</span>
            <SearchBar></SearchBar>
            <div id="mainBody" class="flex flex-col gap-4 justify-center items-center min-h-[92.9vh] mt-12">
                <!-- Evento 1 -->
                <div class="event-card">
                    <img src="../assets/evento.webp" alt="Imagen del evento">
                    <span class="font-bold text-[25px] ml-4">Nombre del evento</span>
                    <div class="flex mt-2 ml-4 text-[20px]">
                        <span class="font-bold mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- Evento 2 -->
                <div class="event-card">
                    <img src="../assets/evento.webp" alt="Imagen del evento">
                    <span class="font-bold text-[25px] ml-4">Nombre del evento</span>
                    <div class="flex mt-2 ml-4 text-[20px]">
                        <span class="font-bold mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- Evento 3 -->
                <div class="event-card">
                    <img src="../assets/evento.webp" alt="Imagen del evento">
                    <span class="font-bold text-[25px] ml-4">Nombre del evento</span>
                    <div class="flex mt-2 ml-4 text-[20px]">
                        <span class="font-bold mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- Evento 4 -->
                <div class="event-card">
                    <img src="../assets/evento.webp" alt="Imagen del evento">
                    <span class="font-bold text-[25px] ml-4">Nombre del evento</span>
                    <div class="flex mt-2 ml-4 text-[20px]">
                        <span class="font-bold mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- Evento 5 -->
                <div class="event-card">
                    <img src="../assets/evento.webp" alt="Imagen del evento">
                    <span class="font-bold text-[25px] ml-4">Nombre del evento</span>
                    <div class="flex mt-2 ml-4 text-[20px]">
                        <span class="font-bold mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- Evento 6 -->
                <div class="event-card">
                    <img src="../assets/evento.webp" alt="Imagen del evento">
                    <span class="font-bold text-[25px] ml-4">Nombre del evento</span>
                    <div class="flex mt-2 ml-4 text-[20px]">
                        <span class="font-bold mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- Evento 7 -->
                <div class="event-card">
                    <img src="../assets/evento.webp" alt="Imagen del evento">
                    <span class="font-bold text-[25px] ml-4">Nombre del evento</span>
                    <div class="flex mt-2 ml-4 text-[20px]">
                        <span class="font-bold mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- Evento 8 -->
                <div class="event-card">
                    <img src="../assets/evento.webp" alt="Imagen del evento">
                    <span class="font-bold text-[25px] ml-4">Nombre del evento</span>
                    <div class="flex mt-2 ml-4 text-[20px]">
                        <span class="font-bold mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- Evento 9 -->
                <div class="event-card">
                    <img src="../assets/evento.webp" alt="Imagen del evento">
                    <span class="font-bold text-[25px] ml-4">Nombre del evento</span>
                    <div class="flex mt-2 ml-4 text-[20px]">
                        <span class="font-bold mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- Evento 10 -->
                <div class="event-card">
                    <img src="../assets/evento.webp" alt="Imagen del evento">
                    <span class="font-bold text-[25px] ml-4">Nombre del evento</span>
                    <div class="flex mt-2 ml-4 text-[20px]">
                        <span class="font-bold mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- Evento 11 -->
                <div class="event-card">
                    <img src="../assets/evento.webp" alt="Imagen del evento">
                    <span class="font-bold text-[25px] ml-4">Nombre del evento</span>
                    <div class="flex mt-2 ml-4 text-[20px]">
                        <span class="font-bold mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
                    <button class="bg-[#0a2d4e] p-2">Entrar</button>
                </div>

                <!-- Evento 12 -->
                <div class="event-card">
                    <img src="../assets/evento.webp" alt="Imagen del evento">
                    <span class="font-bold text-[25px] ml-4">Nombre del evento</span>
                    <div class="flex mt-2 ml-4 text-[20px]">
                        <span class="font-bold mr-2">Horario:</span>
                        <span>00:00</span>
                    </div>
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

    .event-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        width: 325px;
        height: 375px;
        border-radius: 20px;
        position: relative;
        overflow: hidden;
    }

    .event-card img {
        width: 100%;
        height: 55%;
        object-fit: cover;
        margin: auto;
        margin-bottom: 10px;
    }

    .event-card button {
        position: absolute;
        bottom: 20px;
        right: 20px;
        border-radius: 20px;
        width: 100px;
    }

    .event-card button:hover {
        background-color: #0a1e35;
        cursor: pointer;
    }
</style>