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

# 3. Verificar y Generar APP_KEY
# Primero verificamos si la variable de entorno YA existe (inyectada por Railway)
if [ -z "$APP_KEY" ]; then
    echo "⚠️ APP_KEY no detectada en variables de entorno."
    
    # Si no está en envs, buscamos en el archivo .env
    if grep -q "APP_KEY=base64" .env; then
         echo "✅ APP_KEY encontrada en .env"
    else
         echo "🔑 Generando nueva APP_KEY..."
         php artisan key:generate --force
    fi
else
    echo "✅ APP_KEY detectada en variables de entorno."
fi

# 4. Limpiar cachés para evitar conflictos
echo "🧹 Limpiando configuraciones..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "✅ Backend listo. Arrancando servidor..."

# 5. Arrancar servidor Laravel
php artisan serve --host=0.0.0.0 --port=8080
