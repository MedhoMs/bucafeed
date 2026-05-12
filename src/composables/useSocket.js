import { io } from 'socket.io-client';
import { ref, onUnmounted } from 'vue';

export function useSocket() {
    const socket = ref(null);
    const connected = ref(false);

    function connect(roomId, userData = null) {
        if (socket.value?.connected) return;

        const socketUrl = import.meta.env.VITE_SOCKET_URL || `//${window.location.hostname}:3000`;
        socket.value = io(socketUrl);

        socket.value.on('connect', () => {
            connected.value = true;
            if (roomId) {
                socket.value.emit('kahoot:join-room', roomId, userData);
            }
        });

        socket.value.on('disconnect', () => {
            connected.value = false;
        });
    }

    function joinRoom(roomId, userData = null) {
        if (socket.value?.connected) {
            socket.value.emit('kahoot:join-room', roomId, userData);
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
        }
    }

    function on(event, callback) {
        if (socket.value) {
            socket.value.on(event, callback);
        }
    }

    function off(event, callback) {
        if (socket.value) {
            socket.value.off(event, callback);
        }
    }

    function disconnect() {
        if (socket.value) {
            socket.value.disconnect();
            socket.value = null;
            connected.value = false;
        }
    }

    onUnmounted(() => {
        disconnect();
    });

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
