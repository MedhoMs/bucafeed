# ETAPA 1: Compilación del Frontend (Vue.js)
FROM node:18-alpine AS build-stage
WORKDIR /app

# Copiar archivos de dependencias
COPY package*.json ./
RUN npm install

# Copiar el resto del código del frontend
COPY . .

# Compilar la aplicación para producción
# Creamos una configuración temporal que fuerce a Vite a tratar index.html como entrada
RUN echo "import { defineConfig } from 'vite'; \
import vue from '@vitejs/plugin-vue'; \
import { fileURLToPath, URL } from 'node:url'; \
export default defineConfig({ \
  plugins: [vue()], \
  base: '/', \
  build: { \
    outDir: 'dist', \
    rollupOptions: { \
        input: './index.html' \
    } \
  }, \
  resolve: { alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) } } \
});" > vite.config.standalone.js

# Ejecutamos el build usando esta configuración limpia
RUN npx vite build --config vite.config.standalone.js

# Verificar que el build generó archivos (fallar aquí es mejor que desplegar algo roto)
RUN ls -la dist/
RUN if [ ! -f dist/index.html ]; then echo "❌ Error: index.html no generado. Revisa los archivos arriba."; exit 1; fi

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
