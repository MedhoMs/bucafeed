# ETAPA 1: Compilación del Frontend (Vue.js)
FROM node:18-alpine AS build-stage
WORKDIR /app

# Copiar archivos de dependencias
COPY package*.json ./
RUN npm install

# Copiar el resto del código del frontend
COPY . .

# Compilar la aplicación para producción
# Creamos la carpeta que el plugin de Laravel espera para evitar errores de build
RUN mkdir -p backend/public
RUN npx vite build --outDir dist

# ETAPA 2: Servidor de Producción (Nginx)
FROM nginx:stable-alpine
WORKDIR /usr/share/nginx/html

# Copiar los archivos compilados
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Copiar configuración personalizada
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Usamos el puerto 80 (estándar de Nginx)
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
