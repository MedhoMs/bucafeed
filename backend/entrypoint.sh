#!/bin/bash
set -e

echo "🚀 Iniciando contenedor del Backend..."

# 1. Crear .env si no existe
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# 2. Directorios y Permisos
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# 3. Dependencias
if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-interaction --optimize-autoloader --no-scripts
fi

# 4. Sincronizar DB
[ -z "$DB_HOST" ] && [ -n "$MYSQLHOST" ] && export DB_HOST="$MYSQLHOST"
[ -z "$DB_PORT" ] && [ -n "$MYSQLPORT" ] && export DB_PORT="$MYSQLPORT"
[ -z "$DB_DATABASE" ] && [ -n "$MYSQLDATABASE" ] && export DB_DATABASE="$MYSQLDATABASE"
[ -z "$DB_USERNAME" ] && [ -n "$MYSQLUSER" ] && export DB_USERNAME="$MYSQLUSER"
[ -z "$DB_PASSWORD" ] && [ -n "$MYSQLPASSWORD" ] && export DB_PASSWORD="$MYSQLPASSWORD"

# 5. Migraciones y Limpieza
echo "🐘 Ejecutando migraciones..."
php artisan migrate --force || echo "⚠️ Error en migraciones, continuando..."

echo "🌱 Ejecutando seeders..."
php artisan db:seed --force || echo "⚠️ Error en seeders, continuando..."

php artisan config:clear
php artisan cache:clear

if [ -x "/usr/bin/supervisord" ]; then
    echo "✅ Backend listo. Arrancando Nginx + PHP-FPM con Supervisor..."
    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
else
    echo "✅ Backend listo. Arrancando PHP-FPM..."
    exec php-fpm
fi







