# ETAPA 1: Compilación del Frontend (Vue.js) para el Backend
# IMPORTANTE: Este Dockerfile requiere que el 'Root Directory' en Railway sea la raíz '/'
FROM node:22-alpine AS build-stage
WORKDIR /app

# 1. Copiar dependencias del proyecto completo
COPY package*.json ./
RUN npm install

# 2. Copiar todo el código para que Vite vea la carpeta /src y /backend
COPY . .

# Verificación de seguridad del contexto
RUN if [ ! -d "src" ]; then \
    echo "❌ ERROR: Contexto incorrecto. En Railway, cambia el 'Root Directory' de este servicio a '/'"; \
    exit 1; \
    fi

# 3. Compilar el frontend usando la configuración del root (Vite generará los assets en backend/public/frontend)
RUN npm run build

# ETAPA 2: Servidor Backend (PHP/Laravel)
FROM php:8.4-cli

# Dependencias y extensiones
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring bcmath zip pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar dependencias de PHP (Estamos en la raíz, así que buscamos en backend/)
COPY backend/composer.json* backend/composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiar el código del backend
COPY backend/ .

# TRAER LOS ASSETS COMPILADOS DESDE LA ETAPA 1
# Estarán en /app/backend/public/frontend porque así lo dice el vite.config.js del root
COPY --from=build-stage /app/backend/public/frontend ./public/frontend

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# EntryPoint
COPY backend/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

CMD ["entrypoint.sh"]
