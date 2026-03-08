FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Configure PHP settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/g' "$PHP_INI_DIR/php.ini" && \
    sed -i 's/post_max_size = 8M/post_max_size = 20M/g' "$PHP_INI_DIR/php.ini"

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Handle nested structure (if Railway builds from root) BEFORE installing dependencies
RUN if [ -d "/var/www/backend" ]; then \
    echo "Detected nested backend folder. Moving files..." && \
    cp -r /var/www/backend/. /var/www/ && \
    rm -rf /var/www/backend; \
fi

RUN mkdir -p /var/www/storage /var/www/bootstrap/cache && \
    if [ -d "/var/www/src/assets" ]; then \
      mkdir -p /var/www/public/assets; \
      cp -r /var/www/src/assets/. /var/www/public/assets/; \
    elif [ -d "/var/www/backend/src/assets" ]; then \
      mkdir -p /var/www/public/assets; \
      cp -r /var/www/backend/src/assets/. /var/www/public/assets/; \
    elif [ -d "/src/assets" ]; then \
      mkdir -p /var/www/public/assets; \
      cp -r /src/assets/. /var/www/public/assets/; \
    fi && \
    chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www/storage

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Install Node.js (LTS), pass production API URL, and build assets
ARG VITE_API_URL
ENV VITE_API_URL=${VITE_API_URL}

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    npm ci && \
    npm run build

# Copy Nginx configuration (using find to locate file regardless of context structure)
RUN cp $(find /var/www -name nginx.railway.conf | head -n 1) /etc/nginx/conf.d/default.conf

# Remove default Nginx site
RUN rm -f /etc/nginx/sites-enabled/default


# Make entrypoint executable (robust search)
RUN chmod +x $(find /var/www -name entrypoint.sh | head -n 1)

# Copy Supervisor configuration (robust search)
RUN cp $(find /var/www -name supervisord.conf | head -n 1) /etc/supervisor/conf.d/supervisord.conf

# Expose port 9000 and start php-fpm server
EXPOSE 9000

# Use entrypoint to handle setup (migrations, cache, etc.)
ENTRYPOINT ["/var/www/entrypoint.sh"]

# Start Supervisor (which starts Nginx & PHP-FPM)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
