#!/bin/bash
set -e

echo "🚀 Iniciando contenedor del Backend..."

# 1. Crear .env si no existe
if [ ! -f ".env" ]; then
    echo "📄 .env no encontrado. Copiando desde .env.example ..."
    cp .env.example .env
fi

# 2. Instalar dependencias PHP si falta vendor (redundante si se hizo en build, pero útil en volúmenes)
if [ ! -d "vendor" ]; then
    echo "📦 Instalando dependencias de PHP..."
    composer install --no-interaction --optimize-autoloader
fi

# 3. Verificar y Configurar Variables Críticas (.env)

# Función para asegurar variable en .env
ensure_env_var() {
    local key=$1
    local value=$2
    
    if [ -n "$value" ]; then
        # 1. Si la clave ya existe, la eliminamos primero (grep -v es más seguro que sed para esto)
        if grep -q "^$key=" .env; then
            grep -v "^$key=" .env > .env.tmp && mv .env.tmp .env
        fi
        
        # 2. Agregamos la clave con el valor nuevo al final
        # Usamos comillas para manejar espacios, pero cuidado con comillas internas si las hay.
        # Generalmente railway pasa valores limpios.
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

# 4. Limpiar cachés para evitar conflictos
echo "🧹 Limpiando configuraciones..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Escribir el puerto en los logs para depuración
echo "🌐 Railway PORT detectado: ${PORT:-8080}"
echo "✅ Backend listo. Arrancando servidor en el puerto ${PORT:-8080}..."

# 5. Arrancar servidor Laravel (Usamos el puerto de Railway si existe)
php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
