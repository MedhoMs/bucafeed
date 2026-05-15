<template>
  <div class="flex flex-col h-full bg-[#0a151b] overflow-hidden relative">
    <!-- Grid de participantes -->
    <div class="flex-1 p-4 overflow-y-auto custom-scrollbar pb-24">
      <div 
        class="grid gap-4 h-full min-h-[300px]"
        :class="[
          allStreams.length === 1 ? 'grid-cols-1' : 
          allStreams.length === 2 ? 'grid-cols-1 md:grid-cols-2' : 
          'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
        ]"
      >
        <div v-for="entry in allStreams" :key="entry.id" class="relative group min-h-[200px]">
          <!-- Video activo -->
          <video
            v-if="!entry.noCamera && entry.stream && entry.stream.getVideoTracks().length > 0"
            :ref="el => setVideoRef(entry.id, el)"
            autoplay
            playsinline
            :muted="entry.id === 'me'"
            @loadedmetadata="handleVideoPlay"
            class="w-full h-full bg-black rounded-2xl object-cover border-2 border-white/5 shadow-2xl transition-all duration-500"
            :class="entry.id === 'me' ? 'mirror' : ''"
          ></video>

          <!-- Avatar de fallback (sin cámara o cargando) -->
          <div
            v-else
            class="w-full h-full bg-gradient-to-br from-[#1a2e3a] to-[#0d181f] rounded-2xl border-2 border-white/5 flex flex-col items-center justify-center gap-4 shadow-2xl overflow-hidden relative"
          >
            <img 
              v-if="entry.avatarUrl" 
              :src="entry.avatarUrl" 
              class="absolute inset-0 w-full h-full object-cover opacity-10 blur-xl scale-110"
            />
            
            <div class="relative">
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-white/5 flex items-center justify-center border border-white/10 p-1 backdrop-blur-md">
                    <img 
                        v-if="entry.avatarUrl" 
                        :src="entry.avatarUrl" 
                        class="w-full h-full rounded-full object-cover shadow-xl"
                    />
                    <div v-else class="w-full h-full rounded-full bg-[#2a4a5a] flex items-center justify-center text-white/40">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10">
                            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                        </svg>
                    </div>
                </div>
                <div v-if="entry.audioMuted" class="absolute -bottom-1 -right-1 bg-red-500 text-white p-1.5 rounded-full border-2 border-[#0a151b] shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><line x1="1" y1="1" x2="23" y2="23" /><path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V4a3 3 0 0 0-5.94-.6" /><path d="M17 16.95A7 7 0 0 1 5 12v-2m14 0v2a7 7 0 0 1-.11 1.23" /><line x1="12" y1="19" x2="12" y2="23" /><line x1="8" y1="23" x2="16" y2="23" /></svg>
                </div>
            </div>

            <div class="text-center z-10 px-4">
                <span class="text-white font-bold text-xs md:text-sm block mb-1 truncate max-w-[150px]">
                    {{ entry.label || (entry.id === 'me' ? 'Tú' : 'Participante') }}
                </span>
                <span class="text-white/40 text-[9px] font-medium uppercase tracking-[0.2em]">
                    {{ entry.stream ? (entry.noCamera ? 'Cámara Apagada' : 'Conectado') : 'Conectando...' }}
                </span>
            </div>
          </div>

          <div class="absolute bottom-3 left-3 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/5 flex items-center gap-2 transition-opacity group-hover:opacity-100 opacity-80">
            <div v-if="entry.id !== 'me'" :class="[isIceConnected(entry.id) ? 'bg-emerald-500' : 'bg-amber-500', 'w-1.5 h-1.5 rounded-full']"></div>
            <span class="text-[9px] font-bold text-white uppercase tracking-wider">
                {{ entry.id === 'me' ? 'Tú' : (entry.label || 'Peer') }}
            </span>
          </div>

          <div v-if="entry.id !== 'me' && usingTurn" class="absolute top-3 right-3 bg-amber-500/90 text-black text-[8px] font-black px-1.5 py-0.5 rounded-lg uppercase tracking-tighter">RELAY</div>
        </div>
      </div>
    </div>

    <!-- Barra de Controles (Flotante) -->
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 z-40 w-max">
        <div class="px-6 py-3 bg-[#1a2e3a]/80 backdrop-blur-2xl border border-white/10 rounded-3xl flex items-center gap-4 shadow-2xl ring-1 ring-white/5">
            <!-- Mic Toggle -->
            <button 
                @click="toggleMic"
                :class="[isMicOn ? 'bg-white/10 text-white hover:bg-white/20' : 'bg-red-500 text-white shadow-lg shadow-red-500/20', 'w-10 h-10 rounded-2xl flex items-center justify-center transition-all active:scale-90 border border-white/10']"
            >
                <svg v-if="isMicOn" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"/><path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V4a3 3 0 0 0-5.94-.6"/><path d="M17 16.95A7 7 0 0 1 5 12v-2m14 0v2a7 7 0 0 1-.11 1.23"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
            </button>

            <!-- Camera Toggle -->
            <button 
                @click="toggleCamera"
                :class="[isCameraOn ? 'bg-white/10 text-white hover:bg-white/20' : 'bg-red-500 text-white shadow-lg shadow-red-500/20', 'w-10 h-10 rounded-2xl flex items-center justify-center transition-all active:scale-90 border border-white/10']"
            >
                <svg v-if="isCameraOn" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"/><path d="M21 21H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h3m3-3h6l2 3h4a2 2 0 0 1 2 2v9.34"/><circle cx="12" cy="13" r="4"/></svg>
            </button>

            <!-- Separador -->
            <div class="w-px h-6 bg-white/10"></div>

            <!-- Hang Up -->
            <button 
                @click="$emit('close')"
                class="bg-[#ff3b30] text-white hover:bg-red-600 px-6 h-10 rounded-2xl flex items-center justify-center transition-all active:scale-95 shadow-lg shadow-red-500/20 border border-white/10 gap-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="rotate-[135deg]"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <span class="text-[10px] font-black uppercase tracking-widest hidden sm:inline">Colgar</span>
            </button>
        </div>
    </div>

    <!-- Overlay de Errores -->
    <div v-if="cameraError" class="absolute top-4 left-1/2 -translate-x-1/2 bg-red-500/90 backdrop-blur-md text-white px-4 py-2 rounded-full text-xs font-bold border border-white/10 flex items-center gap-2 shadow-2xl z-50 animate-bounce">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ cameraError }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { io } from 'socket.io-client';
