# 🏢 Sistema de Gestión Empresarial - Inventario y Contabilidad

<div align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Docker-20.x-blue?style=for-the-badge&logo=docker" alt="Docker">
  <img src="https://img.shields.io/badge/Bootstrap-5.x-purple?style=for-the-badge&logo=bootstrap" alt="Bootstrap">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License">
</div>

## 📑 **Tabla de Contenidos**
- [🚀 Inicio Rápido con Docker](#-inicio-rápido-con-docker)
- [🔧 Instalación Tradicional](#-instalación-tradicional-sin-docker)
- [👨‍💻 Desarrollador](#-desarrollador)
- [📋 Resumen del Sistema](#-resumen-del-sistema)
- [🚀 Características Principales](#-características-principales)
- [🛠 Tecnologías Utilizadas](#-tecnologías-utilizadas)
- [📁 Estructura del Proyecto](#-estructura-del-proyecto)
- [🎯 Funcionalidades Detalladas](#-funcionalidades-detalladas)
- [📊 Reportes Disponibles](#-reportes-disponibles)
- [🔐 Seguridad](#-seguridad)
- [🤝 Contribución](#-contribución)

---

## 🚀 **INICIO RÁPIDO CON DOCKER**de Gestión Empresarial - Inventario y Contabilidad

<div align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Docker-20.x-blue?style=for-the-badge&logo=docker" alt="Docker">
  <img src="https://img.shields.io/badge/Bootstrap-5.x-purple?style=for-the-badge&logo=bootstrap" alt="Bootstrap">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License">
</div>

---

##  **INICIO RÁPIDO CON DOCKER**

### 📋 **Prerrequisitos**
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado y ejecutándose
- Git instalado

###  **Instalación en 3 pasos**

1. **Clonar el repositorio**:
   ```bash
   git clone https://github.com/miguelxDd/Empresa_Inventario.git
   cd Empresa_Inventario
   ```

2. **Iniciar con Docker** (escoge una opción):
   
   **Opción A - Script automatizado (Windows):**
   ```cmd
   docker-commands.bat
   ```
   - Selecciona opción **1** para primera vez
   - Selecciona opción **2** para inicios normales
   - Selecciona opción **11** para ver URLs de acceso

   **Opción B - Comandos directos:**
   ```bash
   # Primera vez (construye contenedores)
   docker-compose up --build -d
   
   # Inicios normales
   docker-compose up -d
   ```

3. **¡Listo! Accede a tu aplicación:**
   - **🌐 Aplicación**: http://localhost:8082
   - **📊 phpMyAdmin**: http://localhost:8083 (user: `root`, pass: `root_password`)
   - **📧 Mailtrap**: http://localhost:8026

### 🛠️ **Comandos útiles**
```bash
# Ver estado de contenedores
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Ejecutar comandos de Laravel
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed

# Detener aplicación
docker-compose down

# Acceder al contenedor
docker-compose exec app bash
```

> 📖 **Documentación completa de Docker**: Ver [DOCKER_README.md](./DOCKER_README.md)

---

## 🔧 **Instalación Tradicional (Sin Docker)**

### **Requisitos Previos**
- PHP 8.2+ con extensiones: mbstring, xml, ctype, json, openssl, pdo, tokenizer
- Composer instalado
- Node.js y npm
- MySQL/MariaDB o SQLite
- Servidor web (Apache/Nginx) o usar `php artisan serve`

### **Pasos de Instalación**

1. **Clonar y navegar al proyecto**:
   ```bash
   git clone https://github.com/miguelxDd/Empresa_Inventario.git
   cd Empresa_Inventario
   ```

2. **Instalar dependencias**:
   ```bash
   # Dependencias de PHP
   composer install
   
   # Dependencias de Node.js
   npm install
   ```

3. **Configurar entorno**:
   ```bash
   # Copiar archivo de configuración
   copy .env.example .env    # Windows
   # cp .env.example .env    # Linux/Mac
   
   # Generar clave de aplicación
   php artisan key:generate
   ```

4. **Configurar base de datos** (editar `.env`):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=empresa_inventario
   DB_USERNAME=tu_usuario
   DB_PASSWORD=tu_contraseña
   ```

5. **Ejecutar migraciones y seeders**:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Compilar assets**:
   ```bash
   npm run build
   ```

7. **Crear enlace de almacenamiento**:
   ```bash
   php artisan storage:link
   ```

8. **Iniciar servidor de desarrollo**:
   ```bash
   php artisan serve
   ```

9. **Acceder a la aplicación**: http://localhost:8000

### **🔍 Solución de Problemas Comunes**
- **Error de permisos**: `chmod -R 755 storage bootstrap/cache`
- **Error de extensiones PHP**: Verificar que todas las extensiones estén habilitadas
- **Error de base de datos**: Verificar credenciales y que la BD existe

---

## 👨‍💻 **Desarrollador**

**Miguel Antonio Amaya Hernández**  
*Desarrollador PHP/Laravel Especializado*
[![GitHub](https://img.shields.io/badge/GitHub-100000?style=flat&logo=github&logoColor=white)](https://github.com/miguelxDd)

---

## 📋 **Resumen del Sistema**

**Sistema Integral de Gestión Empresarial** desarrollado en Laravel 12, diseñado para empresas que requieren un control completo de sus operaciones de inventario y contabilidad. La plataforma combina funcionalidades avanzadas de gestión de inventarios con un módulo contable robusto, ofreciendo una solución empresarial completa.

### 🎯 **Objetivo Principal**
Proporcionar una herramienta unificada que permita a las empresas gestionar eficientemente sus inventarios, movimientos de stock, y registros contables, asegurando la integridad de la información y facilitando la toma de decisiones estratégicas.

---

## 🚀 **Características Principales**

### 📦 **Módulo de Inventarios**
- **Gestión de Productos**: Catálogo completo con SKU, categorías y unidades de medida
- **Control de Bodegas**: Múltiples ubicaciones de almacenamiento con gestión independiente
- **Movimientos de Inventario**: Entradas, salidas, transferencias y ajustes automatizados
- **Existencias en Tiempo Real**: Seguimiento automático de stock disponible
- **Reporte Kardex**: Historial detallado de movimientos por producto y bodega
- **Costeo Promedio Ponderado**: Cálculo automático de costos unitarios

### 📊 **Módulo Contable**
- **Libro Mayor**: Reportes generales y por cuenta específica
- **Balance General**: Estados financieros automatizados
- **Asientos Contables**: Generación automática desde movimientos de inventario
- **Reportes Financieros**: Análisis detallado de la situación empresarial

### 📈 **Reportes y Analytics**
- **Dashboard Ejecutivo**: Métricas clave en tiempo real
- **Reportes de Valorización**: Análisis del valor del inventario
- **Estadísticas de Movimientos**: Tendencias y patrones de consumo
- **Exportación de Datos**: CSV, Excel y PDF para análisis externos

---

## 🛠 **Tecnologías Utilizadas**

### **Backend**
- **Framework**: Laravel 12.x
- **Lenguaje**: PHP 8.2+
- **Base de Datos**: SQLite (producción configurable)
- **ORM**: Eloquent
- **Autenticación**: Laravel Auth

### **Frontend**
- **CSS Framework**: Bootstrap 5.x
- **JavaScript**: jQuery + ES6
- **Componentes**: Select2, DataTables
- **Iconografía**: Bootstrap Icons
- **Responsive Design**: Mobile-first approach

### **Herramientas de Desarrollo**
- **Gestor de Dependencias**: Composer
- **Build Tools**: Vite
- **Code Style**: Laravel Pint
- **Testing**: PHPUnit
- **Version Control**: Git

---

## 📁 **Estructura del Proyecto**

```
Empresa_Inventario/
├── app/
│   ├── Http/Controllers/
│   │   ├── ProductoController.php      # Gestión de productos
│   │   ├── BodegaController.php        # Control de bodegas
│   │   ├── MovimientoInventarioController.php  # Movimientos
│   │   ├── ContabilidadController.php  # Módulo contable
│   │   ├── MayorController.php         # Libro mayor
│   │   └── ReporteController.php       # Reportes generales
│   └── Models/
│       ├── Producto.php                # Modelo de productos
│       ├── Bodega.php                  # Modelo de bodegas
│       ├── Movimiento.php              # Movimientos de inventario
│       ├── Existencia.php              # Stock actual
│       ├── Cuenta.php                  # Plan de cuentas
│       └── Asiento.php                 # Asientos contables
├── database/
│   ├── migrations/                     # Migraciones de BD
│   └── seeders/                        # Datos iniciales
├── resources/
│   └── views/
│       ├── productos/                  # Vistas de productos
│       ├── bodegas/                    # Vistas de bodegas
│       ├── movimientos/                # Vistas de movimientos
│       ├── contabilidad/               # Vistas contables
│       └── reportes/                   # Vistas de reportes
└── routes/
    └── web.php                         # Definición de rutas
```

---

## ⚙️ **Instalación y Configuración**

### **Requisitos Previos**
- PHP 8.2 o superior
- Composer
- Node.js y npm
- Servidor web (Apache/Nginx)

### **Pasos de Instalación**

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/miguelxDd/Empresa_Inventario.git
   cd Empresa_Inventario
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   npm install
   ```

3. **Configurar entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurar base de datos**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Compilar assets**
   ```bash
   npm run build
   ```

6. **Iniciar servidor**
   ```bash
   php artisan serve
   ```

---

## 📋 **Funcionalidades Detalladas**

### **Dashboard Principal**
- Resumen ejecutivo con métricas clave
- Gráficos de tendencias de inventario
- Alertas de stock mínimo
- Indicadores financieros principales

### **Gestión de Productos**
- Creación y edición de productos
- Asignación de categorías y unidades
- Control de estado (activo/inactivo)
- Historial de cambios de precio

### **Control de Bodegas**
- Configuración de múltiples ubicaciones
- Asignación de responsables
- Reportes de inventario por bodega
- Estadísticas de utilización

### **Movimientos de Inventario**
- **Entradas**: Compras, devoluciones, ajustes positivos
- **Salidas**: Ventas, devoluciones, ajustes negativos
- **Transferencias**: Movimientos entre bodegas
- **Confirmación**: Sistema de aprobación de movimientos

### **Reportes Contables**
- **Libro Mayor**: Análisis general y por cuenta
- **Balance General**: Estados financieros automatizados
- **Conciliación**: Verificación contable-inventario
- **Asientos Descuadrados**: Detección de inconsistencias

---

## 🔧 **Configuración Avanzada**

### **Comandos Artisan Personalizados**
```bash
# Limpiar cachés del sistema
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Regenerar autoload
composer dump-autoload
```

---

## 📊 **Casos de Uso Principales**

1. **Empresa Comercial**: Control de inventario de productos para venta
2. **Distribuidora**: Gestión de múltiples bodegas y transferencias
3. **Manufacturera**: Control de materias primas y productos terminados
4. **Empresa de Servicios**: Gestión de insumos y suministros

---




## 📞 **Contacto**

**Miguel Antonio Amaya Hernández**  
*Desarrollador PHP/Laravel*

- 📧 Email: [miguelxdxp94@gmail.com](mailto:miguelxdxp94@gmail.com)
- 📧 Email: [ah18059@ues.edu.sv](mailto:ah18059@ues.edu.sv)
- 🐱 GitHub: [@miguelxDd](https://github.com/miguelxDd)

---

<div align="center">
  <p><strong>Desarrollador por Miguel Antonio Amaya Hernández</strong></p>
  <p><em>Especialista en Desarrollo Web con PHP/Laravel</em></p>
</div>
