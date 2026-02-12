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
        if grep -q "^$key=" .env; then
            sed -i "s|^$key=.*|$key=$value|g" .env
        else
            echo "$key=$value" >> .env
        fi
        echo "🔧 Configurado $key en .env"
    fi
}

echo "⚙️  Sincronizando configuración..."

# Asegurar APP_KEY
ensure_env_var "APP_KEY" "$APP_KEY"

# Asegurar DB_CONNECTION (Vital para evitar SQLite por defecto del .env.example)
ensure_env_var "DB_CONNECTION" "mysql"
ensure_env_var "DB_HOST" "$DB_HOST"
ensure_env_var "DB_PORT" "$DB_PORT"
ensure_env_var "DB_DATABASE" "$DB_DATABASE"
ensure_env_var "DB_USERNAME" "$DB_USERNAME"
ensure_env_var "DB_PASSWORD" "$DB_PASSWORD"

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

echo "✅ Backend listo. Arrancando servidor..."

# 5. Arrancar servidor Laravel
php artisan serve --host=0.0.0.0 --port=8080
