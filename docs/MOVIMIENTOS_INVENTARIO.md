# Sistema de Movimientos de Inventario - Laravel

## 📋 Descripción
Sistema completo para gestionar movimientos de inventario con formularios dinámicos usando Blade, Bootstrap 5 y JavaScript. Integrado con el `InventoryMovementService` para crear y contabilizar movimientos automáticamente.

## 🏗️ Arquitectura Implementada

### **Controladores**
- `MovimientoInventarioController.php` - Controlador principal con métodos CRUD completos
- Integración directa con `InventoryMovementService@createAndPost`
- Manejo de errores con transacciones de base de datos
- Respuestas AJAX y web tradicional

### **Rutas Configuradas**
```php
// Rutas para Movimientos de Inventario
Route::resource('movimientos', MovimientoInventarioController::class)->only(['index', 'create', 'store']);
Route::get('movimientos-options', [MovimientoInventarioController::class, 'getFormOptions'])->name('movimientos.options');
Route::get('movimientos-list', [MovimientoInventarioController::class, 'getMovements'])->name('movimientos.list');
Route::get('producto-cost', [MovimientoInventarioController::class, 'getProductCost'])->name('producto.cost');
```

### **Vistas Blade**
1. **`movimientos/index.blade.php`** - Listado con DataTables y estadísticas
2. **`movimientos/create.blade.php`** - Formulario dinámico con líneas de productos
3. **`layouts/app.blade.php`** - Layout base actualizado con navegación

## ⚡ Funcionalidades Principales

### **Tipos de Movimiento Soportados**
1. **Entrada** - Productos que ingresan al inventario
2. **Salida** - Productos que salen del inventario  
3. **Ajuste** - Ajustes de existencias
4. **Transferencia** - Transferencias entre bodegas

### **Lógica de Bodegas**
- **Entrada**: Solo bodega destino
- **Salida**: Solo bodega origen
- **Ajuste**: Solo bodega destino
- **Transferencia**: Bodega origen Y destino

### **Gestión de Costos**
- **Editable**: Entradas y Ajustes (campo `costo_unitario` habilitado)
- **Solo Lectura**: Salidas y Transferencias (costo calculado por triggers)
- Carga automática de costos promedio desde existencias

## 🎨 Interfaz de Usuario

### **Dashboard de Estadísticas**
- Cards con contadores por tipo de movimiento
- Iconos diferenciados y colores temáticos
- Actualización automática vía AJAX

### **Formulario Dinámico de Líneas**
- Tabla responsive con productos dinámicos
- Selección de productos con información completa
- Cálculo automático de subtotales y total general
- Validación en tiempo real

### **Características JavaScript**
- **Líneas Dinámicas**: Agregar/eliminar productos fácilmente
- **Cálculos Automáticos**: Subtotales y total general en tiempo real
- **Validación**: Formulario y líneas con feedback visual
- **Carga de Costos**: AJAX para obtener costos promedio por bodega
- **Responsive**: Adaptado para móviles y tablets

### **Modal de Resultado**
- Confirmación visual del movimiento creado
- **Información del Asiento Contable generado**
- Opciones para crear nuevo movimiento o ver listado

## 🔧 Validaciones Implementadas

### **Servidor (Laravel)**
```php
'tipo_movimiento' => 'required|in:entrada,salida,ajuste,transferencia',
'bodega_origen_id' => 'required_if:tipo_movimiento,salida,transferencia|exists:bodegas,id',
'bodega_destino_id' => 'required_if:tipo_movimiento,entrada,transferencia|exists:bodegas,id',
'lineas' => 'required|array|min:1',
'lineas.*.producto_id' => 'required|exists:productos,id',
'lineas.*.cantidad' => 'required|numeric|min:0.01',
'lineas.*.costo_unitario' => 'required_if:tipo_movimiento,entrada,ajuste|numeric|min:0',
```

### **Cliente (JavaScript)**
- Validación de tipo de movimiento seleccionado
- Verificación de al menos una línea de producto
- Validación de campos requeridos por tipo
- Feedback visual con colores y mensajes

## 📊 Integración con Servicio

### **Llamada al InventoryMovementService**
```php
$resultado = $this->inventoryService->createAndPost($movementData);
```

### **Respuesta del Servicio**
```php
[
    'movimiento_id' => $resultado['movimiento']->id,
    'asiento_numero' => $resultado['asiento']->numero,
    'asiento_id' => $resultado['asiento']->id,
    'tipo_movimiento' => $resultado['movimiento']->tipo_movimiento,
]
```

### **Notificación de Asiento Generado**
- Modal con número de asiento contable
- ID del movimiento creado
- Opciones de navegación post-creación

## 🎯 Características Avanzadas

### **Carga Dinámica de Datos**
- Productos con información completa (código, nombre, categoría, unidad)
- Bodegas activas ordenadas alfabéticamente
- Costos promedio por bodega via AJAX

### **Experiencia de Usuario**
- **Loading States**: Indicadores de carga durante operaciones
- **Toast Notifications**: Feedback inmediato de acciones
- **SweetAlert2**: Confirmaciones elegantes
- **DataTables**: Tabla paginada y filtrable en español

### **Responsive Design**
- Bootstrap 5 con diseño mobile-first
- Sidebar colapsable en dispositivos móviles
- Tabla responsive para móviles
- Formularios adaptados a pantallas pequeñas

## 🔐 Seguridad

### **CSRF Protection**
- Token CSRF en todos los formularios AJAX
- Headers X-Requested-With para validar peticiones AJAX

### **Validación de Datos**
- Sanitización de inputs
- Validación tanto en frontend como backend
- Manejo seguro de errores

### **Transacciones de Base de Datos**
- Rollback automático en caso de error
- Logging de errores para debugging
- Manejo de excepciones robusto

## 🚀 Uso del Sistema

1. **Acceder**: `/movimientos` para ver el listado
2. **Crear**: Clic en "Nuevo Movimiento"
3. **Configurar**: Seleccionar tipo y bodegas
4. **Agregar Productos**: Líneas dinámicas con cálculos automáticos
5. **Guardar**: El sistema crea el movimiento y genera el asiento contable
6. **Confirmación**: Modal con número de asiento generado

## 📝 Próximas Mejoras Sugeridas

1. **Impresión**: Generación de reportes PDF de movimientos
2. **Exportación**: Excel de movimientos con filtros
3. **Historial**: Vista detallada de líneas por movimiento
4. **Cancelación**: Funcionalidad para anular movimientos
5. **Notificaciones**: Alertas por email de movimientos importantes

---

**✅ Sistema Completamente Funcional** - Listo para producción con todas las validaciones, seguridad y experiencia de usuario implementadas.
