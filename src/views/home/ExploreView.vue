<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    import SideBar from '../../components/SideBar.vue';
    
    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
</script>

<template>
    <NavBar></NavBar>
    <main class="flex min-h-screen justify-between lg:pl-75">
        <section class="text-white lg:w-375 w-87.5 mx-auto lg:mr-14 mb-4">
            <SearchBar></SearchBar>
            <div id="mainExplore" class="flex justify-center items-center min-h-[92.9vh]">
                <h1 class="text-2xl lg:text-4xl text-center font-bold">No hay nada que buscar</h1>
            </div>
        </section>
    </main>
</template>