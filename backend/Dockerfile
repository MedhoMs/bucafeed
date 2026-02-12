# ETAPA 1: Compilación del Frontend (Vue.js) para el Backend
# Este Dockerfile es "Dual": funciona tanto si el contexto es la raíz / como si es /backend
FROM node:22-alpine AS build-stage
WORKDIR /app

# Copiar archivos de dependencias
# (Intentamos copiar desde la raíz o desde la carpeta actual)
COPY package*.json ./
COPY backend/package*.json ./backend/ 2>/dev/null || true

RUN npm install

# Copiar TODO el código
COPY . .

# Verificamos si estamos en la raíz para que Vite vea 'src'
# Si el contexto es 'backend/', 'src' es invisible. FORZAMOS RAÍZ.
RUN if [ ! -d "src" ]; then \
    echo "❌ ERROR: Railway no encuentra la carpeta /src. Por favor, en la configuración del servicio (Settings -> General), cambia el 'Root Directory' a '/'"; \
    exit 1; \
    fi

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

# 1. Copiamos TODO lo que haya en el contexto actual
COPY . .

# 2. DETECCIÓN DE ESTRUCTURA
# Si existe la carpeta 'backend', significa que el contexto es la RAÍZ (Root: /).
# Movemos el contenido de 'backend' a la raíz del contenedor para que Laravel sea feliz.
RUN if [ -d "backend" ]; then \
    cp -rp backend/* . 2>/dev/null || true; \
    cp -rp backend/.* . 2>/dev/null || true; \
    fi

# 3. Traer los assets compilados de Vue
# La ubicación depende de si el contexto de compilación fue raíz o subcarpeta
COPY --from=build-stage /app/backend/public/frontend ./public/frontend 2>/dev/null || \
    COPY --from=build-stage /app/public/frontend ./public/frontend

# 4. Limpieza de carpetas innecesarias para el contenedor de producción si el contexto fue raíz
RUN rm -rf backend src 2>/dev/null || true

# 5. Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 6. Permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 7. EntryPoint (Buscamos el archivo donde sea que esté)
RUN if [ -f "entrypoint.sh" ]; then cp entrypoint.sh /usr/local/bin/entrypoint.sh; \
    else find . -name entrypoint.sh -exec cp {} /usr/local/bin/entrypoint.sh \; ; fi
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

CMD ["entrypoint.sh"]
