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
          Conectado
        </p>
      </div>
    </div>

    <div v-if="cameraError" class="mt-4 p-3 bg-red-500/20 border border-red-500 text-red-200 rounded-lg text-sm text-center">
      {{ cameraError }}
    </div>

    <div class="mt-4 text-white text-center">
      <p class="bg-[#1d2b38] inline-block px-4 py-2 rounded-full font-mono text-xs">
        Sala: {{ roomId }} | Participantes: {{ allStreams.length }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { useSocket } from '@/composables/useSocket';

const props = defineProps({
    roomId: { type: String, default: 'sala-general' }
});

const { connect: connectSocket, on: onSocket, off: offSocket, joinRoom, leaveRoom } = useSocket();

const cameraError = ref('');
const allStreams = ref([]);
const peerConnections = {};
const videoRefs = {};

let localStream = null;
let rawSocket = null;

// ICE servers con TURN para producción (STUN solo no funciona detrás de NAT restrictivos)
const iceConfig = {
  iceServers: [
    { urls: 'stun:stun.l.google.com:19302' },
    { urls: 'stun:stun1.l.google.com:19302' },
    // TURN servers gratuitos de Open Relay (metered.ca)
    {
      urls: 'turn:openrelay.metered.ca:80',
      username: 'openrelayproject',
      credential: 'openrelayproject',
    },
    {
      urls: 'turn:openrelay.metered.ca:443',
      username: 'openrelayproject',
      credential: 'openrelayproject',
    },
    {
      urls: 'turn:openrelay.metered.ca:443?transport=tcp',
      username: 'openrelayproject',
      credential: 'openrelayproject',
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

  const pc = new RTCPeerConnection(iceConfig);
  peerConnections[targetId] = pc;

  if (localStream) {
    localStream.getTracks().forEach(track => {
      pc.addTrack(track, localStream);
    });
  }

  pc.addEventListener('track', event => {
    const [remoteStream] = event.streams;
    if (remoteStream) {
      const existing = allStreams.value.find(s => s.id === targetId);
      if (existing) {
        existing.stream = remoteStream;
      } else {
        allStreams.value.push({ id: targetId, stream: remoteStream });
      }
      // Asignar el stream al elemento de video
      nextTick(() => {
        const videoEl = videoRefs[targetId];
        if (videoEl && videoEl.srcObject !== remoteStream) {
          videoEl.srcObject = remoteStream;
        }
      });
    }
  });

  pc.addEventListener('icecandidate', event => {
    if (event.candidate && rawSocket) {
      rawSocket.emit('ice-candidate', {
        to: targetId,
        candidate: event.candidate
      });
    }
  });

  pc.addEventListener('iceconnectionstatechange', () => {
    if (pc.iceConnectionState === 'disconnected' || pc.iceConnectionState === 'failed') {
      cleanupPeerConnection(targetId);
    }
  });

  return pc;
}

async function createOffer(targetId) {
  try {
    const pc = createPeerConnection(targetId);
    const offer = await pc.createOffer();
    await pc.setLocalDescription(offer);
    if (rawSocket) {
      rawSocket.emit('offer', { to: targetId, offer: pc.localDescription });
    }
  } catch (err) {
    console.error('Error creating offer:', err);
  }
}

async function handleOffer(from, offer) {
  try {
    const pc = createPeerConnection(from);
    await pc.setRemoteDescription(new RTCSessionDescription(offer));
    const answer = await pc.createAnswer();
    await pc.setLocalDescription(answer);
    if (rawSocket) {
      rawSocket.emit('answer', { to: from, answer: pc.localDescription });
    }
  } catch (err) {
    console.error('Error handling offer:', err);
  }
}

async function handleAnswer(from, answer) {
  try {
    const pc = peerConnections[from];
    if (pc && pc.signalingState === 'have-local-offer') {
      await pc.setRemoteDescription(new RTCSessionDescription(answer));
    }
  } catch (err) {
    console.error('Error handling answer:', err);
  }
}

async function handleIceCandidate(from, candidate) {
  try {
    const pc = peerConnections[from];
    if (pc) {
      await pc.addIceCandidate(new RTCIceCandidate(candidate));
    }
  } catch (err) {
    console.error('Error handling ICE candidate:', err);
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

// Event handlers for socket
function onUserJoined(clientId) {
  createOffer(clientId);
}

function onOffer({ from, offer }) {
  handleOffer(from, offer);
}

function onAnswer({ from, answer }) {
  handleAnswer(from, answer);
}

function onIceCandidate({ from, candidate }) {
  handleIceCandidate(from, candidate);
}

function onUserDisconnected(clientId) {
  cleanupPeerConnection(clientId);
}

onMounted(async () => {
  // 1. Obtener la cámara
  try {
    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    allStreams.value.push({ id: 'me', stream: localStream });
  } catch (err) {
    cameraError.value = "No se pudo activar la cámara. Si estás en el móvil, necesitas usar HTTPS o activar las flags de Chrome.";
  }

  // 2. Conectar al socket (usa el singleton del composable)
  rawSocket = connectSocket();

  // 3. Registrar los listeners de WebRTC
  onSocket('user-joined', onUserJoined);
  onSocket('offer', onOffer);
  onSocket('answer', onAnswer);
  onSocket('ice-candidate', onIceCandidate);
  onSocket('user-disconnected', onUserDisconnected);

  // 4. Unirse a la sala de videollamada
  joinRoom(props.roomId);
});

onUnmounted(() => {
  // Limpiar listeners
  offSocket('user-joined', onUserJoined);
  offSocket('offer', onOffer);
  offSocket('answer', onAnswer);
  offSocket('ice-candidate', onIceCandidate);
  offSocket('user-disconnected', onUserDisconnected);

  // Limpiar conexiones
  cleanupAll();
  leaveRoom(props.roomId);

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