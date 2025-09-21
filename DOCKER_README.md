# 🐳 Docker para Empresa Inventario

Este proyecto incluye una configuración completa de Docker para desarrollo y producción.

## 🚀 Inicio Rápido

### Prerrequisitos
- Docker Desktop instalado
- Git instalado

### Comandos de Inicio

```bash
# Clonar el repositorio (si no lo tienes)
git clone https://github.com/miguelxDd/Empresa_Inventario.git
cd Empresa_Inventario

# Construir y ejecutar los contenedores
docker-compose up --build -d

# Ver los logs en tiempo real
docker-compose logs -f
```

### 🌐 Accesos a la Aplicación

Una vez que los contenedores estén ejecutándose:

- **Aplicación Laravel**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Usuario: `root`
  - Contraseña: `root_password`
- **Mailhog (correos de prueba)**: http://localhost:8025
- **MySQL**: puerto 3307 (desde host)
- **Redis**: puerto 6380 (desde host)

## 📦 Servicios Incluidos

### 🐘 Aplicación (Laravel)
- PHP 8.2-FPM
- Extensiones: MySQL, Redis, GD, Zip, etc.
- Composer y NPM preinstalados
- Supervisor para procesos en background

### 🌐 Nginx
- Servidor web optimizado para Laravel
- Configuración de cache para archivos estáticos
- Manejo de errores personalizado

### 🗄️ MySQL 8.0
- Base de datos principal
- Configuración optimizada para desarrollo
- Datos persistentes en volumen Docker

### 🔄 Redis
- Cache y sesiones
- Queue jobs
- Datos persistentes

### 📊 phpMyAdmin
- Interfaz web para administrar MySQL
- Configuración automática

### 📧 Mailhog
- Captura todos los correos enviados
- Interfaz web para ver correos de prueba

## 🛠️ Comandos Útiles

### Gestión de Contenedores

```bash
# Iniciar todos los servicios
docker-compose up -d

# Detener todos los servicios
docker-compose down

# Ver estado de los contenedores
docker-compose ps

# Ver logs de todos los servicios
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f app
docker-compose logs -f mysql

# Reconstruir los contenedores
docker-compose up --build -d

# Eliminar todo (contenedores, volúmenes, imágenes)
docker-compose down -v --rmi all
```

### Comandos de Laravel

```bash
# Ejecutar comandos de Artisan
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan make:controller NombreController

# Acceder al shell del contenedor
docker-compose exec app bash

# Instalar dependencias de Composer
docker-compose exec app composer install

# Instalar dependencias de NPM
docker-compose exec app npm install
docker-compose exec app npm run build

# Limpiar cache
docker-compose exec app php artisan optimize:clear
```

### Comandos de Base de Datos

```bash
# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Rollback de migraciones
docker-compose exec app php artisan migrate:rollback

# Refresh de migraciones con seeders
docker-compose exec app php artisan migrate:refresh --seed

# Backup de base de datos
docker-compose exec mysql mysqldump -u root -proot_password empresa_inventario > backup.sql

# Restaurar backup
docker-compose exec -T mysql mysql -u root -proot_password empresa_inventario < backup.sql
```

## 🔧 Configuración

### Variables de Entorno

Las principales variables de entorno para Docker están en `.env`:

```env
# Base de datos
DB_HOST=mysql
DB_DATABASE=empresa_inventario
DB_USERNAME=root
DB_PASSWORD=root_password

# Redis
REDIS_HOST=redis

# Mail (Mailhog)
MAIL_HOST=mailhog
MAIL_PORT=1025
```

### Archivos de Configuración

- `docker-compose.yml`: Orquestación de servicios
- `Dockerfile`: Imagen personalizada de la aplicación
- `docker/nginx/default.conf`: Configuración de Nginx
- `docker/php/php.ini`: Configuración personalizada de PHP
- `docker/mysql/my.cnf`: Configuración de MySQL

## 🚨 Solución de Problemas

### Puerto en Uso
Si el puerto 8080 está en uso:
```bash
# Cambiar puerto en docker-compose.yml
nginx:
  ports:
    - "8081:80"  # Cambiar por puerto disponible
```

### Problemas de Permisos
```bash
# Arreglar permisos de storage
docker-compose exec app chown -R laravel:www-data /var/www/storage
docker-compose exec app chmod -R 775 /var/www/storage
```

### MySQL no Inicia
```bash
# Ver logs de MySQL
docker-compose logs mysql

# Eliminar volumen y recrear
docker-compose down -v
docker-compose up -d
```

### Reinstalación Completa
```bash
# Eliminar todo y empezar de nuevo
docker-compose down -v --rmi all
docker system prune -a
docker-compose up --build -d
```

## 📝 Notas de Desarrollo

### Volúmenes
- El código fuente se monta como volumen para desarrollo en vivo
- Los datos de MySQL y Redis se persisten en volúmenes nombrados
- Los logs se mapean para fácil acceso

### Escalabilidad
- Para producción, cambiar `APP_ENV=production` en `.env`
- Deshabilitar debug: `APP_DEBUG=false`
- Usar cache Redis para sesiones en producción

### Actualizaciones
- Actualizar dependencias: `docker-compose exec app composer update`
- Reconstruir imagen después de cambios: `docker-compose up --build -d`

## 🤝 Contribuir

1. Fork el proyecto
2. Crear rama feature (`git checkout -b feature/nueva-caracteristica`)
3. Commit cambios (`git commit -am 'Agregar nueva característica'`)
4. Push a la rama (`git push origin feature/nueva-caracteristica`)
5. Abrir Pull Request