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
# Ejecutamos el build apuntando a la raíz. Vite usará index.html por defecto si existe.
# Forzamos outDir a 'dist' para que Nginx sepa dónde buscar.
RUN npx vite build --outDir dist

# Verificar que el build generó el punto de entrada index.html
RUN ls -la dist/
RUN if [ ! -f dist/index.html ]; then echo "❌ Error: index.html no generado. Revisando archivos..."; ls -R dist/; exit 1; fi

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
