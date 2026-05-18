<!--Vista de formulario de registro-->
<script setup>
    import { useTranslations } from '@/composables/useTranslations'
    import { useRouter } from 'vue-router';
    const { t } = useTranslations()

</script>

<template>
    <main class="relative flex flex-col justify-center items-center min-h-screen">
        <div class="ocean-bg" />
        <div class="caustics" />

        <section class="relative flex justify-center w-120 z-10 mb-30">
            <img class="absolute -top-50 left-1/2 -translate-x-1/2 rotate-180 shark" width="220px" src="/tiburon.webp" alt="">
            <img class="absolute top-30 -left-12 rotate-45 shark" width="220px" src="/tiburon.webp" alt="">
            <img class="absolute top-30 -right-12 -rotate-45 shark" width="220px" src="/tiburon.webp" alt="">

            <p class="four-o-four text-8xl font-bold tracking-[8px]">404</p>
            <p class="absolute -bottom-65 text-3xl subtitle">{{ t.validation?.errorPage?.title || 'Esta página se ha perdido en el océano' }}</p>

            <button class="absolute -bottom-90 text-2xl p-3 border-3 rounded-xl border-[#13536b] bg-white text-[#0d3a4a] font-bold transition-transform duration-200 hover:scale-105 cursor-pointer">
                <RouterLink class="cursor-pointer block" to="/home">
                    {{ t.validation?.errorPage?.backToHome || 'Volver al Inicio' }}
                </RouterLink>
            </button>

        </section>
    </main>
</template>

<style scoped>
main {
    background: radial-gradient(ellipse at 50% 0%, #0d3a4a 0%, #061e2b 40%, #020c12 100%);
}

/* ── Fondo con textura de agua ── */
.ocean-bg {
    position: absolute;
    inset: 0;
    background:
        repeating-linear-gradient(
            180deg,
            transparent 0px,
            rgba(13, 90, 120, 0.04) 2px,
            transparent 4px
        ),
        repeating-linear-gradient(
            90deg,
            transparent 0px,
            rgba(13, 90, 120, 0.03) 1px,
            transparent 3px
        );
    pointer-events: none;
}

/* Efecto de luz que se filtra desde arriba (caústicas) */
.caustics {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 60% 30% at 50% 0%, rgba(109, 228, 252, 0.07) 0%, transparent 70%),
        radial-gradient(ellipse 30% 20% at 30% 10%, rgba(109, 228, 252, 0.04) 0%, transparent 60%),
        radial-gradient(ellipse 25% 15% at 70% 5%,  rgba(109, 228, 252, 0.04) 0%, transparent 60%);
    pointer-events: none;
}

/* ── 404 ── */
.four-o-four {
    color: #e8f8ff;
    text-shadow: 0 0 15px rgba(109, 228, 252);
    position: relative;
    z-index: 10;
}

/* ── Subtítulo ── */
.subtitle {
    color: rgba(180, 230, 245, 0.6);
    letter-spacing: 0.04em;
    text-shadow: 0 0 12px rgba(109, 228, 252, 0.3);
    white-space: nowrap;
}

/* ── Tiburones ── */
.shark {
    z-index: 10;
    filter:
        drop-shadow(0 0 6px rgba(109, 228, 252, 0.5))
        drop-shadow(0 0 18px rgba(109, 228, 252, 0.2));
}
</style>