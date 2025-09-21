# Dockerfile para Laravel 11
FROM php:8.2-fpm

# Argumentos de construcción
ARG user=laravel
ARG uid=1000

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    cron \
    supervisor \
    nginx \
    netcat-openbsd

# Limpiar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Instalar Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Obtener la última versión de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario del sistema para ejecutar comandos de Composer y Artisan
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Configurar PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# Configurar Nginx
COPY docker/nginx/default.conf /etc/nginx/sites-available/default

# Configurar Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos existentes de la aplicación
COPY --chown=$user:$user . /var/www

# Instalar dependencias de Composer
USER $user
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias de Node.js y compilar assets
RUN npm install && npm run build

# Cambiar de vuelta a root para configuraciones finales
USER root

# Establecer permisos
RUN chown -R $user:www-data /var/www
RUN chmod -R 775 /var/www/storage
RUN chmod -R 775 /var/www/bootstrap/cache

# Script de inicio
COPY docker/scripts/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Exponer puerto
EXPOSE 80

# Comando por defecto
CMD ["/usr/local/bin/start.sh"]