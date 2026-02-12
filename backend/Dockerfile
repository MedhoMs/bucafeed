# ETAPA 1: Compilación del Frontend (Vue.js) para el Backend
# Usamos Node 22 para cumplir con los requisitos de Vite 7+
FROM node:22-alpine AS build-stage
WORKDIR /app

# Copiar archivos de dependencias desde el root
COPY package*.json ./
RUN npm install

# Copiar todo el código (incluyendo src/, vite.config.js y tailwind.config.js)
COPY . .

# Compilar usando la configuración de Laravel-Vite existente en el root
# Esto generará los assets en backend/public/frontend según vite.config.js
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

# Copiar dependencias de PHP (Flexible: intenta backend/ o raíz)
COPY backend/composer.json* backend/composer.lock* ./
RUN if [ ! -f "composer.json" ]; then COPY composer.json* composer.lock* ./ ; fi 2>/dev/null || true
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiar el código del backend (Flexible para ambos contextos de build)
COPY . .
RUN if [ -d "backend" ]; then \
    cp -r backend/* . && cp -r backend/.* . 2>/dev/null || true && rm -rf backend; \
    fi

# TRAER LOS ASSETS COMPILADOS DESDE LA ETAPA 1
# Según vite.config.js, están en backend/public/frontend (si contexto es root)
# O simplemente en public/frontend si el contexto fuera distinto.
COPY --from=build-stage /app/backend/public/frontend ./public/frontend 2>/dev/null || \
    COPY --from=build-stage /app/public/frontend ./public/frontend

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# EntryPoint (Súper flexible)
RUN if [ -f "entrypoint.sh" ]; then cp entrypoint.sh /usr/local/bin/entrypoint.sh; \
    else find . -name entrypoint.sh -exec cp {} /usr/local/bin/entrypoint.sh \; ; fi
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

CMD ["entrypoint.sh"]
