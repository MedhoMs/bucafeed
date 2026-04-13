<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    
    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
</script>

<template>
    <NavBar></NavBar>
    <main class="flex flex-col min-h-screen items-center">
        <section class="text-white lg:w-[1500px] w-[350px] mx-auto lg:mr-14 mb-4">
            <!-- Filtro de notificaciones -->
            <div id="notificationFilter" class="flex lg:justify-between justify-end gap-5 items-center lg:text-[35px] mt-4">
                <span class="py-3 rounded-3xl text-center cursor-pointer hover:bg-[#2A4A5A] duration-500">Todo</span>
                <span class="py-3 rounded-3xl text-center cursor-pointer hover:bg-[#2A4A5A] duration-500">Charlas</span>
                <span class="py-3 rounded-3xl text-center cursor-pointer hover:bg-[#2A4A5A] duration-500">Preguntas Pendientes</span>
            </div>

            <div id="mainBody" class="flex flex-col gap-4 items-center min-h-[92.9vh]">
                <!-- Notificacion de post -->
                <div class="notification-card lg:w-[1500px] w-[350px]">
                    <div class="flex justify-between">
                        <span>HAN RESPONDIDO A RESPUESTA A TU PREGUNTA</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-message-exclamation"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M15 18h-2l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v5.5" /><path d="M19 16v3" /><path d="M19 22v.01" /></svg>
                    </div>
                    <h2 class="notification-title">TITULO DEL POST</h2>
                    <div class="notification-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">24</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>
                
                <!-- Notificaciones de la charla -->
                <div class="notification-card lg:w-[1500px] w-[350px]">
                    <div class="flex justify-between">
                        <span>AVISO CHARLA PROGRAMADA</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-stats"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" /><path d="M18 14v4h4" /><path d="M14 18a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M15 3v4" /><path d="M7 3v4" /><path d="M3 11h16" /></svg>
                    </div>
                    <h2 class="notification-title">CHARLA CON PROFESOR</h2>
                    <div class="notification-footer">
                        <div class="responses-badge">
                            <span>Horario: 14:00 - 16:00</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="notification-card lg:w-[1500px] w-[350px]">
                    <div class="flex justify-between">
                        <span>HAN RESPONDIDO A RESPUESTA A TU PREGUNTA</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-message-exclamation"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M15 18h-2l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v5.5" /><path d="M19 16v3" /><path d="M19 22v.01" /></svg>
                    </div>
                    <h2 class="notification-title">TITULO DEL POST</h2>
                    <div class="notification-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">24</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="notification-card lg:w-[1500px] w-[350px]">
                    <div class="flex justify-between">
                        <span>AVISO CHARLA PROGRAMADA</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-stats"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" /><path d="M18 14v4h4" /><path d="M14 18a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M15 3v4" /><path d="M7 3v4" /><path d="M3 11h16" /></svg>
                    </div>
                    <h2 class="notification-title">CHARLA CON PROFESOR</h2>
                    <div class="notification-footer">
                        <div class="responses-badge">
                            <span>Horario: 14:00 - 16:00</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="notification-card lg:w-[1500px] w-[350px]">
                    <div class="flex justify-between">
                        <span>HAN RESPONDIDO A RESPUESTA A TU PREGUNTA</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-message-exclamation"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M15 18h-2l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v5.5" /><path d="M19 16v3" /><path d="M19 22v.01" /></svg>
                    </div>
                    <h2 class="notification-title">TITULO DEL POST</h2>
                    <div class="notification-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">24</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="notification-card lg:w-[1500px] w-[350px]">
                    <div class="flex justify-between">
                        <span>AVISO CHARLA PROGRAMADA</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-stats"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" /><path d="M18 14v4h4" /><path d="M14 18a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M15 3v4" /><path d="M7 3v4" /><path d="M3 11h16" /></svg>
                    </div>
                    <h2 class="notification-title">CHARLA CON PROFESOR</h2>
                    <div class="notification-footer">
                        <div class="responses-badge">
                            <span>Horario: 14:00 - 16:00</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="notification-card lg:w-[1500px] w-[350px]">
                    <div class="flex justify-between">
                        <span>HAN RESPONDIDO A RESPUESTA A TU PREGUNTA</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-message-exclamation"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M15 18h-2l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v5.5" /><path d="M19 16v3" /><path d="M19 22v.01" /></svg>
                    </div>
                    <h2 class="notification-title">TITULO DEL POST</h2>
                    <div class="notification-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">24</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="notification-card lg:w-[1500px] w-[350px]">
                    <div class="flex justify-between">
                        <span>AVISO CHARLA PROGRAMADA</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-stats"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" /><path d="M18 14v4h4" /><path d="M14 18a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M15 3v4" /><path d="M7 3v4" /><path d="M3 11h16" /></svg>
                    </div>
                    <h2 class="notification-title">CHARLA CON PROFESOR</h2>
                    <div class="notification-footer">
                        <div class="responses-badge">
                            <span>Horario: 14:00 - 16:00</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="notification-card lg:w-[1500px] w-[350px]">
                    <div class="flex justify-between">
                        <span>HAN RESPONDIDO A RESPUESTA A TU PREGUNTA</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-message-exclamation"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M15 18h-2l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v5.5" /><path d="M19 16v3" /><path d="M19 22v.01" /></svg>
                    </div>
                    <h2 class="notification-title">TITULO DEL POST</h2>
                    <div class="notification-footer">
                        <div class="responses-badge">
                            <span class="response-emoji">💬</span>
                            <span class="response-count">24</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>

                <div class="notification-card lg:w-[1500px] w-[350px]">
                    <div class="flex justify-between">
                        <span>AVISO CHARLA PROGRAMADA</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-stats"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" /><path d="M18 14v4h4" /><path d="M14 18a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M15 3v4" /><path d="M7 3v4" /><path d="M3 11h16" /></svg>
                    </div>
                    <h2 class="notification-title">CHARLA CON PROFESOR</h2>
                    <div class="notification-footer">
                        <div class="responses-badge">
                            <span>Horario: 14:00 - 16:00</span>
                        </div>
                        <hr></hr>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>

<style scoped>
    .notification-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 20px;
        padding: 20px;
        position: relative;
        overflow: hidden;
    }

    .notification-card:hover {
        cursor: pointer;
        background: linear-gradient(135deg, rgba(54, 54, 54, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
    }

    .notification-title {
        font-size: 20px;
        font-weight: bold;
    }

    .responses-badge {
        display: flex;
        justify-content: flex-end;
    }
</style>
