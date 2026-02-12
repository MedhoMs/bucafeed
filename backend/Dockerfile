# ETAPA 1: Compilación del Frontend (Vue.js) para el Backend
FROM node:22-alpine AS build-stage
WORKDIR /app

# 1. Forzar uso de las dependencias del ROOT para tener Vite y el Plugin de Laravel
COPY package.json package-lock.json* ./
RUN npm install

# 2. Copiar todo el código para tener acceso a src/, vite.config.js, etc.
COPY . .

# 3. Compilar forzando el punto de entrada a src/main.js para evitar el error de "index.html"
# Pasamos los parámetros por comando para saltarnos cualquier conflicto del archivo de config
RUN ./node_modules/.bin/vite build src/main.js --outDir backend/public/frontend --base /frontend/ --emptyOutDir

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

# Copiar dependencias de PHP (Flexible)
COPY backend/composer.json* backend/composer.lock* ./
RUN if [ ! -f "composer.json" ]; then COPY composer.json* composer.lock* ./ ; fi 2>/dev/null || true
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiar el código del backend
COPY . .
RUN if [ -d "backend" ]; then \
    cp -r backend/* . && cp -r backend/.* . 2>/dev/null || true && rm -rf backend; \
    fi

# TRAER LOS ASSETS COMPILADOS DESDE LA ETAPA 1
# Buscamos en ambos sitios por si Railway cambia el contexto
COPY --from=build-stage /app/backend/public/frontend ./public/frontend 2>/dev/null || \
    COPY --from=build-stage /app/public/frontend ./public/frontend

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# EntryPoint
RUN if [ -f "entrypoint.sh" ]; then cp entrypoint.sh /usr/local/bin/entrypoint.sh; \
    else find . -name entrypoint.sh -exec cp {} /usr/local/bin/entrypoint.sh \; ; fi
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

CMD ["entrypoint.sh"]
