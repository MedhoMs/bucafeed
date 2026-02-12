# ETAPA 1: Compilación del Frontend (Vue.js)
FROM node:18-alpine AS build-stage
WORKDIR /app

# Copiar archivos de dependencias
COPY package*.json ./
RUN npm install

# Copiar el resto del código del frontend
COPY . .

# Compilar la aplicación para producción
# Forzamos outDir a 'dist' porque el vite.config.js original apunta a una carpeta de backend que aquí no existe o está ignorada
RUN npx vite build --outDir dist

# ETAPA 2: Servidor de Producción (Nginx)
FROM nginx:stable-alpine
WORKDIR /usr/share/nginx/html

# Copiar los archivos compilados desde la etapa anterior
# Vite por defecto compila en la carpeta 'dist'
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Copiar configuración compartida como plantilla para soportar el puerto dinámico de Railway ($PORT)
# Nginx estable-alpine procesa archivos en /etc/nginx/templates/*.template y los mueve a /etc/nginx/conf.d/
RUN mkdir -p /etc/nginx/templates
COPY nginx.conf /etc/nginx/templates/default.conf.template

# En Railway no es estrictamente necesario EXPOSE si usamos $PORT, pero ayuda a la documentación interna
EXPOSE 8080

CMD ["nginx", "-g", "daemon off;"]
