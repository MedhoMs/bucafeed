import { io } from 'socket.io-client';
import { ref, onUnmounted } from 'vue';

export function useSocket() {
    const socket = ref(null);
    const connected = ref(false);

    function connect(roomId, userData = null) {
        if (socket.value?.connected) return;

        const host = window.location.hostname;
        socket.value = io(`http://${host}:3000`);

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
        emit,
        on,
        off,
        disconnect,
    };
}
