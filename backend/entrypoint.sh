#!/bin/bash

# 1. Gestión de .env
# En producción (Railway), las env vars vienen del sistema. Un archivo .env sobreescribiría esas vars.
# En desarrollo, creamos .env desde .env.example si no existe.
if [ "$APP_ENV" = "production" ]; then
    # Eliminar .env si existe para que Laravel use las env vars del sistema de Railway
    if [ -f ".env" ]; then
        echo "Producción detectada: eliminando .env para usar variables de Railway..."
        rm .env
    fi
else
    if [ ! -f ".env" ]; then
        cp .env.example .env
    fi
fi

# 1.5. Asegurar directorios de storage y cache con permisos correctos
echo "Preparando directorios de storage y cache..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Ajustar permisos si es necesario
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Asegurar enlace simbólico de storage
if [ ! -L "public/storage" ]; then
    echo "Creando enlace simbólico de storage..."
    php artisan storage:link
fi

# 2. Instalar dependencias de PHP solo si falta el autoload
if [ ! -f "vendor/autoload.php" ]; then
    echo "Instalando dependencias de PHP (composer)..."
    composer install --no-interaction --optimize-autoloader
fi

# 3. Generar clave si no existe (solo en desarrollo con archivo .env)
if [ -f ".env" ] && ! grep -q "APP_KEY=base64" .env; then
    php artisan key:generate --force
fi

# 4. Instalar API solo si no existe el archivo de rutas
if [ ! -f "routes/api.php" ]; then
    echo "Configurando API de Laravel..."
    php artisan install:api --no-interaction
fi

# 5. Instalar dependencias de Node solo si falta la carpeta node_modules o esta vacia
if [ ! -d "node_modules" ] || [ -z "$(ls -A node_modules)" ]; then
    echo "Instalando dependencias de Node..."
    npm install
fi

# 6. Construir assets solo si no existe la carpeta build
if [ ! -d "public/build" ]; then
    echo "Compilando assets por primera vez..."
    npm run build
fi

# 7. Limpiar y recachear configuración (asegura que Railway env vars se aplican)
php artisan config:clear
php artisan route:clear

# En producción, recachear para rendimiento y para que las env vars se apliquen correctamente
if [ "$APP_ENV" = "production" ]; then
    echo "Cacheando configuración para producción..."
    php artisan config:cache
    php artisan route:cache
fi

# 8. Run migrations (--force needed for production)
php artisan migrate --force

# Iniciar Supervisor (que gestiona PHP y Nginx)
echo "✅ Backend listo. Arrancando Nginx + PHP-FPM con Supervisor..."
exec "$@"