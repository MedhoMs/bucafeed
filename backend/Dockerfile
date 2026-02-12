# ETAPA 1: Compilación del Frontend (Vue.js) para el Backend
FROM node:22-alpine AS build-stage
WORKDIR /app

# Copiar dependencias y configuraciones del root
COPY package*.json ./
RUN npm install

# Copiar código fuente y archivos de configuración
COPY . .

# Compilar los assets de Vue
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

# Copiar SOLO el contenido de la carpeta backend para evitar contaminación
COPY backend/ .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-scripts

# TRAER LOS ASSETS COMPILADOS DESDE LA ETAPA 1
# La ubicación depende de si el contexto de compilación fue raíz
COPY --from=build-stage /app/backend/public/frontend ./public/frontend

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# EntryPoint
COPY backend/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

CMD ["entrypoint.sh"]
