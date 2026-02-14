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
        <section class="text-white w-[1500px] mr-14">
            <SearchBar></SearchBar>
            <div id="mainExplore" class="flex justify-center items-center min-h-[92.9vh]">
                <h1 class="text-4xl font-bold">No hay nada que buscar</h1>
            </div>
        </section>
    </main>
</template>

<style scoped>

</style>