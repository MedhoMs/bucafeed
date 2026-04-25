<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    
    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
</script>

<template>
    <NavBar></NavBar>
    <main class="flex min-h-screen lg:pl-75">
        <section class="text-white w-full max-w-5xl mx-auto px-4 lg:px-10 mb-20">
            <!-- Header con Buscador -->
            <div class="mt-12 mb-12 max-w-2xl">
                <SearchBar></SearchBar>
            </div>

            <div id="mainExplore" class="flex flex-col items-center justify-center min-h-[50vh] bg-white/5 rounded-[2.5rem] border border-dashed border-white/10">
                <h1 class="text-xl lg:text-3xl text-center font-black uppercase tracking-tighter text-white/20">No hay nada que buscar</h1>
                <p class="text-white/10 text-[10px] font-bold uppercase mt-2">Prueba a explorar otras secciones</p>
            </div>
        </section>
    </main>
</template>