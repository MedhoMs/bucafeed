FROM mirror.gcr.io/library/node:20-alpine

WORKDIR /app

# Copiar solo los manifiestos primero → cachea deps si el código no cambia
COPY package*.json ./
RUN npm ci

# Copiar el resto del código
COPY . .

EXPOSE 3000

CMD ["node", "server.js"]
