const express = require('express');
const app = express();
const server = require('http').Server(app);
const io = require('socket.io')(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
});

io.on('connection', socket => {
  console.log('New client connected:', socket.id);

  socket.on('join-room', (roomId, userId) => {
    console.log(`User ${userId} joined room: ${roomId}`);
    socket.join(roomId);
    socket.to(roomId).emit('user-connected', userId);
  });

  socket.on('leave-room', (roomId) => {
    console.log(`Client ${socket.id} left room: ${roomId}`);
    socket.leave(roomId);
  });

  socket.on('send-message', (data) => {
    console.log(`Message in ${data.roomId} from ${data.userId}: ${data.message}`);
    io.to(data.roomId).emit('receive-message', data);
  });

  socket.on('disconnect', () => {
    console.log('Client disconnected:', socket.id);
  });

  // Chat events
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

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Signaling server running on port ${PORT}`);
});
