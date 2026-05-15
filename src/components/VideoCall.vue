<template>
  <div class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="entry in remoteStreams" :key="entry.id" class="relative">
        <video
          :srcObject.prop="entry.stream"
          autoplay
          playsinline
          muted
          @loadedmetadata="handleVideoPlay"
          :class="[
            'w-full h-64 bg-black rounded-lg object-cover border-2 border-[#2a4a5a]',
            entry.id === 'me' ? 'mirror' : ''
          ]"
        ></video>
        <p v-if="entry.id !== 'me'" class="absolute bottom-2 left-2 text-xs bg-black/60 px-2 py-0.5 rounded-full">
          Conectado
        </p>
      </div>
    </div>

    <div v-if="cameraError" class="mt-4 p-3 bg-red-500/20 border border-red-500 text-red-200 rounded-lg text-sm text-center">
      {{ cameraError }}
    </div>

    <div class="mt-4 text-white text-center">
      <p class="bg-[#1d2b38] inline-block px-4 py-2 rounded-full font-mono text-xs">
        Sala: {{ roomId }} | Participantes: {{ remoteStreams.length }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { io } from 'socket.io-client';

const props = defineProps({
    roomId: { type: String, default: 'sala-general' }
});

const roomId = ref(props.roomId);
const cameraError = ref('');
const remoteStreams = ref([]);
const peerConnections = {};

let socket = null;
let localStream = null;

const iceServers = {
  iceServers: [
    { urls: 'stun:stun.l.google.com:19302' },
    { urls: 'stun:stun1.l.google.com:19302' },
  ]
};

function handleVideoPlay(e) {
  e.target.play().catch(() => {});
}

function createPeerConnection(targetId, isInitiator) {
  if (peerConnections[targetId]) {
    peerConnections[targetId].close();
    delete peerConnections[targetId];
  }

  const pc = new RTCPeerConnection(iceServers);
  peerConnections[targetId] = pc;

  if (localStream) {
    localStream.getTracks().forEach(track => {
      pc.addTrack(track, localStream);
    });
  }

  pc.addEventListener('track', event => {
    const [remoteStream] = event.streams;
    if (remoteStream && !remoteStreams.value.find(s => s.id === targetId)) {
      remoteStreams.value.push({ id: targetId, stream: remoteStream });
    }
  });

  pc.addEventListener('ice-candidate', event => {
    if (event.candidate && socket) {
      socket.emit('ice-candidate', {
        to: targetId,
        candidate: event.candidate
      });
    }
  });

  pc.addEventListener('ice-connection-statechange', () => {
    if (pc.iceConnectionState === 'disconnected' || pc.iceConnectionState === 'failed') {
      cleanupPeerConnection(targetId);
    }
  });

  return pc;
}

async function createOffer(targetId) {
  try {
    const pc = createPeerConnection(targetId, true);
    const offer = await pc.createOffer();
    await pc.setLocalDescription(offer);
    if (socket) {
      socket.emit('offer', { to: targetId, offer: pc.localDescription });
    }
  } catch (err) {
    console.error('Error creating offer:', err);
  }
}

async function handleOffer(from, offer) {
  try {
    const pc = createPeerConnection(from, false);
    await pc.setRemoteDescription(new RTCSessionDescription(offer));
    const answer = await pc.createAnswer();
    await pc.setLocalDescription(answer);
    if (socket) {
      socket.emit('answer', { to: from, answer: pc.localDescription });
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
  remoteStreams.value = remoteStreams.value.filter(s => s.id !== id);
}

function cleanupAll() {
  Object.keys(peerConnections).forEach(id => {
    peerConnections[id].close();
    delete peerConnections[id];
  });
  remoteStreams.value = remoteStreams.value.filter(s => s.id === 'me');
}

onMounted(async () => {
  try {
    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    remoteStreams.value.push({ id: 'me', stream: localStream });
  } catch (err) {
    cameraError.value = "No se pudo activar la cámara. Si estás en el móvil, necesitas usar HTTPS o activar las flags de Chrome.";
  }

  const schema = location.protocol === 'https:' ? 'wss:' : 'http:';
  const socketUrl = import.meta.env.VITE_SOCKET_URL || import.meta.env.VITE_SIGNALING_URL || `${schema}//${window.location.hostname}:3000`;
  socket = io(socketUrl);

  socket.on('user-joined', clientId => {
    createOffer(clientId);
  });

  socket.on('offer', ({ from, offer }) => {
    handleOffer(from, offer);
  });

  socket.on('answer', ({ from, answer }) => {
    handleAnswer(from, answer);
  });

  socket.on('ice-candidate', ({ from, candidate }) => {
    handleIceCandidate(from, candidate);
  });

  socket.on('user-disconnected', clientId => {
    cleanupPeerConnection(clientId);
  });

  socket.emit('join-room', roomId.value);
});

onUnmounted(() => {
  cleanupAll();
  if (socket) socket.disconnect();
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