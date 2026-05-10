<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import SearchBar from '../../components/SearchBar.vue';
    import PrimaryButton from '../../components/common/PrimaryButton.vue';
    import PageHeader from '@/components/common/PageHeader.vue';
    import { ref } from 'vue';
    import { user } from '@/stores/auth';
    
    import { useTranslations } from '../../composables/useTranslations'
    const { t } = useTranslations()

    const showCreateEventModal = ref(false); // Placeholder logic for now
</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-20 lg:pt-0 flex flex-col min-h-screen">
            <PageHeader 
                :title="t.explore.title" 
                :subtitle="t.explore.subtitle"
            >
                <template #search>
                    <SearchBar class="w-full"></SearchBar>
                </template>
                <template #actions>
                    <PrimaryButton 
                        v-if="user?.role?.toLowerCase() === 'admin' || user?.role?.toLowerCase() === 'centro'"
                        :text="t.explore.newEvent" 
                        icon="plus"
                        @click="() => console.log('crear evento')"
                    />
                </template>
            </PageHeader>

            <section class="text-white w-full px-4 lg:px-10 mb-20">
                <div id="mainExplore" class="flex flex-col items-center justify-center min-h-[50vh] bg-white/5 rounded-[2.5rem] border border-dashed border-white/10 p-10 text-center">
                    <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mb-6 border border-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-opacity="0.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 21l-6 -6" /><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /></svg>
                    </div>
                    <h1 class="text-xl lg:text-3xl font-black uppercase tracking-tighter text-white/20">{{ t.explore.emptyTitle }}</h1>
                    <p class="text-white/10 text-xs font-bold uppercase mt-2 max-w-xs">{{ t.explore.emptySubtitle }}</p>
                </div>
            </section>
        </main>
    </div>
</template>