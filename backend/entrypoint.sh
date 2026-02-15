#!/bin/bash
set -e

echo "🚀 Iniciando contenedor del Backend..."

# 1. Crear .env si no existe
if [ ! -f ".env" ]; then
    echo "📄 .env no encontrado. Copiando desde .env.example ..."
    cp .env.example .env
fi

# 2. Asegurar directorios de storage y cache
echo "Preparando directorios de storage y cache..."
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# 3. Sincronizar Variables de Railway
echo "⚙️  Sincronizando configuración con Railway..."
if [ -z "$APP_URL" ] && [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
    export APP_URL="https://$RAILWAY_PUBLIC_DOMAIN"
fi

# Mapeo de DB (Railway usa estas variables por defecto)
[ -z "$DB_HOST" ] && [ -n "$MYSQLHOST" ] && export DB_HOST="$MYSQLHOST"
[ -z "$DB_PORT" ] && [ -n "$MYSQLPORT" ] && export DB_PORT="$MYSQLPORT"
[ -z "$DB_DATABASE" ] && [ -n "$MYSQLDATABASE" ] && export DB_DATABASE="$MYSQLDATABASE"
[ -z "$DB_USERNAME" ] && [ -n "$MYSQLUSER" ] && export DB_USERNAME="$MYSQLUSER"
[ -z "$DB_PASSWORD" ] && [ -n "$MYSQLPASSWORD" ] && export DB_PASSWORD="$MYSQLPASSWORD"

# Generar APP_KEY si falta
if ! grep -q "^APP_KEY=base64" .env; then
    php artisan key:generate --force
fi

# 4. Limpiar caches
php artisan config:clear
php artisan cache:clear

# 5. MIGRACIONES Y SEEDERS (Crucial para que aparezcan tus usuarios)
echo "🐘 Ejecutando migraciones..."
php artisan migrate --force

echo "🌱 Ejecutando seeders..."
# Usamos --force porque en producción Laravel pide confirmación para seedear
php artisan db:seed --force

# 6. Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Backend listo. Arrancando servidor..."
php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
