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
# IMPORTANTE: Si copiamos .env.example, tiene "APP_KEY=". Esto puede sobrescribir la variable de entorno con un valor vacío.
# Solución: Si detectamos APP_KEY en el entorno, eliminamos la línea del archivo .env para asegurar que "gana" la variable de entorno.

if [ -n "$APP_KEY" ]; then
    echo "✅ APP_KEY detectada en variables de entorno."
    # Eliminamos cualquier línea APP_KEY=... del archivo .env para evitar conflictos (prioridad variable de entorno)
    if [ -f ".env" ]; then
        sed -i '/^APP_KEY=/d' .env
        echo "🔧 Eliminada entrada APP_KEY de .env para usar la del sistema."
    fi
else
    echo "⚠️ APP_KEY no detectada en variables de entorno."
    
    # Si no está en envs, buscamos si ya se generó en el archivo .env
    if grep -q "APP_KEY=base64" .env; then
         echo "✅ APP_KEY encontrada en .env"
    else
         echo "🔑 Generando nueva APP_KEY..."
         php artisan key:generate --force
    fi
fi

# 4. Limpiar cachés para evitar conflictos
echo "🧹 Limpiando configuraciones..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "✅ Backend listo. Arrancando servidor..."

# 5. Arrancar servidor Laravel
php artisan serve --host=0.0.0.0 --port=8080
