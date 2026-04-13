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

# Copiar archivos de dependencias de PHP primero para cachear
COPY backend/composer.json backend/composer.lock ./

# Instalar dependencias de PHP (sin scripts para evitar fallos si el código aún no está)
RUN composer install --no-dev --no-scripts --no-interaction --optimize-autoloader

# Copiar archivos de dependencias de Node
COPY backend/package.json backend/package-lock.json ./

# Instalar dependencias de Node
RUN npm install

# Copiar el resto del código del backend
COPY backend/ .

# Crear directorios necesarios para Laravel
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache

# Ahora sí, compilar assets y terminar autoload de composer
RUN npm run build
RUN composer dump-autoload --optimize --no-dev --no-interaction

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
