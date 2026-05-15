<template>
  <div class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="entry in allStreams" :key="entry.id" class="relative">
        <video
          :ref="el => setVideoRef(entry.id, el)"
          autoplay
          playsinline
          :muted="entry.id === 'me'"
          @loadedmetadata="handleVideoPlay"
          :class="[
            'w-full h-64 bg-black rounded-lg object-cover border-2 border-[#2a4a5a]',
            entry.id === 'me' ? 'mirror' : ''
          ]"
        ></video>
        <p v-if="entry.id === 'me'" class="absolute bottom-2 left-2 text-xs bg-black/60 px-2 py-0.5 rounded-full text-white">
          Tú
        </p>
        <p v-else class="absolute bottom-2 left-2 text-xs bg-black/60 px-2 py-0.5 rounded-full text-white">
          {{ connectionStatus }}
        </p>
      </div>
    </div>

    <div v-if="cameraError" class="mt-4 p-3 bg-red-500/20 border border-red-500 text-red-200 rounded-lg text-sm text-center">
      {{ cameraError }}
    </div>

    <div v-if="debugInfo" class="mt-2 p-2 bg-yellow-500/10 border border-yellow-500/30 text-yellow-200 rounded-lg text-[10px] font-mono text-center">
      {{ debugInfo }}
    </div>

    <div class="mt-4 text-white text-center">
      <p class="bg-[#1d2b38] inline-block px-4 py-2 rounded-full font-mono text-xs">
        Sala: {{ videoRoomId }} | Participantes: {{ allStreams.length }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { io } from 'socket.io-client';

const props = defineProps({
    roomId: { type: String, default: 'sala-general' }
});

// Sala exclusiva para video (separada de la sala de chat)
const videoRoomId = `video-${props.roomId}`;

const cameraError = ref('');
const debugInfo = ref('Conectando...');
const connectionStatus = ref('Conectando...');
const allStreams = ref([]);
const peerConnections = {};
const videoRefs = {};

let localStream = null;
let socket = null;

// ICE servers - STUN de Google + TURN gratuitos de Metered
const iceConfig = {
  iceServers: [
    { urls: 'stun:stun.l.google.com:19302' },
    { urls: 'stun:stun1.l.google.com:19302' },
    { urls: 'stun:stun2.l.google.com:19302' },
    { urls: 'stun:stun3.l.google.com:19302' },
    { urls: 'stun:stun4.l.google.com:19302' },
    // TURN servers de Metered (free tier)
    {
      urls: 'turn:a.relay.metered.ca:80',
      username: 'e8dd65b992da0a14e1ae4eb1',
      credential: '5w2JKXAqfCHMqE/d',
    },
    {
      urls: 'turn:a.relay.metered.ca:80?transport=tcp',
      username: 'e8dd65b992da0a14e1ae4eb1',
      credential: '5w2JKXAqfCHMqE/d',
    },
    {
      urls: 'turn:a.relay.metered.ca:443',
      username: 'e8dd65b992da0a14e1ae4eb1',
      credential: '5w2JKXAqfCHMqE/d',
    },
    {
      urls: 'turns:a.relay.metered.ca:443?transport=tcp',
      username: 'e8dd65b992da0a14e1ae4eb1',
      credential: '5w2JKXAqfCHMqE/d',
    },
  ]
};

function setVideoRef(id, el) {
  if (el) {
    videoRefs[id] = el;
    const entry = allStreams.value.find(s => s.id === id);
    if (entry && entry.stream && el.srcObject !== entry.stream) {
      el.srcObject = entry.stream;
    }
  }
}

function handleVideoPlay(e) {
  e.target.play().catch(() => {});
}

function createPeerConnection(targetId) {
  if (peerConnections[targetId]) {
    peerConnections[targetId].close();
    delete peerConnections[targetId];
  }

  console.log('[VideoCall] Creating peer connection to:', targetId);
  const pc = new RTCPeerConnection(iceConfig);
  peerConnections[targetId] = pc;

  if (localStream) {
    localStream.getTracks().forEach(track => {
      pc.addTrack(track, localStream);
    });
  }

  pc.addEventListener('track', event => {
    console.log('[VideoCall] Received remote track from:', targetId);
    const [remoteStream] = event.streams;
    if (remoteStream) {
      const existing = allStreams.value.find(s => s.id === targetId);
      if (existing) {
        existing.stream = remoteStream;
      } else {
        allStreams.value.push({ id: targetId, stream: remoteStream });
      }
      connectionStatus.value = 'Conectado';
      debugInfo.value = '';
      nextTick(() => {
        const videoEl = videoRefs[targetId];
        if (videoEl && videoEl.srcObject !== remoteStream) {
          videoEl.srcObject = remoteStream;
        }
      });
    }
  });

  pc.addEventListener('icecandidate', event => {
    if (event.candidate && socket) {
      socket.emit('ice-candidate', {
        to: targetId,
        candidate: event.candidate
      });
    }
  });

  pc.addEventListener('iceconnectionstatechange', () => {
    console.log('[VideoCall] ICE state with', targetId, ':', pc.iceConnectionState);
    if (pc.iceConnectionState === 'connected' || pc.iceConnectionState === 'completed') {
      connectionStatus.value = 'Conectado';
      debugInfo.value = '';
    } else if (pc.iceConnectionState === 'checking') {
      connectionStatus.value = 'Estableciendo conexión...';
    } else if (pc.iceConnectionState === 'disconnected') {
      connectionStatus.value = 'Desconectado';
    } else if (pc.iceConnectionState === 'failed') {
      connectionStatus.value = 'Conexión fallida';
      debugInfo.value = 'Error: ICE falló. Posible problema de red o firewall.';
      cleanupPeerConnection(targetId);
    }
  });

  return pc;
}

async function createOffer(targetId) {
  try {
    console.log('[VideoCall] Creating offer to:', targetId);
    debugInfo.value = 'Enviando oferta de conexión...';
    const pc = createPeerConnection(targetId);
    const offer = await pc.createOffer();
    await pc.setLocalDescription(offer);
    if (socket) {
      socket.emit('offer', { to: targetId, offer: pc.localDescription });
    }
  } catch (err) {
    console.error('[VideoCall] Error creating offer:', err);
    debugInfo.value = 'Error creando oferta: ' + err.message;
  }
}

async function handleOffer(from, offer) {
  try {
    console.log('[VideoCall] Received offer from:', from);
    debugInfo.value = 'Recibida oferta, respondiendo...';
    const pc = createPeerConnection(from);
    await pc.setRemoteDescription(new RTCSessionDescription(offer));
    const answer = await pc.createAnswer();
    await pc.setLocalDescription(answer);
    if (socket) {
      socket.emit('answer', { to: from, answer: pc.localDescription });
    }
  } catch (err) {
    console.error('[VideoCall] Error handling offer:', err);
    debugInfo.value = 'Error procesando oferta: ' + err.message;
  }
}

async function handleAnswer(from, answer) {
  try {
    console.log('[VideoCall] Received answer from:', from);
    const pc = peerConnections[from];
    if (pc && pc.signalingState === 'have-local-offer') {
      await pc.setRemoteDescription(new RTCSessionDescription(answer));
    }
  } catch (err) {
    console.error('[VideoCall] Error handling answer:', err);
  }
}

async function handleIceCandidate(from, candidate) {
  try {
    const pc = peerConnections[from];
    if (pc) {
      await pc.addIceCandidate(new RTCIceCandidate(candidate));
    }
  } catch (err) {
    console.error('[VideoCall] Error handling ICE candidate:', err);
  }
}

function cleanupPeerConnection(id) {
  if (peerConnections[id]) {
    peerConnections[id].close();
    delete peerConnections[id];
  }
  delete videoRefs[id];
  allStreams.value = allStreams.value.filter(s => s.id !== id);
}

function cleanupAll() {
  Object.keys(peerConnections).forEach(id => {
    peerConnections[id].close();
    delete peerConnections[id];
  });
  allStreams.value = allStreams.value.filter(s => s.id === 'me');
}

onMounted(async () => {
  // 1. Obtener la cámara
  try {
    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    allStreams.value.push({ id: 'me', stream: localStream });
    debugInfo.value = 'Cámara activada. Conectando al servidor...';
  } catch (err) {
    console.error('[VideoCall] Camera error:', err);
    cameraError.value = "No se pudo activar la cámara. Verifica los permisos y que estés usando HTTPS.";
    debugInfo.value = 'Error de cámara: ' + err.message;
  }

  // 2. Crear socket EXCLUSIVO para video (separado del socket del chat)
  // Esto evita conflictos de salas con el chat
  const socketUrl = import.meta.env.VITE_SOCKET_URL 
    || import.meta.env.VITE_SIGNALING_URL 
    || (location.protocol === 'https:' ? `//${window.location.hostname}` : `//${window.location.hostname}:3000`);

  console.log('[VideoCall] Connecting to signaling server:', socketUrl);

  socket = io(socketUrl, {
    transports: ['websocket', 'polling'],
    reconnection: true,
    reconnectionAttempts: 10,
    forceNew: true, // Forzar nueva conexión, NO reutilizar la del chat
  });

  socket.on('connect', () => {
    console.log('[VideoCall] Socket connected, id:', socket.id);
    debugInfo.value = 'Conectado al servidor. Uniéndose a la sala de video...';

    // Unirse a la sala de VIDEO (separada de la de chat)
    // IMPORTANTE: NO creamos ofertas aquí. Solo el que ya estaba en la sala
    // crea la oferta (via evento 'user-joined'). Esto evita que ambos lados
    // creen ofertas simultáneas que se cancelen mutuamente (glare condition).
    socket.emit('join-room', videoRoomId, (existingClients) => {
      console.log('[VideoCall] Joined room. Existing clients:', existingClients);
      debugInfo.value = existingClients?.length
        ? `Encontrados ${existingClients.length} participante(s). Esperando conexión...`
        : 'Esperando a que otro participante se una...';
    });
  });

  socket.on('connect_error', (err) => {
    console.error('[VideoCall] Socket connection error:', err);
    debugInfo.value = 'Error de conexión: ' + err.message;
  });

  // 3. Eventos de señalización WebRTC
  socket.on('user-joined', (clientId) => {
    console.log('[VideoCall] User joined:', clientId);
    debugInfo.value = 'Nuevo participante detectado. Conectando...';
    createOffer(clientId);
  });

  socket.on('offer', ({ from, offer }) => {
    console.log('[VideoCall] Offer received from:', from);
    handleOffer(from, offer);
  });

  socket.on('answer', ({ from, answer }) => {
    console.log('[VideoCall] Answer received from:', from);
    handleAnswer(from, answer);
  });

  socket.on('ice-candidate', ({ from, candidate }) => {
    handleIceCandidate(from, candidate);
  });

  socket.on('user-disconnected', (clientId) => {
    console.log('[VideoCall] User disconnected:', clientId);
    cleanupPeerConnection(clientId);
  });
});

onUnmounted(() => {
  cleanupAll();

  if (socket) {
    socket.emit('leave-room', videoRoomId);
    socket.disconnect();
    socket = null;
  }

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