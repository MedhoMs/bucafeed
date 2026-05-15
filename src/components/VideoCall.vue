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
            v-show="!entry.noCamera"
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
            v-show="entry.noCamera"
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
import { Room, RoomEvent, VideoPresets, createLocalTracks } from 'livekit-client';
import { user as authUser } from '@/stores/auth';
import { useApi } from '@/composables/useApi';

const props = defineProps({
    roomId: { type: String, default: 'sala-general' }
});

const emit = defineEmits(['close']);

const { post } = useApi();
const videoRoomId = `video-${props.roomId}`;

const cameraError = ref('');
const connectionStatus = ref('Conectando...');
const allStreams = ref([]);
const videoRefs = {};
const usingTurn = ref(false); // Mantener para compatibilidad visual si se requiere, aunque LiveKit usa TURN automáticamente

const isMicOn = ref(true);
const isCameraOn = ref(true);

let currentRoom = null;
let localTracks = [];

function getAvatarFullUrl(path) {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    const baseSrc = import.meta.env.VITE_BACKEND_URL || 'http://localhost:8001';
    return baseSrc + (path.startsWith('/') ? '' : '/') + path;
}

function setVideoRef(id, el) {
  if (el) {
    videoRefs[id] = el;
    // Si la referencia del DOM acaba de montarse, forzamos que LiveKit asigne los tracks correspondientes
    if (currentRoom) {
        const isMe = id === 'me';
        if (isMe) {
            localTracks.forEach(t => t.attach(el));
        } else {
            const participant = currentRoom.remoteParticipants.get(id);
            if (participant) {
                participant.trackPublications.forEach(p => {
                    if (p.track) p.track.attach(el);
                });
            }
        }
    }
  }
}

function handleVideoPlay(e) { e.target.play().catch(() => {}); }

async function toggleMic() {
    isMicOn.value = !isMicOn.value;
    if (currentRoom && currentRoom.localParticipant) {
        await currentRoom.localParticipant.setMicrophoneEnabled(isMicOn.value);
    }
    const me = allStreams.value.find(s => s.id === 'me');
    if (me) me.audioMuted = !isMicOn.value;
}

async function toggleCamera() {
    isCameraOn.value = !isCameraOn.value;
    if (currentRoom && currentRoom.localParticipant) {
        await currentRoom.localParticipant.setCameraEnabled(isCameraOn.value);
    }
    const me = allStreams.value.find(s => s.id === 'me');
    if (me) me.noCamera = !isCameraOn.value;
}

function updateParticipantState(participant) {
    const isMe = participant.isLocal;
    const id = isMe ? 'me' : participant.identity;
    
    let avatarUrl = null;
    try {
        if (participant.metadata) {
            const meta = JSON.parse(participant.metadata);
            avatarUrl = meta.avatar;
        }
    } catch (e) {}

    const existing = allStreams.value.find(s => s.id === id);
    
    const audioMuted = !participant.isMicrophoneEnabled;
    const noCamera = !participant.isCameraEnabled;

    if (existing) {
        existing.label = participant.name || existing.label;
        if (avatarUrl) existing.avatarUrl = avatarUrl;
        existing.noCamera = noCamera;
        existing.audioMuted = audioMuted;
    } else {
        allStreams.value.push({
            id,
            stream: null,
            noCamera,
            label: participant.name || (isMe ? 'Tú' : 'Participante'),
            avatarUrl: avatarUrl || (isMe ? getAvatarFullUrl(authUser.value?.profile_picture) : null),
            audioMuted
        });
    }

    nextTick(() => {
        const el = videoRefs[id];
        if (el) {
            if (isMe) {
                localTracks.forEach(t => {
                    t.attach(el);
                });
            } else {
                participant.trackPublications.forEach(publication => {
                    if (publication.track) {
                        publication.track.attach(el);
                    }
                });
            }
        }
    });
}

function isIceConnected(id) {
    if (id === 'me') return true;
    return currentRoom && currentRoom.state === 'connected';
}

onMounted(async () => {
    const myLabel = authUser.value ? `${authUser.value.name || ''} ${authUser.value.last_name || ''}`.trim() : 'Yo';
    const myAvatar = getAvatarFullUrl(authUser.value?.profile_picture);

    allStreams.value.push({ 
        id: 'me', 
        stream: null, 
        noCamera: false, 
        label: myLabel,
        avatarUrl: myAvatar,
        audioMuted: false
    });

    try {
        localTracks = await createLocalTracks({
            audio: true,
            video: { resolution: VideoPresets.h720.resolution }
        });
        const stream = new MediaStream(localTracks.map(t => t.mediaStreamTrack));
        const me = allStreams.value.find(s => s.id === 'me');
        if (me) me.stream = stream;
    } catch (err) {
        console.error("Camera error:", err);
        cameraError.value = 'Sin acceso a cámara';
        isCameraOn.value = false;
        try {
            localTracks = await createLocalTracks({ audio: true, video: false });
            const stream = new MediaStream(localTracks.map(t => t.mediaStreamTrack));
            const me = allStreams.value.find(s => s.id === 'me');
            if (me) {
                me.stream = stream;
                me.noCamera = true;
            }
        } catch (e) {
            console.error("Mic error:", e);
        }
    }

    try {
        const { token } = await post('livekit/token', { room: videoRoomId });
        const livekitUrl = import.meta.env.VITE_LIVEKIT_URL;

        if (!livekitUrl || !token) {
            throw new Error('Configuración de LiveKit faltante');
        }

        currentRoom = new Room({
            adaptiveStream: true,
            dynacast: true
        });

        currentRoom
            .on(RoomEvent.Connected, () => {
                connectionStatus.value = 'Conectado';
                console.log('[LiveKit] Conectado a la sala');
                usingTurn.value = false;
            })
            .on(RoomEvent.ParticipantConnected, (participant) => {
                console.log('[LiveKit] Participante unido:', participant.identity);
                updateParticipantState(participant);
            })
            .on(RoomEvent.ParticipantDisconnected, (participant) => {
                console.log('[LiveKit] Participante desconectado:', participant.identity);
                allStreams.value = allStreams.value.filter(s => s.id !== participant.identity);
            })
            .on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
                updateParticipantState(participant);
            })
            .on(RoomEvent.TrackUnsubscribed, (track, publication, participant) => {
                updateParticipantState(participant);
            })
            .on(RoomEvent.TrackMuted, (publication, participant) => {
                updateParticipantState(participant);
            })
            .on(RoomEvent.TrackUnmuted, (publication, participant) => {
                updateParticipantState(participant);
            })
            .on(RoomEvent.LocalTrackPublished, () => {
                updateParticipantState(currentRoom.localParticipant);
            })
            .on(RoomEvent.Disconnected, () => {
                console.log('[LiveKit] Desconectado');
                connectionStatus.value = 'Desconectado';
            });

        await currentRoom.connect(livekitUrl, token);
        
        for (const track of localTracks) {
            await currentRoom.localParticipant.publishTrack(track);
        }
        
        if (!isCameraOn.value) await currentRoom.localParticipant.setCameraEnabled(false);
        if (!isMicOn.value) await currentRoom.localParticipant.setMicrophoneEnabled(false);

    } catch (err) {
        console.error('[LiveKit] Error:', err);
        cameraError.value = 'Error al conectar a la llamada';
    }
});

onUnmounted(() => {
    if (currentRoom) {
        currentRoom.disconnect();
    }
    localTracks.forEach(track => track.stop());
});
</script>
<style scoped>
.mirror { transform: scaleX(-1); }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
</style>