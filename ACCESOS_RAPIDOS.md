# 🌐 ACCESOS RÁPIDOS - EMPRESA INVENTARIO

## 🚀 URLs de la Aplicación

### 📱 **APLICACIÓN PRINCIPAL**
🔗 **http://localhost:8082**
- Acceso principal al sistema
- Pantalla de login e interfaz completa

### 🗄️ **ADMINISTRACIÓN DE BASE DE DATOS**
🔗 **http://localhost:8083**
- phpMyAdmin para gestión de BD
- **Usuario**: `root`
- **Contraseña**: `root_password`

### 📧 **PRUEBAS DE CORREO**
🔗 **http://localhost:8026**
- Mailtrap para ver correos enviados
- Todos los correos del sistema aparecen aquí

---

## 👥 **USUARIOS DE PRUEBA**

| 🔑 **Rol**      | 📧 **Email**           | 🔐 **Contraseña** | 📋 **Permisos**          |
|------------------|------------------------|-------------------|---------------------------|
| 👑 Administrador | admin@empresa.com      | admin123          | Acceso completo al sistema |
| 📊 Contador      | contador@empresa.com   | contador123       | Reportes y contabilidad   |
| 📦 Bodeguero     | bodega@empresa.com     | bodega123         | Gestión de inventarios    |
| 💼 Vendedor      | ventas@empresa.com     | ventas123         | Ventas y consultas        |

---

## ⚡ **COMANDOS RÁPIDOS**

### Windows (script automatizado):
```cmd
docker-commands.bat
```

### Comandos directos:
```cmd
# Iniciar aplicación
docker-compose up -d

# Ver estado
docker-compose ps

# Ver logs
docker-compose logs -f

# Detener aplicación
docker-compose down
```

---

## 🛠️ **SOLUCIÓN DE PROBLEMAS**

### ❌ **"No puedo acceder a localhost:8082"**
1. Verificar que Docker esté ejecutándose
2. Ejecutar: `docker-compose ps` para ver estado
3. Si hay problemas, reiniciar: `docker-compose restart`

### ❌ **"Puerto ocupado"**
- El puerto 8082 puede estar en uso
- Cambiar en `docker-compose.yml` línea: `"8082:80"`
- Por ejemplo a: `"8084:80"`

### ❌ **"Aplicación muy lenta"**
- Verificar que Docker Desktop tenga suficiente RAM asignada
- Reiniciar Docker Desktop si es necesario

---

## 📞 **SOPORTE**
**Desarrollador**: Miguel Antonio Amaya Hernández  
**GitHub**: [@miguelxDd](https://github.com/miguelxDd)

---
**🚀 ¡Sistema listo para usar! Accede a http://localhost:8082 para comenzar!**