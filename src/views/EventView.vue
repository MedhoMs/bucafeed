<script setup>
    import NavBar from '../components/NavBar.vue';
    import SearchBar from '../components/SearchBar.vue';
    import SideBar from '../components/SideBar.vue';
    
    import { useTranslations } from '../composables/useTranslations'
    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
</script>

<template>
    <main class="flex justify-between min-h-[100vh]">
        <NavBar></NavBar>
        <section class="text-white w-[1580px] mr-4">
            <SearchBar></SearchBar>
            <div id="mainMesagge" class="flex justify-center items-center min-h-[92.9vh]">
                <h1 class="text-4xl font-bold">No hay ningun evento</h1>
            </div>
        </section>
    </main>
</template>

<style scoped>
/*
    main {
        display: flex;
        flex-direction: row;
        justify-content: center;
        min-height: 100vh;
    }

    section {
        width: 1200px;
    }
*/
</style>