<template>
  <div class="p-6">
    <div id="video-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <!-- Los videos se renderizan aquí dinámicamente -->
      <div v-for="stream in remoteStreams" :key="stream.id" class="relative">
        <video 
          :srcObject.prop="stream.stream" 
          autoplay 
          playsinline 
          class="w-full h-64 bg-black rounded-lg object-cover border-2 border-[#2a4a5a]"
          :class="{ 'mirror': stream.id === 'me' }"
        ></video>
        <div class="absolute bottom-2 left-2 bg-black/50 text-white text-xs px-2 py-1 rounded">
          {{ stream.id === 'me' ? 'Tú' : 'Usuario' }}
        </div>
      </div>
    </div>

    <div v-if="cameraError" class="mt-4 p-3 bg-red-500/20 border border-red-500 text-red-200 rounded-lg text-sm text-center">
      <p>⚠️ {{ cameraError }}</p>
      <button 
        @click="retryCamera" 
        class="mt-2 bg-red-600 hover:bg-red-700 text-white px-4 py-1 rounded text-xs transition"
      >
        Reintentar Permisos
      </button>
    </div>
    
    <div class="mt-4 text-white text-center">
      <p class="bg-[#1d2b38] inline-block px-4 py-2 rounded-full font-mono text-sm">
        Sala: <span class="text-blue-400 font-bold">{{ roomId }}</span> | Peer ID: <span class="text-emerald-400">{{ myPeerId }}</span>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { io } from 'socket.io-client';
import Peer from 'peerjs';

const route = useRoute();
const roomId = ref(route.params.id || 'sala-general');
const myPeerId = ref('Cargando...');
const cameraError = ref('');
const remoteStreams = ref([]); // Lista de {id, stream}
const connectionStatus = ref('Desconectado');
const peers = {};

let socket = null;
let myPeer = null;
let localStream = null;

// Configuración de URLs
const hostname = window.location.hostname;
const isRailway = hostname.includes('railway.app');

// En producción, forzamos HTTPS/WSS. En local, usamos HTTP/WS.
// IMPORTANTE: En Railway, la variable VITE_SIGNALING_URL debe estar definida.
// Si no está, fallará visiblemente en lugar de inventar una URL incorrecta.
const SIGNALING_URL = import.meta.env.VITE_SIGNALING_URL || 'UNDEFINED_SIGNALING_URL';

async function retryCamera() {
  cameraError.value = '';
  try {
    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    // Si ya existía, no lo duplicamos, pero aquí reiniciamos streams
    const existingMe = remoteStreams.value.find(s => s.id === 'me');
    if (!existingMe) {
      remoteStreams.value.push({ id: 'me', stream: localStream });
    } else {
        existingMe.stream = localStream;
    }
  } catch (err) {
    console.warn('Reintento fallido:', err);
    cameraError.value = `Error: ${err.name}. Revisa la configuración del navegador.`;
  }
}

onMounted(async () => {
  // 1. Obtener media local
  await retryCamera();

  // 2. Conectar a Socket.io
  socket = io(SIGNALING_URL);
  
  socket.on('connect', () => {
    connectionStatus.value = 'Conectado a Socket.io';
  });

  socket.on('connect_error', (err) => {
    connectionStatus.value = `Error: ${err.message}`;
    console.error('Socket Error:', err);
  });

  // 3. Configurar PeerJS
  // Usamos el servidor público de PeerJS para máxima compatibilidad
  // (Sin configuración `host` usa 0.peerjs.com automáticamente)
  myPeer = new Peer(undefined);

  myPeer.on('open', id => {
    myPeerId.value = id;
    socket.emit('join-room', roomId.value, id);
  });

  myPeer.on('error', err => {
     console.error("PeerJS Error:", err);
  });

  // Recibir llamadas
  myPeer.on('call', call => {
    call.answer(localStream);
    call.on('stream', userVideoStream => {
      addVideoStream(call.peer, userVideoStream);
    });
  });

  // Usuario conectado
  socket.on('user-connected', userId => {
    // Esperar un poco para que el otro peer esté listo
    setTimeout(() => connectToNewUser(userId, localStream), 1000);
  });

  // Usuario desconectado
  socket.on('user-disconnected', userId => {
    if (peers[userId]) peers[userId].close();
    remoteStreams.value = remoteStreams.value.filter(s => s.id !== userId);
  });
});

function connectToNewUser(userId, stream) {
  const call = myPeer.call(userId, stream);
  
  call.on('stream', userVideoStream => {
    addVideoStream(userId, userVideoStream);
  });
  
  call.on('close', () => {
    remoteStreams.value = remoteStreams.value.filter(s => s.id !== userId);
  });
  
  peers[userId] = call;
}

function addVideoStream(userId, stream) {
  if (!remoteStreams.value.find(s => s.id === userId)) {
    remoteStreams.value.push({ id: userId, stream });
  }
}

// Hacemos disponible la URL para el template
const signalingUrl = SIGNALING_URL;

onUnmounted(() => {
  if (socket) socket.disconnect();
  if (myPeer) myPeer.destroy();
  if (localStream) {
    localStream.getTracks().forEach(track => track.stop());
  }
});
</script>

<style scoped>
.mirror {
  transform: scaleX(-1);
}
</style>