import { user as authUser } from '@/stores/auth';
import { useApi } from '@/composables/useApi';

const props = defineProps({
    roomId: { type: String, default: 'sala-general' }
});

defineEmits(['close']);

const { get } = useApi();
const videoRoomId = `video-${props.roomId}`;

const cameraError = ref('');
const debugInfo = ref('');
const connectionStatus = ref('Esperando...');
const allStreams = ref([]);
const peerConnections = {};
const videoRefs = {};
const mySocketId = ref('');
const usingTurn = ref(false);

const isMicOn = ref(true);
const isCameraOn = ref(true);

let localStream = null;
let socket = null;
let turnServers = null;

const stunOnlyServers = [
  { urls: 'stun:stun.l.google.com:19302' },
  { urls: 'stun:stun1.l.google.com:19302' },
  { urls: 'stun:stun2.l.google.com:19302' },
  { urls: 'stun:stun3.l.google.com:19302' },
  { urls: 'stun:stun4.l.google.com:19302' },
];

function getAvatarFullUrl(path) {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    const baseSrc = import.meta.env.VITE_BACKEND_URL || 'http://localhost:8001';
    return baseSrc + (path.startsWith('/') ? '' : '/') + path;
}

async function loadTurnServers() {
  if (turnServers !== null) return turnServers;
  try {
    const data = await get('ice-servers');
    turnServers = Array.isArray(data) && data.length > 0 ? [...stunOnlyServers, ...data] : stunOnlyServers;
  } catch (err) {
    turnServers = stunOnlyServers;
  }
  return turnServers;
}

function setVideoRef(id, el) {
  if (el) {
    videoRefs[id] = el;
    const entry = allStreams.value.find(s => s.id === id);
    if (entry && entry.stream && el.srcObject !== entry.stream) {
      el.srcObject = entry.stream;
    }
  }
}

function handleVideoPlay(e) { e.target.play().catch(() => {}); }

function toggleMic() {
    if (!localStream) return;
    isMicOn.value = !isMicOn.value;
    localStream.getAudioTracks().forEach(track => track.enabled = isMicOn.value);
    
    // Notificar a otros (vía socket) que hemos silenciado
    if (socket) {
        socket.emit('media-state-changed', { 
            roomId: videoRoomId, 
            audioMuted: !isMicOn.value 
        });
    }
    
    // Actualizar mi propia entrada
    const me = allStreams.value.find(s => s.id === 'me');
    if (me) me.audioMuted = !isMicOn.value;
}

function toggleCamera() {
    if (!localStream) return;
    isCameraOn.value = !isCameraOn.value;
    localStream.getVideoTracks().forEach(track => track.enabled = isCameraOn.value);
    
    const me = allStreams.value.find(s => s.id === 'me');
    if (me) me.noCamera = !isCameraOn.value;
}

