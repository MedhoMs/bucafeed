# ETAPA 1: Compilación del Frontend (Vue.js)
FROM node:18-alpine AS build-stage
WORKDIR /app

# Copiar archivos de dependencias
COPY package*.json ./
RUN npm install

# Copiar el resto del código del frontend
COPY . .

# Compilar la aplicación para producción
RUN npm run build

# ETAPA 2: Servidor de Producción (Nginx)
FROM nginx:stable-alpine
WORKDIR /usr/share/nginx/html

# Copiar los archivos compilados desde la etapa anterior
# Vite por defecto compila en la carpeta 'dist'
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Copiar configuración personalizada de Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Exponer el puerto que Railway usa por defecto (80) o el configurado (8080)
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
