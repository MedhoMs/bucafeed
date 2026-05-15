import { io } from 'socket.io-client';
import { ref } from 'vue';

// Patrón Singleton para mantener una única conexión estable
let socketInstance = null;
const connected = ref(false);
const onlineUsers = ref([]);

export function useSocket() {
    
    function setupSocket(userData = null) {
        if (socketInstance?.connected) {
            // Si ya estamos conectados pero no hemos enviado el user:online (ej. tras login)
            if (userData && socketInstance.userId !== userData.id) {
                socketInstance.emit('user:online', userData);
                socketInstance.userId = userData.id;
            }
            return socketInstance;
        }

        // Detección de protocolo para producción (HTTPS -> WSS)
        const isProd = window.location.protocol === 'https:';
        const socketUrl = import.meta.env.VITE_SOCKET_URL || import.meta.env.VITE_SIGNALING_URL || (isProd ? `//${window.location.hostname}` : `//${window.location.hostname}:3000`);
        
        socketInstance = io(socketUrl, {
            transports: ['websocket', 'polling'],
            reconnection: true,
            reconnectionAttempts: 10
        });

        socketInstance.on('connect', () => {
            connected.value = true;
            
            if (userData) {
                socketInstance.emit('user:online', userData);
                socketInstance.userId = userData.id;
            }
        });

        socketInstance.on('disconnect', () => {
            connected.value = false;
        });

        socketInstance.on('update-user-list', (users) => {
            onlineUsers.value = users;
        });

        socketInstance.onAny(() => {});

        return socketInstance;
    }

    function emitSocket(event, roomId, data = {}) {
        if (socketInstance?.connected) {
            socketInstance.emit(event, roomId, data);
        } else {
            console.warn('Cannot emit: socket not connected', event);
        }
    }

    function onSocket(event, callback) {
        if (!socketInstance) setupSocket();
        socketInstance.on(event, callback);
    }

    function offSocket(event, callback) {
        if (socketInstance) {
            socketInstance.off(event, callback);
        }
    }

    function joinRoom(roomId) {
        if (socketInstance?.connected) {
            socketInstance.emit('chat:join', roomId);
        } else {
            const waitAndJoin = () => {
                if (socketInstance?.connected) {
                    socketInstance.emit('chat:join', roomId);
                    socketInstance.off('connect', waitAndJoin);
                }
            };
            if (socketInstance) {
                socketInstance.on('connect', waitAndJoin);
            }
        }
    }

    function leaveRoom(roomId) {
        if (socketInstance?.connected) {
            socketInstance.emit('leave-room', roomId);
        }
    }

    function disconnectSocket() {
        if (socketInstance) {
            socketInstance.disconnect();
            socketInstance = null;
            connected.value = false;
        }
    }

    return {
        socket: socketInstance,
        connected,
        onlineUsers,
        setupSocket,
        connect: setupSocket,
        joinRoom,
        leaveRoom,
        disconnect: disconnectSocket,
        emitSocket,
        emit: emitSocket,
        on: onSocket,
        off: offSocket
    };
}
