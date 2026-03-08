<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    import Meeting from '../../components/Meeting.vue';
    
    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations() // Variable para llamar al archivo de traduccion
</script>

<template>
    <main class="flex min-h-screen justify-between">
        <NavBar></NavBar>
        <section class="text-white w-full ml-20 pt-8 pb-10">
            <p class="text-[40px] font-bold">Charlas Disponibles</p>
            <SearchBar></SearchBar>
            <div class="grid grid-cols-4 gap-4 h-[92.9vh] mt-10">

                <Meeting />
                <Meeting />
                <Meeting />
                <Meeting />
                <Meeting />
                <Meeting />

                <iframe data-testid="embed-iframe" style="border-radius:12px" src="https://open.spotify.com/embed/track/2hBbSTlbdMHeb9fOvyGRy1?utm_source=generator" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
            </div>
        </section>
    </main>
</template>