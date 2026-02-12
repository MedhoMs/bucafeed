# ETAPA 1: Compilación del Frontend
# Usa una imagen de Node.js para compilar los assets de Vue
FROM node:18-alpine AS frontend
WORKDIR /app

# Copia solo los archivos necesarios para npm install
COPY package.json package-lock.json ./
RUN npm install

# Copia solo los archivos y directorios relacionados con el frontend
COPY public/ ./public/
COPY src/ ./src/
COPY index.html ./
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./
# Agrega cualquier otro archivo de configuración de frontend de nivel raíz si existen, por ejemplo, babel.config.js, .env

RUN npm run build


# ETAPA 2: Compilación del Backend
# Usa una imagen de Composer para instalar las dependencias de Laravel
FROM composer:2 AS vendor
WORKDIR /app
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts


# ETAPA 3: Imagen Final de Producción
# Usa una imagen de PHP para ejecutar la aplicación
FROM php:8.2-fpm-alpine

# Instala las extensiones de PHP requeridas por Laravel
RUN apk add --no-cache oniguruma-dev libxml2-dev libzip-dev \
    && docker-php-ext-install bcmath ctype fileinfo mbstring pdo pdo_mysql tokenizer xml zip

# Copia el código de la aplicación
COPY backend /var/www

# Copia el directorio 'vendor' de Composer
COPY --from=vendor /app/vendor /var/www/vendor

# Copia los assets del frontend compilados
COPY --from=frontend /app/backend/public/frontend /var/www/public/frontend

# Establece el directorio de trabajo
WORKDIR /var/www

# Establece los permisos adecuados para storage y bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expone el puerto que Railway proporciona
EXPOSE 8080

# El comando para iniciar el servidor de Laravel
CMD php artisan serve --host=0.0.0.0 --port=8080
