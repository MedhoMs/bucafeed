<script setup>
// Component shown when a Student account is not yet verified by their educational center
defineProps({
    /** Custom message override */
    message: {
        type: String,
        default: null
    }
})
</script>

<template>
    <div class="flex flex-col items-center justify-center flex-1 py-24 px-6">
        <div class="max-w-md w-full bg-amber-500/8 border border-amber-500/25 rounded-2xl p-8 text-center backdrop-blur-sm shadow-xl">
            <!-- Icono de candado -->
            <div class="w-16 h-16 rounded-2xl bg-amber-500/15 border border-amber-500/25 flex items-center justify-center mx-auto mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>

            <h3 class="text-lg font-black text-white uppercase tracking-wide mb-2">
                Cuenta pendiente de verificación
            </h3>
            <p class="text-white/50 text-sm leading-relaxed">
                {{ message || 'Tu cuenta todavía no ha sido verificada por tu centro educativo. Mientras tanto, no puedes realizar esta acción.' }}
            </p>
            <div class="mt-5 flex items-center justify-center gap-2 text-amber-400/60 text-xs font-bold uppercase tracking-widest">
                <span class="w-2 h-2 rounded-full bg-amber-400/60 animate-pulse"></span>
                Esperando verificación del centro
            </div>
        </div>
    </div>
</template>
