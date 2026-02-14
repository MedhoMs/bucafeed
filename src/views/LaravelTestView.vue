<script setup>
import { onMounted, ref } from 'vue';

const status = ref('Cargando...');
const backendUrl = import.meta.env.VITE_API_URL || 'http://localhost:8001/api';

onMounted(async () => {
    try {
        const response = await fetch(`${backendUrl}/test-connection`);
        const data = await response.json();
        status.value = data.status === 'success' ? 'Conectado a la API' : 'Error de respuesta';
    } catch (e) {
        status.value = 'No se pudo conectar con el Backend';
    }
});
</script>

<template>
    <div class="flex flex-col items-center justify-center min-h-screen bg-slate-900 text-white p-6 text-center">
        <div class="max-w-xl w-full bg-slate-800 p-8 rounded-2xl border border-slate-700 shadow-xl">
            <h1 class="text-4xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-emerald-400">
                Prueba de Integración Laravel
            </h1>
            <p class="text-slate-400 mb-6 font-medium">
                Esta vista comprueba que el frontend de Vue puede comunicarse con el backend de Laravel.
            </p>
            
            <div :class="['p-4 rounded-xl border text-lg font-bold transition-all duration-300', 
                status.includes('Conectado') ? 'bg-emerald-500/10 border-emerald-500 text-emerald-400 shadow-[0_0_20px_rgba(16,185,129,0.1)]' : 'bg-red-500/10 border-red-500 text-red-400']">
                Estado: {{ status }}
            </div>

            <div class="mt-8 flex flex-col gap-4">
                <router-link to="/home" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 font-bold rounded-xl transition-all shadow-lg hover:shadow-blue-500/20">
                    Volver al Inicio
                </router-link>
            </div>
        </div>
    </div>
</template>
