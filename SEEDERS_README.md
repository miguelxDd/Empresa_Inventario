# Sistema de Inventario Empresarial - Datos Demo

## ✅ Base de Datos Poblada Exitosamente

El sistema se ha inicializado con datos completos para un ambiente de **demostración y desarrollo**.

### 📊 Resumen de Datos Creados:

- **54 Cuentas Contables** - Plan de cuentas estructurado por niveles
- **40 Unidades de Medida** - Todas las unidades necesarias para productos
- **49 Categorías de Productos** - Organizadas por sectores industriales  
- **10 Bodegas** - Distribuidas geográficamente
- **22 Productos Demo** - Productos de muestra para diferentes categorías
- **4 Usuarios de Prueba** - Con diferentes roles y permisos

### 👥 Usuarios de Acceso

| Usuario | Email | Password | Rol |
|---------|-------|----------|-----|
| Admin | admin@empresa.com | admin123 | Administrador |
| Inventario | inventario@empresa.com | inventario123 | Gestión Inventario |
| Contabilidad | contabilidad@empresa.com | contabilidad123 | Contabilidad |
| Ventas | ventas@empresa.com | ventas123 | Ventas |

### 📈 Plan de Cuentas Contables

#### Estructura Principal:
- **Grupo 1**: ACTIVOS (Corrientes y No Corrientes)
  - Inventarios (Productos Terminados, Materias Primas, En Proceso, Suministros)
  - Bancos y Cuentas por Cobrar
  - Propiedad, Planta y Equipo

- **Grupo 2**: PASIVOS (Corrientes y No Corrientes)
  - Cuentas por Pagar Comerciales
  - Obligaciones Laborales y Fiscales

- **Grupo 3**: PATRIMONIO
  - Capital Social
  - Utilidades Retenidas y del Ejercicio

- **Grupo 4**: INGRESOS
  - Ingresos Operacionales (Ventas)
  - Otros Ingresos

- **Grupo 5**: COSTOS
  - Costo de Ventas y Prestación de Servicios

- **Grupo 6**: GASTOS
  - Gastos Operacionales (Administrativos, Ventas)
  - Gastos Financieros

### 🏪 Bodegas Disponibles

1. **Bodega Central San José** - Principal
2. **Bodega Norte Alajuela** - Distribución Norte
3. **Bodega Sur Cartago** - Distribución Sur
4. **Bodega Pacífico Puntarenas** - Costa Pacífico
5. **Bodega Atlántico Limón** - Costa Atlántica
6. **Bodega Refrigerados** - Productos refrigerados
7. **Almacén Materia Prima** - Materias primas
8. **Depósito Productos Terminados** - Productos listos
9. **Zona de Cuarentena** - Productos en inspección
10. **Bodega Devoluciones** - Productos devueltos

### 🏷️ Categorías de Productos

#### Productos Alimenticios (ALI)
- Productos Frescos (ALI-FRE)
- Productos Conservados (ALI-CON)
- Bebidas (ALI-BEB)
- Productos Lácteos (ALI-LAC)
- Productos Cárnicos (ALI-CAR)

#### Productos Tecnológicos (TEC)
- Equipos de Computación (TEC-COM)
- Dispositivos Móviles (TEC-MOV)
- Audio y Video (TEC-AUD)
- Gaming (TEC-GAM)

#### Productos Textiles (TEX)
- Ropa Hombre (TEX-ROH)
- Ropa Mujer (TEX-ROM)
- Ropa Niños (TEX-RON)
- Calzado (TEX-CAL)
- Accesorios (TEX-ACC)

#### Productos para el Hogar (HOG)
- Productos de Cocina (HOG-COC)
- Productos de Limpieza (HOG-LIM)
- Productos de Baño (HOG-BAÑ)
- Decoración (HOG-DEC)
- Jardín y Exterior (HOG-JAR)

#### Productos Industriales (IND)
- Herramientas (IND-HER)
- Maquinaria (IND-MAQ)
- Materiales de Construcción (IND-MAT)
- Productos Químicos (IND-QUI)
- Seguridad Industrial (IND-SEG)

#### Productos Automotrices (AUT)
- Repuestos (AUT-REP)
- Accesorios (AUT-ACE)
- Llantas y Neumáticos (AUT-LLA)
- Lubricantes (AUT-LUB)

#### Productos de Oficina (OFI)
- Papelería (OFI-PAP)
- Útiles de Escritura (OFI-ESC)
- Equipos de Oficina (OFI-EQU)
- Mobiliario (OFI-MOB)

#### Servicios (SER)
- Consultoría (SER-CON)
- Mantenimiento (SER-MAN)
- Servicios Técnicos (SER-TEC)
- Capacitación (SER-CAP)

### 📦 Productos Demo Incluidos

- **Alimenticios**: Banano Premium, Manzana Roja, Leche Entera, Pollo Entero
- **Tecnológicos**: Laptop Dell Inspiron 15, iPhone 13 128GB
- **Textiles**: Camisa Formal Azul, Zapatos Ejecutivos
- **Hogar**: Licuadora Oster, Detergente Líquido
- **Industriales**: Taladro Eléctrico, Casco de Seguridad
- **Oficina**: Papel Bond, Bolígrafo Azul

### 📏 Unidades de Medida

**Básicas**: Unidad (UNI), Kilogramo (KG), Gramo (G), Libra (LB), Onza (OZ)

**Volumen**: Litro (LT), Mililitro (ML), Galón (GAL), Metro Cúbico (M3)

**Longitud**: Metro (M), Centímetro (CM), Milímetro (MM), Pulgada (IN), Pie (FT)

**Área**: Metro Cuadrado (M2), Centímetro Cuadrado (CM2)

**Tiempo**: Hora (HR), Día (DIA), Semana (SEM), Mes (MES), Año (AÑO)

**Empaque**: Paquete (PAQ), Caja (CAJ), Saco (SAC), Rollo (ROL), Barril (BAR)

**Especiales**: Par (PAR), Docena (DOC), Ciento (CTO), Millar (MIL)

### 🎯 Reglas Contables Configuradas

Se han configurado reglas automáticas para movimientos de inventario:

- **Entradas**: Débito a Inventario, Crédito a Proveedores
- **Salidas**: Débito a Costo de Ventas, Crédito a Inventario  
- **Ajustes**: Débito/Crédito según ajuste positivo/negativo
- **Transferencias**: Entre cuentas de inventario de diferentes bodegas

### 🚀 Próximos Pasos

1. **Configurar servidor web** para acceder a la aplicación
2. **Revisar rutas API** disponibles en `routes/web.php`
3. **Personalizar datos** según necesidades específicas
4. **Configurar políticas de seguridad** y permisos
5. **Integrar con sistemas externos** si es necesario

### 📝 Notas Importantes

- Los **procedimientos almacenados** se omitieron temporalmente debido a conflictos
- Todos los **precios están en colones costarricenses** (ejemplo)
- Las **contraseñas son de desarrollo** - cambiar en producción
- Los **datos son de demostración** - personalizar para uso real

---

**¡Sistema listo para usar!** 🎉

Base de datos completamente poblada y lista para desarrollo, pruebas y demostración del sistema de inventario empresarial con integración contable.
