#!/bin/bash

# 1. Asegurar archivo .env
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# 1.5. Asegurar directorios de storage y cache con permisos correctos
echo "Preparando directorios de storage y cache..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/uploads/profiles
mkdir -p public/uploads/banners
mkdir -p public/uploads/events

# Ajustar permisos SIEMPRE al inicio (vital para volúmenes de Railway)
echo "Corrigiendo permisos de storage, bootstrap/cache y public/uploads..."
chown -R www-data:www-data storage bootstrap/cache public/uploads
chmod -R 775 storage bootstrap/cache public/uploads

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


# 3. Generar clave si no existe
if ! grep -q "APP_KEY=base64" .env; then
    php artisan key:generate --force
fi

echo "Refrescando base de datos y ejecutando seeders (migrate:fresh --seed)..."
php artisan migrate:fresh --seed --force

# 4. Instalar API solo si no existe el archivo de rutas
if [ ! -f "routes/api.php" ]; then
    echo "Configurando API de Laravel..."
    php artisan install:api --no-interaction
fi

# 5. y 6. Assets compilados en Dockerfile (Build time)

# 7. Limpiar caches para desarrollo (rápido y evita errores)
php artisan config:clear
php artisan route:clear

# Iniciar Supervisor (que gestiona PHP y Nginx)
echo "✅ Backend listo. Arrancando Nginx + PHP-FPM con Supervisor..."
exec "$@"
