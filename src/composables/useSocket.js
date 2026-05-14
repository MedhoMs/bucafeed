import { io } from 'socket.io-client';
import { ref } from 'vue';

const socket = ref(null);
const connected = ref(false);
const pendingListeners = ref([]);
const pendingEmissions = ref([]);

export function useSocket() {

    function connect(roomId, userData = null) {
        if (socket.value?.connected) {
            if (roomId) joinRoom(roomId, userData);
            return;
        }

        const socketUrl = import.meta.env.VITE_SOCKET_URL || `${location.protocol === 'https:' ? 'wss:' : 'http:'}//${window.location.hostname}:3000`;
        socket.value = io(socketUrl);

        socket.value.on('connect', () => {
            connected.value = true;
            console.log('Socket connected to:', socketUrl);
            
            if (roomId) {
                socket.value.emit('join-room', roomId, userData);
                const userId = typeof roomId === 'string' && roomId.startsWith('user:') ? roomId.slice(5) : null;
                if (userId) {
                    socket.value.emit('user-online', userId);
                }
            }
            
            // Attach pending listeners
            pendingListeners.value.forEach(({ event, callback }) => {
                socket.value.on(event, callback);
            });
            pendingListeners.value = [];

            // Process pending emissions
            pendingEmissions.value.forEach(({ event, roomId, data }) => {
                socket.value.emit(event, roomId, data);
            });
            pendingEmissions.value = [];
        });

        socket.value.on('disconnect', () => {
            connected.value = false;
        });
    }

    function joinRoom(roomId, userData = null) {
        if (socket.value?.connected) {
            socket.value.emit('join-room', roomId, userData);
            socket.value.emit('kahoot:join-room', roomId, userData);
        } else {
            // Si no está conectado, lo conectamos con esta sala
            connect(roomId, userData);
        }
    }

    function leaveRoom(roomId) {
        if (socket.value?.connected) {
            socket.value.emit('leave-room', roomId);
        }
    }

    function emit(event, roomId, data = {}) {
        if (socket.value?.connected) {
            socket.value.emit(event, roomId, data);
        } else {
            pendingEmissions.value.push({ event, roomId, data });
        }
    }

    function on(event, callback) {
        if (socket.value?.connected) {
            socket.value.on(event, callback);
        } else {
            pendingListeners.value.push({ event, callback });
        }
    }

    function off(event, callback) {
        if (socket.value) {
            socket.value.off(event, callback);
        }
    }

    function disconnect() {
        // En modo singleton solemos no desconectar automáticamente al desmontar
        // pero podemos exponer la función para cierres manuales si es necesario.
        if (socket.value) {
            socket.value.disconnect();
            socket.value = null;
            connected.value = false;
        }
    }

    return {
        socket,
        connected,
        connect,
        joinRoom,
        leaveRoom,
        emit,
        on,
        off,
        disconnect,
    };
}
