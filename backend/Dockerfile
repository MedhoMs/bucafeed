FROM php:8.2-fpm

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

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

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
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
