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
echo "Ajustando permisos de directorios..."
mkdir -p public/uploads
chmod -R 777 storage bootstrap/cache database public/uploads
chown -R www-data:www-data storage bootstrap/cache database public/uploads 2>/dev/null || true

# Asegurar enlace simbólico de storage
if [ ! -L "public/storage" ]; then
    echo "Creando enlace simbólico de storage..."
    php artisan storage:link
fi

# 2. Instalar/Actualizar dependencias de PHP
if [ "$APP_ENV" != "production" ]; then
    echo "Verificando/Instalando dependencias de PHP (composer) para desarrollo..."
    composer install --no-interaction --optimize-autoloader
else
    if [ ! -f "vendor/autoload.php" ]; then
        echo "Instalando dependencias de PHP (composer) indispensables..."
        composer install --no-interaction --optimize-autoloader --no-dev
    fi
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

# 5. Instalar/Actualizar dependencias de Node para el Admin Panel
if [ "$APP_ENV" != "production" ]; then
    echo "Verificando/Instalando dependencias de Node para el Admin Panel..."
    npm install
else
    if [ ! -d "node_modules" ]; then
        echo "Instalando dependencias de Node indispensables..."
        npm install --only=production
    fi
fi

# 6. Construir assets si no existe la carpeta build o estamos en desarrollo
if [ ! -d "public/build" ] || [ "$APP_ENV" != "production" ]; then
    echo "Compilando assets del Admin Panel..."
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
echo "Ejecutando migraciones pendientes..."
php artisan migrate --force

# En desarrollo, sembrar datos de prueba si la tabla está vacía (menos de 10 usuarios)
if [ "$APP_ENV" != "production" ]; then
    USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tr -d '\r\n')
    if [ -z "$USER_COUNT" ] || ! [[ "$USER_COUNT" =~ ^[0-9]+$ ]]; then
        USER_COUNT=0
    fi
    if [ "$USER_COUNT" -lt 10 ]; then
        echo "La base de datos parece vacía ($USER_COUNT usuarios). Sembrando datos iniciales..."
        php artisan db:seed --force
    else
        echo "La base de datos ya contiene datos ($USER_COUNT usuarios). Saltando seeding automático."
    fi
fi

# Iniciar Supervisor (que gestiona PHP y Nginx)
echo "✅ Backend listo. Arrancando Nginx + PHP-FPM con Supervisor..."
exec "$@"