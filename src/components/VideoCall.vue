<template>
  <div class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="entry in remoteStreams" :key="entry.id" class="relative">
        <video
          :ref="el => setVideoRef(el, entry.id)"
          autoplay
          playsinline
          muted
          :class="[
            'w-full h-64 bg-black rounded-lg object-cover border-2 border-[#2a4a5a]',
            entry.id === 'me' ? 'mirror' : ''
          ]"
        ></video>
      </div>
    </div>

    <div v-if="cameraError" class="mt-4 p-3 bg-red-500/20 border border-red-500 text-red-200 rounded-lg text-sm text-center">
      {{ cameraError }}
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
const remoteStreams = ref([]);
const peers = {};

let socket = null;
let myPeer = null;
let localStream = null;

const videoElements = {};

function setVideoRef(el, id) {
  if (el) {
    videoElements[id] = el;
    const entry = remoteStreams.value.find(e => e.id === id);
    if (entry && el.srcObject !== entry.stream) {
      el.srcObject = entry.stream;
      el.play().catch(() => {});
    }
  }
}

onMounted(async () => {
  try {
    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    remoteStreams.value.push({ id: 'me', stream: localStream });
  } catch (err) {
    cameraError.value = "No se pudo activar la cámara. Si estás en el móvil, necesitas usar HTTPS o activar las flags de Chrome.";
  }

  const schema = location.protocol === 'https:' ? 'wss:' : 'http:';
  const socketUrl = import.meta.env.VITE_SOCKET_URL || `${schema}//${window.location.hostname}:3000`;
  socket = io(socketUrl);

  myPeer = new Peer();

  myPeer.on('open', id => {
    myPeerId.value = id;
    socket.emit('join-room', roomId.value, id);
  });

  myPeer.on('call', call => {
    if (localStream) {
      call.answer(localStream);
    }
    call.on('stream', userVideoStream => {
      addVideoStream(call.peer, userVideoStream);
    });
  });

  socket.on('user-connected', userId => {
    if (localStream) {
      connectToNewUser(userId, localStream);
    }
  });

  socket.on('user-disconnected', userId => {
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
.mirror {
  transform: scaleX(-1);
}
</style>
