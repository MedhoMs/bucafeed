<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    import SideBar from '../../components/SideBar.vue';
    
    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
</script>

<template>
    <main class="flex justify-between min-h-screen">
        <NavBar></NavBar>
        <section class="text-white w-[1580px] mr-4">
            <SearchBar></SearchBar>
            <div id="mainTrending" class="flex justify-center items-center min-h-[92.9vh]">
                <h1 class="text-4xl font-bold">No hay ninguna pregunta</h1>
            </div>
        </section>
    </main>
</template>

<style scoped>

</style>