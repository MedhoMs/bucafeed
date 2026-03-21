FROM php:8.4-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    supervisor \
    nginx \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd opcache

# Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar el código del backend
COPY backend/ .

# Crear directorios necesarios para Laravel y asegurar que existan para composer
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache

# Instalar dependencias de Node y compilar assets (Vite)
RUN npm install && npm run build

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configurar Nginx para Railway (usando el archivo que ya tienes)
COPY backend/nginx.railway.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Configurar Supervisord
COPY backend/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Permisos para Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Exponer el puerto que Railway espera (8080)
EXPOSE 8080

# Iniciar procesos a través de Supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
