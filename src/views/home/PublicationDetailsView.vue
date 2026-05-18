<script setup>
    import NavBar from '../../components/NavBar/NavBar.vue';
    import PageHeader from '@/components/common/PageHeader.vue';
    import { ref, onMounted, computed } from 'vue'
    import { useRoute } from 'vue-router'
    import { user } from '@/stores/auth'
    import { useTranslations } from '../../composables/useTranslations'
    import { useApi } from '@/composables/useApi'
    import UnverifiedBanner from '@/components/common/UnverifiedBanner.vue'

    const isUnverified = computed(() => ['Student', 'Teacher'].includes(user.value?.role) && user.value?.is_verified === false)

    const publicationDetails = ref(null)
    const { t } = useTranslations()
    const route = useRoute()
    const { get } = useApi()

    const formattedDate = computed(() => {
        try {
            if (!publicationDetails.value?.created_at) return ''
            const d = new Date(publicationDetails.value.created_at)
            if (isNaN(d.getTime())) return publicationDetails.value.created_at
            return d.toISOString().split('T')[0]
        } catch (e) {
            return publicationDetails.value?.created_at || '---'
        }
    })

    onMounted(async () => {
        const id = route.params.id;
        if (id) {
            try {
                const res = await get(`publications/${id}`);
                publicationDetails.value = res.data || res;
            } catch (e) {
                console.error("Error fetching publication details:", e);
            }
        }
    })
</script>

<template>
    <div class="min-h-screen">
        <NavBar></NavBar>
        <main class="lg:pl-75 pt-20 lg:pt-0 flex flex-col min-h-screen">
            <!-- Banner compacto superior si no está verificado -->
            <div class="px-6 lg:px-14 pt-6" v-if="isUnverified">
                <UnverifiedBanner 
                    compact 
                    :message="t.publications?.unverifiedBanner || 'Puedes ver la información de la publicación, pero no podrás publicar hasta que tu centro verifique tu identidad.'"
                />
            </div>
            <PageHeader 
                :title="t.publications?.detailsTitle || 'Detalles de la Publicación'"  
                :subtitle="t.publications?.detailsSubtitle || 'Información completa y novedades del centro educativo.'"
                noMargin
            />

            <section class="flex flex-col gap-8 text-white w-full max-w-screen-2xl mx-auto px-6 lg:px-14 mb-20">
                <div class="flex flex-col sm:flex-row gap-8 sm:gap-14 w-fit mt-4">
                    <div class="flex items-center gap-2">
                        <svg class="bg-accent-normal p-3 rounded-2xl" xmlns="http://www.w3.org/2000/svg" height="56px" viewBox="0 -960 960 960" width="56px" fill="#e3e3e3"><path d="M480-144 216-276v-240L48-600l432-216 432 216v312h-72v-276l-96 48v240L480-144Zm0-321 271-135-271-135-271 135 271 135Zm0 240 192-96v-159l-192 96-192-96v159l192 96Zm0-240Zm0 81Zm0 0Z"/></svg>
                        <div>
                            <p class="font-bold text-xs">{{ t.publications?.educationalCenter || 'Institución educativa' }}</p>
                            <p>{{ publicationDetails?.center_name || (t.publications?.unknown || 'Desconocido') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="bg-accent-normal p-3 rounded-2xl" xmlns="http://www.w3.org/2000/svg" height="56px" viewBox="0 -960 960 960" width="56px" fill="#e3e3e3"><path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Zm280 240q-17 0-28.5-11.5T440-440q0-17 11.5-28.5T480-480q17 0 28.5 11.5T520-440q0 17-11.5 28.5T480-400Zm-160 0q-17 0-28.5-11.5T280-440q0-17 11.5-28.5T320-480q17 0 28.5 11.5T360-440q0 17-11.5 28.5T320-400Zm320 0q-17 0-28.5-11.5T600-440q0-17 11.5-28.5T640-480q17 0 28.5 11.5T680-440q0 17-11.5 28.5T640-400ZM480-240q-17 0-28.5-11.5T440-280q0-17 11.5-28.5T480-320q17 0 28.5 11.5T520-280q0 17-11.5 28.5T480-240Zm-160 0q-17 0-28.5-11.5T280-280q0-17 11.5-28.5T320-320q17 0 28.5 11.5T360-280q0 17-11.5 28.5T320-240Zm320 0q-17 0-28.5-11.5T600-280q0-17 11.5-28.5T640-320q17 0 28.5 11.5T680-280q0 17-11.5 28.5T640-240Z"/></svg>
                        <div>
                            <p class="font-bold text-xs">{{ t.publications?.publishDate || 'Fecha de publicación' }}</p>
                            <p>{{ formattedDate || '--/--/----' }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-8 bg-white/5 border border-white/10 rounded-3xl p-6 md:p-10 mt-4">
                    <img v-if="publicationDetails?.image_url" :src="publicationDetails.image_url" class="w-auto lg:max-w-[45%] max-h-[400px] rounded-3xl object-contain border border-white/10 shadow-2xl" alt="Publication Image">
                    <div v-else class="w-full lg:w-2/5 h-[300px] rounded-3xl bg-secondary-normal/20 flex items-center justify-center border border-secondary-normal/30">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#B7B7B7"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm40-80h480L570-480 450-320l-90-120-120 160Zm-40 80v-560 560Z"/></svg>
                    </div>
                    <div class="flex flex-col gap-8 flex-1">
                        <div class="flex flex-col gap-4">
                            <h2 class="text-3xl font-black uppercase tracking-tighter text-white">{{ publicationDetails?.title || (t.publications?.untitled || 'Sin Título') }}</h2>
                            <p class="max-w-3xl text-white/80 whitespace-pre-line text-sm md:text-base leading-relaxed pt-2">{{ publicationDetails?.description || (t.publications?.noContent || 'No hay contenido disponible para esta publicación.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-center md:justify-start">
                    <RouterLink to="/publicaciones" class="flex justify-center items-center gap-2 bg-accent-normal hover:bg-accent-normal-hover text-white px-8 py-3.5 cursor-pointer rounded-2xl duration-300 shadow-lg font-black uppercase tracking-widest text-[10px]">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="m274-450 248 248-42 42-320-320 320-320 42 42-248 248h526v60H274Z"/></svg>
                        {{ t.nav.back || 'Volver' }}
                    </RouterLink>
                </div>
            </section>
        </main>
    </div>
</template>
