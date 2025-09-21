@echo off
REM Script de comandos útiles para Docker en Windows

echo ================================
echo   EMPRESA INVENTARIO - DOCKER
echo ================================
echo 🌐 APLICACION: http://localhost:8082
echo 📊 PHPMYADMIN: http://localhost:8083
echo 📧 MAILTRAP:   http://localhost:8026
echo ================================
echo.

:menu
echo Selecciona una opción:
echo.
echo 1. Iniciar aplicación (primera vez)
echo 2. Iniciar aplicación (normal)
echo 3. Detener aplicación
echo 4. Ver estado de contenedores
echo 5. Ver logs en tiempo real
echo 6. Acceder al contenedor de Laravel
echo 7. Ejecutar migraciones
echo 8. Limpiar cache de Laravel
echo 9. Backup de base de datos
echo 10. Reinstalar completamente
echo 11. Mostrar URLs de acceso
echo 0. Salir
echo.

set /p choice="Ingresa tu opción (0-11): "

if "%choice%"=="1" goto first_start
if "%choice%"=="2" goto normal_start
if "%choice%"=="3" goto stop
if "%choice%"=="4" goto status
if "%choice%"=="5" goto logs
if "%choice%"=="6" goto shell
if "%choice%"=="7" goto migrate
if "%choice%"=="8" goto cache_clear
if "%choice%"=="9" goto backup
if "%choice%"=="10" goto reinstall
if "%choice%"=="11" goto show_urls
if "%choice%"=="0" goto exit

echo Opción inválida. Intenta de nuevo.
goto menu

:first_start
echo Iniciando por primera vez (construyendo contenedores)...
echo Este proceso puede tomar varios minutos...
echo.
docker-compose up --build -d
echo.
echo ⏳ Esperando que todos los servicios estén listos...
timeout /t 30 /nobreak >nul
echo.
echo ✅ Aplicación iniciada!
echo.
echo 🌐 Aplicación Laravel: http://localhost:8082
echo 📊 phpMyAdmin: http://localhost:8083 (user: root, pass: root_password)
echo 📧 Mailtrap: http://localhost:8026
echo.
echo Presiona cualquier tecla para volver al menú...
pause >nul
goto menu

:normal_start
echo Iniciando aplicación...
docker-compose up -d
echo ✅ Aplicación iniciada!
echo 🌐 Accede en: http://localhost:8082
echo.
echo Presiona cualquier tecla para volver al menú...
pause >nul
goto menu

:stop
echo Deteniendo aplicación...
docker-compose down
echo ✅ Aplicación detenida!
echo.
echo Presiona cualquier tecla para volver al menú...
pause >nul
goto menu

:status
echo Estado de los contenedores:
echo.
docker-compose ps
echo.
echo Presiona cualquier tecla para volver al menú...
pause >nul
goto menu

:logs
echo Mostrando logs en tiempo real (Ctrl+C para salir)...
echo.
docker-compose logs -f
goto menu

:shell
echo Accediendo al contenedor de Laravel...
echo (escribe 'exit' para salir)
echo.
docker-compose exec app bash
goto menu

:migrate
echo Ejecutando migraciones...
echo.
docker-compose exec app php artisan migrate
echo.
echo ✅ Migraciones completadas!
echo.
echo Presiona cualquier tecla para volver al menú...
pause >nul
goto menu

:cache_clear
echo Limpiando cache de Laravel...
echo.
docker-compose exec app php artisan optimize:clear
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
echo.
echo ✅ Cache limpiado y optimizado!
echo.
echo Presiona cualquier tecla para volver al menú...
pause >nul
goto menu

:backup
echo Creando backup de la base de datos...
set datetime=%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set datetime=%datetime: =0%
docker-compose exec mysql mysqldump -u root -proot_password empresa_inventario > backup_%datetime%.sql
echo ✅ Backup creado: backup_%datetime%.sql
echo.
echo Presiona cualquier tecla para volver al menú...
pause >nul
goto menu

:reinstall
echo ⚠️  ATENCIÓN: Esto eliminará todos los datos y contenedores!
set /p confirm="¿Estás seguro? (S/N): "
if /i "%confirm%" neq "S" goto menu
echo.
echo Eliminando contenedores y volúmenes...
docker-compose down -v --rmi all
echo.
echo Reconstruyendo desde cero...
docker-compose up --build -d
echo.
echo ✅ Reinstalación completada!
echo.
echo Presiona cualquier tecla para volver al menú...
pause >nul
goto menu

:exit
echo ¡Hasta luego!
exit
goto menu

:normal_start
echo Iniciando aplicación...
docker-compose up -d
echo.
echo ✅ Aplicación iniciada!
echo 🌐 Accede en: http://localhost:8080
pause
goto menu

:stop
echo Deteniendo aplicación...
docker-compose down
echo ✅ Aplicación detenida!
pause
goto menu

:status
echo Estado de los contenedores:
docker-compose ps
pause
goto menu

:logs
echo Mostrando logs en tiempo real (Ctrl+C para salir)...
docker-compose logs -f
pause
goto menu

:shell
echo Accediendo al contenedor de Laravel...
docker-compose exec app bash
goto menu

:migrate
echo Ejecutando migraciones...
docker-compose exec app php artisan migrate
echo ✅ Migraciones completadas!
pause
goto menu

:cache_clear
echo Limpiando cache de Laravel...
docker-compose exec app php artisan optimize:clear
docker-compose exec app php artisan optimize
echo ✅ Cache limpiado!
pause
goto menu

:backup
set /p filename="Nombre del archivo de backup (sin extensión): "
if "%filename%"=="" set filename=backup_%date:~-4%%date:~3,2%%date:~0,2%
echo Creando backup: %filename%.sql
docker-compose exec mysql mysqldump -u root -proot_password empresa_inventario > %filename%.sql
echo ✅ Backup creado: %filename%.sql
pause
goto menu

:show_urls
echo ================================
echo      URLS DE ACCESO
echo ================================
echo.
echo 🌐 APLICACION PRINCIPAL:
echo    👉 http://localhost:8082
echo.
echo 📊 PHPMYADMIN (Base de Datos):
echo    👉 http://localhost:8083
echo    Usuario: root
echo    Contraseña: root_password
echo.
echo 📧 MAILTRAP (Correos de prueba):
echo    👉 http://localhost:8026
echo.
echo 👥 USUARIOS DE PRUEBA:
echo    Administrador: admin@empresa.com / admin123
echo    Contador: contador@empresa.com / contador123
echo    Bodeguero: bodega@empresa.com / bodega123
echo    Vendedor: ventas@empresa.com / ventas123
echo.
echo ================================
echo.
echo Presiona cualquier tecla para volver al menú...
pause >nul
goto menu

:reinstall
echo ⚠️  ADVERTENCIA: Esto eliminará todos los datos y contenedores!
set /p confirm="¿Estás seguro? (s/N): "
if /i not "%confirm%"=="s" goto menu
echo Eliminando contenedores y volúmenes...
docker-compose down -v --rmi all
echo Limpiando sistema Docker...
docker system prune -f
echo Reconstruyendo desde cero...
docker-compose up --build -d
echo ✅ Reinstalación completada!
pause
goto menu

:exit
echo ¡Hasta luego!
exit /b 0