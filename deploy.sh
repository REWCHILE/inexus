#!/usr/bin/env bash
# ==============================================================================
# INEXUS CHILE - Script de Despliegue en Producción
# ==============================================================================
set -e

echo "🚀 Iniciando despliegue de INEXUS Chile en Producción..."

# 1. Verificar Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer no está instalado. Por favor instálalo primero."
    exit 1
fi

# 2. Configuración de Entorno (.env)
if [ ! -f .env ]; then
    echo "📋 Creando archivo .env a partir de .env.example..."
    cp .env.example .env
    php artisan key:generate --ansi
    echo "⚠️ Por favor verifica las credenciales de base de datos en .env"
fi

# 3. Instalar dependencias de producción
echo "📦 Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Enlace simbólico de almacenamiento
echo "🔗 Verificando enlace simbólico de storage..."
php artisan storage:link || true

# 5. Optimizar y cachear configuraciones
echo "⚡ Optimizando caché de Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Permisos de carpetas (Linux / Nginx / Apache)
echo "🔒 Ajustando permisos de storage y bootstrap/cache..."
chmod -R 775 storage bootstrap/cache || true
if [ "$(id -u)" -eq 0 ]; then
    chown -R www-data:www-data storage bootstrap/cache || true
fi

echo "=============================================================================="
echo "✅ Despliegue de archivos completado exitosamente."
echo "💡 Para cargar la base de datos con los 6.812 productos de Ingram Micro ejecuta:"
echo "   mysql -u TU_USUARIO -p TU_BASE_DE_DATOS < database/inexus_dump.sql"
echo "=============================================================================="
