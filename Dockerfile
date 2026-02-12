# ETAPA 1: Compilación del Frontend (Vue.js)
FROM node:18-alpine AS build-stage
WORKDIR /app

# Copiar archivos de dependencias
COPY package*.json ./
RUN npm install

# Copiar el resto del código del frontend
COPY . .

# Compilar la aplicación para producción
# Creamos la carpeta que el plugin de Laravel espera para evitar errores de configuración
RUN mkdir -p backend/public
# Forzamos la inclusión de index.html para que Vite lo procese como una SPA independiente
RUN npx vite build --outDir dist index.html

# Verificar que el build generó archivos (fallar aquí es mejor que desplegar algo roto)
RUN if [ ! -f dist/index.html ]; then echo "❌ Error: index.html no encontrado en dist/"; exit 1; fi

# ETAPA 2: Servidor de Producción (Nginx)
FROM nginx:stable-alpine
WORKDIR /usr/share/nginx/html

# Copiar los archivos compilados
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Copiar configuración personalizada
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Usamos el puerto 8080 para que coincida con la UI de Railway del usuario
EXPOSE 8080

CMD ["nginx", "-g", "daemon off;"]
