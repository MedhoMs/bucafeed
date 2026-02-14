#!/bin/bash
set -e

echo "🚀 Iniciando contenedor del Backend..."

# 1. Crear .env si no existe
if [ ! -f ".env" ]; then
    echo "📄 .env no encontrado. Copiando desde .env.example ..."
    cp .env.example .env
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

# 2. Instalar dependencias de PHP solo si falta el autoload
if [ ! -f "vendor/autoload.php" ]; then
    echo "Instalando dependencias de PHP (composer)..."
    composer install --no-interaction --optimize-autoloader
fi

# 3. Verificar y Configurar Variables Críticas (.env)

# Función para asegurar variable en .env
ensure_env_var() {
    local key=$1
    local value=$2
    
    # En local, si la variable ya existe en el .env con ALGÚN valor, no la tocamos
    # para permitir que el usuario la cambie manualmente.
    if [ "$APP_ENV" = "local" ] && grep -q "^$key=" .env; then
        return
    fi

    if [ -n "$value" ]; then
        # 1. Si la clave ya existe, la eliminamos primero
        if grep -q "^$key=" .env; then
            grep -v "^$key=" .env > .env.tmp && mv .env.tmp .env
        fi
        
        # 2. Agregamos la clave con el valor nuevo al final
        echo "$key=\"$value\"" >> .env
        echo "🔧 Configurado $key en .env"
    fi
}

echo "⚙️  Sincronizando configuración..."

# 4. Auto-mapeo de variables de Railway
# Si Railway inyecta RAILWAY_PUBLIC_DOMAIN, lo usamos para APP_URL si no está definida
if [ -z "$APP_URL" ] && [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
    export APP_URL="https://$RAILWAY_PUBLIC_DOMAIN"
    echo "🌐 APP_URL configurada automáticamente: $APP_URL"
fi

# Auto-mapeo de base de datos
if [ -z "$DB_HOST" ] && [ -n "$MYSQLHOST" ]; then export DB_HOST="$MYSQLHOST"; fi
if [ -z "$DB_PORT" ] && [ -n "$MYSQLPORT" ]; then export DB_PORT="$MYSQLPORT"; fi
if [ -z "$DB_DATABASE" ] && [ -n "$MYSQLDATABASE" ]; then export DB_DATABASE="$MYSQLDATABASE"; fi
if [ -z "$DB_USERNAME" ] && [ -n "$MYSQLUSER" ]; then export DB_USERNAME="$MYSQLUSER"; fi
if [ -z "$DB_PASSWORD" ] && [ -n "$MYSQLPASSWORD" ]; then export DB_PASSWORD="$MYSQLPASSWORD"; fi

# Asegurar variables críticas en .env
ensure_env_var "APP_URL" "$APP_URL"
ensure_env_var "DB_CONNECTION" "mysql"
ensure_env_var "DB_HOST" "$DB_HOST"
ensure_env_var "DB_PORT" "$DB_PORT"
ensure_env_var "DB_DATABASE" "$DB_DATABASE"
ensure_env_var "DB_USERNAME" "$DB_USERNAME"
ensure_env_var "DB_PASSWORD" "$DB_PASSWORD"
ensure_env_var "FRONTEND_URL" "$FRONTEND_URL"


# Generar APP_KEY si aún falta
if ! grep -q "^APP_KEY=base64" .env; then
    echo "🔑 Generando nueva APP_KEY (no encontrada en sistema)..."
    php artisan key:generate --force
fi

# 4. Instalar API solo si no existe el archivo de rutas
if [ ! -f "routes/api.php" ]; then
    echo "Configurando API de Laravel..."
    php artisan install:api --no-interaction
fi

# 5. Arreglar/Generar Manifest de Vite
echo "🔍 Diagnóstico de Manifest (Vite 5)..."
echo "📍 Directorio actual (pwd): $(pwd)"

# Eliminar archivo 'hot' si existe (confunde a Laravel en local)
[ -f public/hot ] && rm public/hot
[ -f public/frontend/hot ] && rm public/frontend/hot

# Asegurar que el manifest está en su sitio
if [ -f "public/frontend/.vite/manifest.json" ]; then
    echo "📦 Moviendo manifest de .vite/ a frontend/"
    cp public/frontend/.vite/manifest.json public/frontend/manifest.json
fi

if [ -f "public/frontend/manifest.json" ]; then
    echo "✅ manifest.json ENCONTRADO."
    echo "📄 Contenido del manifest (primeros 100 caracteres):"
    head -c 100 public/frontend/manifest.json
    echo ""
    echo "� Permisos del archivo:"
    ls -la public/frontend/manifest.json
else
    echo "❌ manifest.json SIGUE SIN APARECER en public/frontend/"
fi

# 6. Comprobar qué cree Laravel que es su Public Path (Debug Crucial)
echo "🧪 Comprobando public_path() interno de Laravel:"
php artisan tinker --execute="echo 'Laravel Public Path: ' . public_path();"

# 7. Optimizar Laravel
echo "🚀 Refrescando cache..."
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize
chmod -R 777 storage bootstrap/cache public/frontend

# 8. Arrancar servidor Laravel
echo "🌐 Puerto detectado: ${PORT:-8080}"
echo "✅ Backend listo. Arrancando servidor..."
php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
