# Use PHP 8.2 CLI for a lightweight, Apache-free environment
FROM php:8.2-cli

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring bcmath zip pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy only composer files first to leverage cache
COPY composer.json composer.lock ./

# Install dependencies (no scripts yet to avoid DB errors during build)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy the rest of the application
COPY . .

# Set permissions for storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create a custom entrypoint to handle config caching at runtime
# We intentionally do NOT run 'php artisan config:cache' here because env vars 
# from Railway are only available at runtime, not build time.
# Instead, we'll do it in the CMD chain or a script.

# Expose port 8080 (Railway expects this if we set it)
EXPOSE 8080

# Command to start the server
# We disable the configuration cache command in the CMD to ensure it grabs real-time ENVs
# or we can try to cache it just before serve if we want.
# For simplicity and reliability on Railway:
CMD sh -c "php artisan config:clear && php artisan route:clear && php artisan serve --host=0.0.0.0 --port=8080"
