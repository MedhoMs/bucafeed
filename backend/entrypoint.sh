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

# Ajustar permisos SIEMPRE al inicio (vital para volúmenes de Railway)
echo "Corrigiendo permisos de storage y bootstrap/cache..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 2. Instalar dependencias de PHP solo si falta el autoload
if [ ! -f "vendor/autoload.php" ]; then
    echo "Instalando dependencias de PHP (composer)..."
    composer install --no-interaction --optimize-autoloader
fi


# 3. Generar clave si no existe
if ! grep -q "APP_KEY=base64" .env; then
    php artisan key:generate --force
fi

# 3.5 Ejecutar migraciones (SIEMPRE en despliegue)
echo "Ejecutando migraciones..."
php artisan migrate --force


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

# 7. Limpiar caches para desarrollo (rápido y evita errores)
php artisan config:clear
php artisan route:clear

# Iniciar Supervisor (que gestiona PHP y Nginx)
echo "✅ Backend listo. Arrancando Nginx + PHP-FPM con Supervisor..."
exec "$@"