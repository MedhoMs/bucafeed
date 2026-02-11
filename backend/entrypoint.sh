#!/bin/bash

# 1. Crear .env si no existe
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# 2. Instalar dependencias PHP si no existen
if [ ! -d "vendor" ]; then
    echo "Instalando dependencias de PHP..."
    composer install --no-interaction --optimize-autoloader
fi

# 3. Generar APP_KEY si no existe
if ! grep -q "APP_KEY=base64" .env; then
    php artisan key:generate --force
fi

# 4. Ejecutar migraciones
php artisan migrate --force

# 5. Limpiar caché
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Backend listo para recibir conexiones."

# 6. Arrancar PHP-FPM
php-fpm
