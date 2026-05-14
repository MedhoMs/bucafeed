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
        ></video>
      </div>
    </div>

    <div v-if="cameraError" class="mt-4 p-3 bg-red-500/20 border border-red-500 text-red-200 rounded-lg text-sm text-center">
      ⚠️ {{ cameraError }}
    </div>
    
    <div class="mt-4 text-white text-center">
      <p class="bg-[#1d2b38] inline-block px-4 py-2 rounded-full font-mono">
        Sala: {{ roomId }} | Mi Peer ID: {{ myPeerId }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { io } from 'socket.io-client';
import { Peer } from 'peerjs';

const props = defineProps({
    roomId: { type: String, default: 'sala-general' }
});

const roomId = ref(props.roomId);
const myPeerId = ref('');
const cameraError = ref('');
const remoteStreams = ref([]); // Lista de {id, stream}
const peers = {}; // Referencias a las llamadas activas

let socket = null;
let myPeer = null;
let localStream = null;

onMounted(async () => {
  // 1. Obtener media local
  try {
    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    // Añadir mi propio video a la lista
    remoteStreams.value.push({ id: 'me', stream: localStream });
  } catch (err) {
    console.warn('Cámara no encontrada:', err);
    cameraError.value = "No se pudo activar la cámara. Si estás en el móvil, necesitas usar HTTPS o activar las flags de Chrome.";
  }

  // 2. Conectar a Socket.io (servidor de señales)
  // Usamos el host actual para que funcione desde el móvil
  const host = window.location.hostname;
  socket = io(`http://${host}:3000`);

  // 3. Configurar PeerJS
  myPeer = new Peer();

  myPeer.on('open', id => {
    myPeerId.value = id;
    socket.emit('join-room', roomId.value, id);
  });

  // Responder llamadas entrantes
  myPeer.on('call', call => {
    call.answer(localStream);
    call.on('stream', userVideoStream => {
      addVideoStream(call.peer, userVideoStream);
    });
  });

  // Cuando un nuevo usuario se conecta
  socket.on('user-connected', userId => {
    console.log('Usuario conectado:', userId);
    connectToNewUser(userId, localStream);
  });

  // Cuando un usuario se desconecta
  socket.on('user-disconnected', userId => {
    console.log('Usuario desconectado:', userId);
    if (peers[userId]) {
      peers[userId].close();
      remoteStreams.value = remoteStreams.value.filter(s => s.id !== userId);
    }
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
  // Evitar duplicados
  if (!remoteStreams.value.find(s => s.id === userId)) {
    remoteStreams.value.push({ id: userId, stream });
  }
}

onUnmounted(() => {
  if (socket) socket.disconnect();
  if (myPeer) myPeer.destroy();
  if (localStream) {
    localStream.getTracks().forEach(track => track.stop());
  }
});
</script>

<style scoped>
.video-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}
</style>
