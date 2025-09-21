#!/bin/bash

echo "🚀 Iniciando Empresa Inventario Docker..."

# Esperar a que MySQL esté listo
echo "⏳ Esperando a que MySQL esté listo..."
until nc -z mysql 3306; do
    sleep 1
done
echo "✅ MySQL está listo!"

# Cambiar al directorio de la aplicación
cd /var/www

# Esperar un poco más para asegurar que MySQL esté completamente iniciado
sleep 5

# Generar key si no existe
if [ ! -f .env ]; then
    echo "⚙️ Copiando archivo .env..."
    cp .env.example .env
fi

# Generar application key
echo "🔑 Generando application key..."
php artisan key:generate --force

# Optimizar configuraciones
echo "⚙️ Optimizando configuraciones..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar migraciones
echo "🗄️ Ejecutando migraciones..."
php artisan migrate --force

# Ejecutar seeders (opcional, comentar si no quieres datos de prueba)
echo "🌱 Ejecutando seeders..."
php artisan db:seed --force || echo "⚠️ No hay seeders o falló la ejecución"

# Crear enlaces simbólicos para storage
echo "🔗 Creando enlaces simbólicos..."
php artisan storage:link

# Limpiar y optimizar
echo "🧹 Limpiando cache..."
php artisan optimize:clear
php artisan optimize

# Establecer permisos correctos
echo "🔒 Estableciendo permisos..."
chown -R laravel:www-data /var/www
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache

echo "✅ Aplicación lista!"
echo "🌐 Accede a tu aplicación en: http://localhost:8082"
echo "📊 phpMyAdmin disponible en: http://localhost:8083"
echo "📧 Mailtrap disponible en: http://localhost:8026"

# Iniciar supervisord
echo "🔄 Iniciando servicios..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf