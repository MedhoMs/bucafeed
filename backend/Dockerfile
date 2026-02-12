# Use PHP 8.2 CLI for the build/run environment
# We use a single stage or simple build for now as requested, but ensuring extensions exist.
FROM php:8.2-apache

# Install system dependencies
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
    && docker-php-ext-install pdo_mysql mbstring bcmath zip \
    && a2enmod rewrite

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy only composer files first to leverage cache
COPY composer.json composer.lock ./

# Install dependencies (no scripts yet, just deps)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy the rest of the application
COPY . .

# Set permissions for storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run artisan commands (optional but good for production ready state if envs are present, 
# but specifically for build we might skip some that need DB).
# We'll just generate the autoloader optimized class map again with scripts if safe, 
# usually strictly for production we acknowledge scripts might need env.
# For now, let's keep it simple.

# Expose port (Railway sets PORT env, but Apache defaults to 80. We should configure Apache to listen on PORT or 8080)
# Railway automatically maps the exposed port. Apache default is 80.
# Let's configure Apache to bind to 0.0.0.0:8080 to match previous config expectation or use default 80.
# The user request mentioned 8080.
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 8080

# Start Apache
CMD ["apache2-foreground"]