function createPeerConnection(targetId, iceServerList) {
  if (peerConnections[targetId]) {
    peerConnections[targetId].close();
    delete peerConnections[targetId];
  }

  const pc = new RTCPeerConnection({ iceServers: iceServerList });
  peerConnections[targetId] = pc;

  if (localStream) {
    localStream.getTracks().forEach(track => pc.addTrack(track, localStream));
  }

  pc.addEventListener('track', event => {
    const [remoteStream] = event.streams;
    if (remoteStream) {
      const existing = allStreams.value.find(s => s.id === targetId);
      if (existing) {
        existing.stream = remoteStream;
      } else {
        allStreams.value.push({ 
            id: targetId, 
            stream: remoteStream, 
            noCamera: remoteStream.getVideoTracks().length === 0, 
            label: 'Participante',
            avatarUrl: null
        });
      }
      connectionStatus.value = 'Conectado';
    }
  });

  pc.addEventListener('icecandidate', event => {
    if (event.candidate && socket) {
      socket.emit('ice-candidate', { to: targetId, candidate: event.candidate });
    }
  });

  pc.addEventListener('iceconnectionstatechange', async () => {
    if (pc.iceConnectionState === 'failed') {
      const fullServers = await loadTurnServers();
      const hasTurn = fullServers.some(s => s.urls?.toString().includes('turn:'));
      if (!hasTurn) {
        cleanupPeerConnection(targetId);
        return;
      }
      usingTurn.value = true;
      const newPc = createPeerConnection(targetId, fullServers);
      const offer = await newPc.createOffer({ iceRestart: true });
      await newPc.setLocalDescription(offer);
      if (socket) socket.emit('offer', { to: targetId, offer: newPc.localDescription });
    }
  });

  return pc;
}

function isIceConnected(id) {
    const pc = peerConnections[id];
    return pc && (pc.iceConnectionState === 'connected' || pc.iceConnectionState === 'completed');
}

onMounted(async () => {
  const myLabel = authUser.value ? `${authUser.value.name || ''} ${authUser.value.last_name || ''}`.trim() : 'Yo';
  const myAvatar = getAvatarFullUrl(authUser.value?.profile_picture);

  try {
    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    isCameraOn.value = true;
    isMicOn.value = true;
  } catch (err) {
    try {
      localStream = await navigator.mediaDevices.getUserMedia({ video: false, audio: true });
    } catch {
      localStream = null;
    }
    cameraError.value = 'Sin acceso a cámara';
    isCameraOn.value = false;
  }

  allStreams.value.push({ 
    id: 'me', 
    stream: localStream, 
    noCamera: !isCameraOn.value, 
    label: myLabel,
    avatarUrl: myAvatar,
    audioMuted: false
  });

  const socketUrl = import.meta.env.VITE_SOCKET_URL || (location.protocol === 'https:' ? `//${window.location.hostname}` : `//${window.location.hostname}:3000`);
  socket = io(socketUrl, { transports: ['websocket', 'polling'], forceNew: true });

  socket.on('connect', () => {
    mySocketId.value = socket.id;
    socket.emit('join-room', videoRoomId, (existingClients) => {
        // Enviar mi info (nombre y avatar) al entrar
        socket.emit('user-info', { 
            roomId: videoRoomId, 
            name: myLabel, 
            avatar: myAvatar 
        });
    });
  });

  socket.on('user-info', (data) => {
      const entry = allStreams.value.find(s => s.id === data.from);
      if (entry) {
          entry.label = data.name;
          entry.avatarUrl = data.avatar;
      }
  });

  socket.on('media-state-changed', (data) => {
      const entry = allStreams.value.find(s => s.id === data.from);
      if (entry) {
          entry.audioMuted = data.audioMuted;
      }
  });

  socket.on('user-joined', (clientId) => {
    if (!allStreams.value.find(s => s.id === clientId)) {
      allStreams.value.push({ id: clientId, stream: null, noCamera: true, label: 'Cargando...', avatarUrl: null });
    }
    // Re-enviar mi info al nuevo que llega
    socket.emit('user-info', { roomId: videoRoomId, name: myLabel, avatar: myAvatar });
    const pc = createPeerConnection(clientId, stunOnlyServers);
    pc.createOffer().then(offer => pc.setLocalDescription(offer)).then(() => {
        socket.emit('offer', { to: clientId, offer: pc.localDescription });
    });
  });

  socket.on('offer', async ({ from, offer }) => {
    if (!allStreams.value.find(s => s.id === from)) {
        allStreams.value.push({ id: from, stream: null, noCamera: true, label: 'Participante', avatarUrl: null });
    }
    const pc = createPeerConnection(from, stunOnlyServers);
    await pc.setRemoteDescription(new RTCSessionDescription(offer));
    const answer = await pc.createAnswer();
    await pc.setLocalDescription(answer);
    socket.emit('answer', { to: from, answer: pc.localDescription });
  });

  socket.on('answer', ({ from, answer }) => {
    const pc = peerConnections[from];
    if (pc) pc.setRemoteDescription(new RTCSessionDescription(answer));
  });

  socket.on('ice-candidate', ({ from, candidate }) => {
    const pc = peerConnections[from];
    if (pc) pc.addIceCandidate(new RTCIceCandidate(candidate));
  });

  socket.on('user-disconnected', (clientId) => cleanupPeerConnection(clientId));
});

function cleanupPeerConnection(id) {
  if (peerConnections[id]) peerConnections[id].close();
  delete peerConnections[id];
  allStreams.value = allStreams.value.filter(s => s.id !== id);
}

onUnmounted(() => {
  Object.keys(peerConnections).forEach(cleanupPeerConnection);
  if (socket) {
    socket.emit('leave-room', videoRoomId);
    socket.disconnect();
  }
  if (localStream) localStream.getTracks().forEach(t => t.stop());
});
</script>

<style scoped>
.mirror { transform: scaleX(-1); }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
</style>