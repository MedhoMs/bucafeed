# ─── Stage 1: Composer deps ───────────────────────────────────────────────────
FROM mirror.gcr.io/library/composer:2 AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --optimize-autoloader \
    --no-dev

# ─── Stage 2: App ─────────────────────────────────────────────────────────────
FROM mirror.gcr.io/library/php:8.4-fpm-alpine

# Install system dependencies (Alpine uses apk, not apt-get)
RUN apk add --no-cache \
    git \
    curl \
    bash \
    nginx \
    supervisor \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Configure PHP settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 50M/g' "$PHP_INI_DIR/php.ini" && \
    sed -i 's/post_max_size = 8M/post_max_size = 50M/g' "$PHP_INI_DIR/php.ini"

# Get Composer from stage 1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy pre-installed vendor from stage 1 (cache layer)
COPY --from=composer-deps /app/vendor ./vendor

# Copy application source
COPY . /var/www

# Generate optimized autoloader with full source
RUN composer dump-autoload --optimize --no-dev

# Copy Nginx and Supervisor configs
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install npm dependencies and build assets
RUN npm ci && npm run build

# Make entrypoint executable
RUN chmod +x /var/www/entrypoint.sh

# Expose port
EXPOSE 8080

ENTRYPOINT ["/var/www/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
