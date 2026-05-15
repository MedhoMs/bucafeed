const express = require('express');
const app = express();
app.use(express.json());
const server = require('http').Server(app);
const io = require('socket.io')(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
});

// userId → { userData, sockets: Set<socketId> }
const onlineUsers = new Map();

io.on('connection', socket => {
  console.log('New client connected:', socket.id);

  socket.on('user:online', (userData) => {
    if (!userData || !userData.id) return;
    
    const userId = Number(userData.id);
    if (!onlineUsers.has(userId)) {
        onlineUsers.set(userId, {
            userData: userData,
            sockets: new Set()
        });
    }
    onlineUsers.get(userId).sockets.add(socket.id);
    socket.userId = userId;
    
    console.log(`User ${userData.name} is now online (Sockets: ${onlineUsers.get(userId).sockets.size})`);
    
    // Notificar a todos la lista actualizada
    broadcastUserList();
  });

  socket.on('join-room', (roomId, callback) => {
    socket.join(roomId);
    const room = io.sockets.adapter.rooms.get(roomId);
    const existingClients = room ? Array.from(room).filter(id => id !== socket.id) : [];
    socket.to(roomId).emit('user-joined', socket.id);
    if (typeof callback === 'function') {
      callback(existingClients);
    }
  });

  socket.on('leave-room', (roomId) => {
    console.log(`Client ${socket.id} left room: ${roomId}`);
    socket.leave(roomId);
  });

  // WebRTC signaling
  socket.on('offer', ({ to, offer }) => {
    socket.to(to).emit('offer', { from: socket.id, offer });
  });

  socket.on('answer', ({ to, answer }) => {
    socket.to(to).emit('answer', { from: socket.id, answer });
  });

  socket.on('ice-candidate', ({ to, candidate }) => {
    socket.to(to).emit('ice-candidate', { from: socket.id, candidate });
  });

  // Compartir información de usuario (nombre, avatar)
  socket.on('user-info', (data) => {
    if (data.roomId) {
      socket.to(data.roomId).emit('user-info', {
        from: socket.id,
        name: data.name,
        avatar: data.avatar
      });
    }
  });

  // Notificar cambios de estado (micro silenciado, etc)
  socket.on('media-state-changed', (data) => {
    if (data.roomId) {
      socket.to(data.roomId).emit('media-state-changed', {
        from: socket.id,
        audioMuted: data.audioMuted
      });
    }
  });

  socket.on('disconnect', () => {
    for (const room of socket.rooms) {
      if (room !== socket.id) {
        socket.to(room).emit('user-disconnected', socket.id);
      }
    }
    if (socket.userId && onlineUsers.has(socket.userId)) {
      const userEntry = onlineUsers.get(socket.userId);
      userEntry.sockets.delete(socket.id);
      
      if (userEntry.sockets.size === 0) {
        onlineUsers.delete(socket.userId);
      }
      broadcastUserList();
    }
  });

  // Chat events
  socket.on('chat:join', (roomId) => {
    socket.join(roomId);
  });

  socket.on('chat:message', (roomId, data) => {
    socket.to(roomId).emit('chat:message', data);
  });

  // Kahoot events
  socket.on('kahoot:join-room', (roomId, userData) => {
    socket.join(roomId);
    socket.to(roomId).emit('kahoot:player-joined', userData);
  });

  socket.on('kahoot:started', (roomId, data) => {
    socket.to(roomId).emit('kahoot:started', data);
  });

  socket.on('kahoot:question', (roomId, data) => {
    socket.to(roomId).emit('kahoot:question', data);
  });

  socket.on('kahoot:player-answered', (roomId, data) => {
    socket.to(roomId).emit('kahoot:player-answered', data);
  });

  socket.on('kahoot:reveal', (roomId, data) => {
    socket.to(roomId).emit('kahoot:reveal', data);
  });

  socket.on('kahoot:ended', (roomId, data) => {
    socket.to(roomId).emit('kahoot:ended', data);
  });

  socket.on('kahoot:next-question', (roomId, data) => {
    socket.to(roomId).emit('kahoot:next-question', data);
  });
});

function broadcastUserList() {
    const list = Array.from(onlineUsers.values()).map(entry => entry.userData);
    io.emit('update-user-list', list);
}

// Endpoint para notificaciones en tiempo real (llamado desde Laravel)
app.post('/notify', (req, res) => {
  const { userId, notification } = req.body;
  if (!userId || !notification) {
    return res.status(400).json({ error: 'Missing userId or notification' });
  }
  
  const userEntry = onlineUsers.get(Number(userId));
  if (userEntry) {
      userEntry.sockets.forEach(socketId => {
          io.to(socketId).emit('notification', notification);
      });
      res.json({ ok: true, delivered: userEntry.sockets.size });
  } else {
      res.status(404).json({ ok: false, message: 'User not online' });
  }
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Signaling server running on port ${PORT}`);
});
