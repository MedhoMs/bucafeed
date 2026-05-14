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
        const socketUrl = import.meta.env.VITE_SOCKET_URL || (isProd ? `//${window.location.hostname}` : `//${window.location.hostname}:3000`);
        
        console.log('Connecting to socket:', socketUrl);
        
        socketInstance = io(socketUrl, {
            transports: ['websocket', 'polling'],
            reconnection: true,
            reconnectionAttempts: 10
        });

        socketInstance.on('connect', () => {
            console.log('Socket connected:', socketInstance.id);
            connected.value = true;
            
            if (userData) {
                socketInstance.emit('user:online', userData);
                socketInstance.userId = userData.id;
            }
        });

        socketInstance.on('disconnect', () => {
            console.log('Socket disconnected');
            connected.value = false;
        });

        socketInstance.on('update-user-list', (users) => {
            console.log('Online users updated:', users.length);
            onlineUsers.value = users;
        });

        // Bridge para eventos genéricos
        socketInstance.onAny((event, ...args) => {
            // console.log(`Socket Event: ${event}`, args);
        });

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

    return {
        socket: socketInstance,
        connected,
        onlineUsers,
        setupSocket,
        emitSocket,
        on: onSocket,
        off: offSocket
    };
}
