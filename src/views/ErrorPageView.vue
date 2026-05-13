<!--Vista de formulario de registro-->
<script setup>
    import { useTranslations } from '@/composables/useTranslations'
    import { useRouter } from 'vue-router';
    const { t } = useTranslations()

</script>

<template>
    <main class="relative flex flex-col justify-center items-center min-h-screen bg-black/50">
        <section class="flex border rounded-xl bg-white/20 p-5">
            <div>
                <h1 class="text-6xl font-bold">404</h1>
                <p class="text-2xl">Esta página se ha perdido en el oceáno</p>
            </div>
            <img src="/public/logoTelamon.png" alt="Barco Telamon">
        </section>
    </main>
</template>

<style scoped>
    section {
        background-image: url("/public/telamonBarco.webp");
        background-size: cover;
        background-repeat: no-repeat;
        position: relative;
        border-radius: 0.75rem;
        overflow: hidden;
    }

    section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        pointer-events: none;
        border-radius: 0.75rem;
    }

    section > * {
        position: relative;
        z-index: 1;
    }
</style>