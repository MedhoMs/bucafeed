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
# ESTRATEGIA DEFINITIVA: Escribir explícitamente la APP_KEY en el archivo .env
# Esto elimina cualquier ambigüedad sobre qué variable tiene precedencia.

if [ -n "$APP_KEY" ]; then
    echo "✅ APP_KEY detectada en variables de entorno."
    
    # Si el archivo .env existe, nos aseguramos que tenga la APP_KEY correcta
    if [ -f ".env" ]; then
        if grep -q "^APP_KEY=" .env; then
            # Si existe la línea, la reemplazamos (usando | como delimitador por si la key tiene /)
            sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|g" .env
            echo "🔧 Actualizada APP_KEY en .env con el valor del sistema."
        else
            # Si no existe la línea, la agregamos
            echo "APP_KEY=$APP_KEY" >> .env
            echo "➕ Agregada APP_KEY a .env desde el sistema."
        fi
    fi
else
    echo "⚠️ APP_KEY no detectada en variables de entorno."
    
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
