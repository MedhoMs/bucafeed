# ETAPA 1: Compilación del Frontend (Vue.js) para el Backend
FROM node:18-alpine AS build-stage
WORKDIR /app

# NOTA: En Railway, el contexto de build para el backend suele ser la carpeta 'backend/'.
# Pero necesitamos el código del frontend que está un nivel arriba.
# Si Railway usa el root como contexto, esto funcionará. 
# Si el contexto es solo 'backend/', fallará. Asumiremos el root para máxima integración.

# Copiar archivos de dependencias desde el root
COPY package*.json ./
RUN npm install

# Copiar todo el código del root para tener acceso a src/, vite.config.js, etc.
COPY . .

# Compilar la aplicación para producir los assets (JS/CSS)
RUN npx vite build --outDir backend/public/frontend --base /frontend/

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

# Copiar dependencias de PHP (Flexible: intenta backend/ o raíz)
COPY backend/composer.json* backend/composer.lock* ./
RUN if [ ! -f "composer.json" ]; then COPY composer.json* composer.lock* ./ ; fi 2>/dev/null || true

RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiar el código del backend (intentar ambas rutas para compatibilidad de contexto)
COPY . .
RUN if [ -d "backend" ]; then mv backend/* . && mv backend/.* . 2>/dev/null || true; fi

# TRAER LOS ASSETS COMPILADOS DESDE LA ETAPA 1
COPY --from=build-stage /app/backend/public/frontend ./public/frontend 2>/dev/null || \
    COPY --from=build-stage /app/public/frontend ./public/frontend

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# EntryPoint (Flexible)
RUN if [ -f "entrypoint.sh" ]; then cp entrypoint.sh /usr/local/bin/entrypoint.sh; \
    else cp backend/entrypoint.sh /usr/local/bin/entrypoint.sh; fi
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

CMD ["entrypoint.sh"]
