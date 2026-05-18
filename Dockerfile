# ═══════════════════════════════════════════════════════════════════════
# TelamoNet - Unified Dockerfile
#
# Stages:
#   development  →  npm run dev (Vite HMR)  — usado por docker-compose
#   production   →  npm build + Nginx       — usado por Railway
#
# Uso local (docker-compose usa `target: development` automáticamente):
#   docker-compose up
#
# Uso Railway (builds el último stage = production):
#   Configura las VITE_* como Build Variables en Railway Settings
# ═══════════════════════════════════════════════════════════════════════

# ── Stage 0: Dependencias compartidas ────────────────────────────────
FROM mirror.gcr.io/library/node:20-alpine AS base
WORKDIR /app
RUN apk add --no-cache python3 make g++
COPY package*.json ./
RUN npm install

# ── Stage 1: Desarrollo con HMR (docker-compose) ─────────────────────
FROM base AS development
COPY . .
EXPOSE 5173
CMD ["sh", "-c", "npm install && npm run dev -- --host 0.0.0.0"]

# ── Stage 2: Builder de producción ───────────────────────────────────
FROM base AS builder

ARG VITE_API_URL
ARG VITE_SOCKET_URL
ARG VITE_SIGNALING_URL
ARG VITE_BACKEND_URL
ARG VITE_UMAMI_ID
ARG VITE_LIVEKIT_URL
ARG VITE_APP_NAME
ENV VITE_API_URL=$VITE_API_URL
ENV VITE_SOCKET_URL=$VITE_SOCKET_URL
ENV VITE_SIGNALING_URL=$VITE_SIGNALING_URL
ENV VITE_BACKEND_URL=$VITE_BACKEND_URL
ENV VITE_UMAMI_ID=$VITE_UMAMI_ID
ENV VITE_LIVEKIT_URL=$VITE_LIVEKIT_URL
ENV VITE_APP_NAME=$VITE_APP_NAME
COPY . .
RUN npm run build

# ── Stage 3: Producción con Nginx ──────
FROM mirror.gcr.io/library/nginx:stable-alpine AS production
COPY --from=builder /app/dist /usr/share/nginx/html
COPY nginx.railway.conf /etc/nginx/templates/default.conf.template

ENV SIGNALING_URL=http://localhost:3000
EXPOSE ${PORT:-8080}

CMD ["sh", "-c", "envsubst '${PORT} ${SIGNALING_URL}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf && exec nginx -g 'daemon off;'"]
