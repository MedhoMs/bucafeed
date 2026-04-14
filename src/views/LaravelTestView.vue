<script setup>
import { ref, onMounted } from 'vue';

const message = ref('');
const dbStatus = ref('');
const status = ref('idle'); // idle, loading, success, error
const errorDetails = ref('');
const apiUrl = ref('http://localhost:8000/api/test-connection');

const checkConnection = async () => {
  status.value = 'loading';
  errorDetails.value = '';
  message.value = '';
  dbStatus.value = '';
  
  try {
    console.log('Probando conexión a:', apiUrl.value);
    const response = await fetch(apiUrl.value);
    
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    message.value = data.message;
    dbStatus.value = data.database;
    status.value = 'success';
  } catch (err) {
    status.value = 'error';
    errorDetails.value = err.message || 'Error al conectar con el backend.';
    console.error('Error completo:', err);
  }
};

onMounted(() => {
  checkConnection();
});
</script>

<template>
  <div class="p-8 max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Prueba de Conexión Full-Stack</h1>
    
    <div class="mb-6 bg-white p-4 rounded-lg border shadow-xs">
      <label class="block text-sm font-medium text-gray-700 mb-2">URL del API Backend:</label>
      <div class="flex gap-2">
        <input v-model="apiUrl" type="text" class="flex-1 border rounded-sm px-3 py-2 text-sm" />
        <button @click="checkConnection" :disabled="status === 'loading'" 
                class="bg-blue-600 text-white px-4 py-2 rounded-sm hover:bg-blue-700 disabled:opacity-50">
          {{ status === 'loading' ? 'Probando...' : 'Reintentar' }}
        </button>
      </div>
      <p class="text-xs text-gray-500 mt-2">
        Nota: Si usas Docker, debería ser <code class="bg-gray-100 px-1">http://localhost:8000/api/test-connection</code>
      </p>
    </div>

    <div v-if="status === 'error'" class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-sm mb-6">
      <p class="font-bold">Error de Red:</p>
      <p>{{ errorDetails }}</p>
      
      <div class="mt-6 p-4 bg-red-100 rounded-sm border border-red-200">
        <p class="font-bold text-red-800 mb-2">Comandos sugeridos para el Backend:</p>
        <pre class="bg-black text-green-400 p-3 rounded-sm text-xs overflow-x-auto">
# Instalar dependencias
docker-compose exec backend composer install

# Generar clave de seguridad
docker-compose exec backend php artisan key:generate

# Instalar sistema de API
docker-compose exec backend php artisan install:api --no-interaction

# Limpiar caché
docker-compose exec backend php artisan config:cache</pre>
      </div>
    </div>

    <div v-if="status === 'success'" class="space-y-4">
      <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-sm shadow-xs">
        <h2 class="font-semibold text-blue-800">Respuesta de Laravel:</h2>
        <p class="text-lg mt-1">{{ message }}</p>
      </div>

      <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-sm shadow-xs">
        <h2 class="font-semibold text-green-800">Estado de MySQL:</h2>
        <p class="text-lg mt-1">{{ dbStatus }}</p>
      </div>
    </div>

    <div v-if="status === 'loading'" class="flex flex-col items-center justify-center p-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600 font-medium">Conectando con el servidor...</p>
    </div>
    
    <div class="mt-8 flex gap-4">
      <router-link to="/home" class="text-gray-600 hover:text-gray-900 flex items-center gap-1">
        ← Volver al Inicio
      </router-link>
    </div>
  </div>
</template>