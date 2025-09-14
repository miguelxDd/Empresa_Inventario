# Sistema de Reportes de Inventario - Laravel

## 📋 Descripción
Sistema completo de reportes para inventario que incluye Kardex por producto, existencias con filtros y exportación CSV, y listado de asientos contables con enlaces a movimientos origen.

## 🏗️ Arquitectura Implementada

### **Controlador Principal**
- `ReporteController.php` - Controlador único que maneja todos los reportes
- Consultas optimizadas usando índices de base de datos existentes
- Métodos especializados para cada tipo de reporte
- Exportación CSV nativa de PHP para mejor rendimiento

### **Rutas Configuradas**
```php
// Rutas para Reportes
Route::prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReporteController::class, 'index'])->name('index');
    
    // Kardex
    Route::get('/kardex', [ReporteController::class, 'kardex'])->name('kardex');
    Route::post('/kardex/generar', [ReporteController::class, 'generateKardex'])->name('kardex.generar');
    
    // Existencias
    Route::get('/existencias', [ReporteController::class, 'existencias'])->name('existencias');
    Route::get('/existencias/data', [ReporteController::class, 'getExistencias'])->name('existencias.data');
    Route::get('/existencias/export', [ReporteController::class, 'exportExistenciasCSV'])->name('existencias.export');
    
    // Asientos Contables
    Route::get('/asientos', [ReporteController::class, 'asientos'])->name('asientos');
    Route::get('/asientos/data', [ReporteController::class, 'getAsientos'])->name('asientos.data');
    
    // Opciones generales
    Route::get('/options', [ReporteController::class, 'getOptions'])->name('options');
});
```

### **Vistas Blade Implementadas**
1. **`reportes/index.blade.php`** - Dashboard principal de reportes
2. **`reportes/kardex.blade.php`** - Reporte de kardex con filtros avanzados
3. **`reportes/existencias.blade.php`** - Reporte de existencias con exportación
4. **`reportes/asientos.blade.php`** - Listado de asientos contables

## 📊 Reportes Implementados

### **1. Kardex de Productos**

#### **Características:**
- ✅ **Filtro por producto específico** (obligatorio)
- ✅ **Filtro por bodega** (opcional - todas las bodegas si no se especifica)
- ✅ **Rango de fechas personalizable**
- ✅ **Períodos predefinidos** (Hoy, Semana, Mes, Año)
- ✅ **Saldo inicial calculado** antes del período
- ✅ **Detalle de movimientos** con entrada, salida y saldo corriente
- ✅ **Resumen del período** con totales y valores

#### **Información Mostrada:**
```
- Saldo inicial del producto/bodega
- Por cada movimiento:
  * Fecha y tipo de movimiento
  * Observaciones
  * Bodegas origen/destino
  * Cantidad entrada/salida
  * Saldo acumulado
  * Costo unitario
  * Valor de entrada/salida
- Resumen:
  * Total entradas/salidas en cantidad
  * Valor total entradas/salidas
  * Saldo final del período
```

#### **Consultas Optimizadas:**
- Usa índices en `movimientos_inventario.fecha` y `lineas_movimiento.producto_id`
- Cálculo eficiente de saldo inicial con agregaciones
- Joins optimizados con bodegas para nombres descriptivos

### **2. Reporte de Existencias**

#### **Características:**
- ✅ **Vista consolidada** de todas las existencias actuales
- ✅ **Filtros múltiples**: Producto, Bodega, Categoría
- ✅ **Filtro especial** para productos con stock bajo
- ✅ **Estadísticas en tiempo real** en dashboard
- ✅ **Exportación completa a CSV** con codificación UTF-8
- ✅ **DataTable responsive** con paginación

#### **Información Mostrada:**
```
- Por cada existencia:
  * Código y nombre del producto
  * Categoría del producto
  * Unidad de medida con símbolo
  * Código y nombre de bodega
  * Cantidad actual
  * Costo promedio
  * Valor total (cantidad × costo)
  * Fecha última actualización
- Resumen:
  * Total de registros
  * Cantidad total consolidada
  * Valor total del inventario
  * Número de bodegas con stock
```

#### **Exportación CSV:**
- Archivo con codificación UTF-8 y BOM
- Separador punto y coma (;) para compatibilidad Excel
- Nombres de archivo con timestamp
- Headers descriptivos en español
- Formateo de números con comas decimales

### **3. Asientos Contables**

#### **Características:**
- ✅ **Listado completo** de asientos contables
- ✅ **Filtros por fecha**, número de asiento y origen
- ✅ **Enlaces directos** a movimientos de inventario origen
- ✅ **Filtros rápidos** (Hoy, Este Mes)
- ✅ **Identificación visual** por tipo de movimiento
- ✅ **Estadísticas de débitos y créditos**

#### **Información Mostrada:**
```
- Por cada asiento:
  * Número del asiento contable
  * Fecha del asiento
  * Concepto/descripción
  * Total débitos y créditos
  * Tabla origen (movimientos_inventario u otros)
  * Tipo de movimiento de inventario
  * Observaciones del movimiento
- Enlaces:
  * Botón para ver detalle del asiento
  * Botón para ver movimiento origen (si aplica)
  * Opción de impresión
```

#### **Integración con Movimientos:**
- Campo `origen_tabla = 'movimientos_inventario'`
- Campo `origen_id` apunta al ID del movimiento
- Join automático para mostrar información del movimiento
- Badges con colores por tipo de movimiento

## 🔧 Optimizaciones Implementadas

### **Consultas de Base de Datos:**
```sql
-- Kardex: Usa índices en fechas y productos
SELECT * FROM lineas_movimiento lm
JOIN movimientos_inventario mi ON lm.movimiento_id = mi.id
WHERE lm.producto_id = ? 
  AND mi.fecha BETWEEN ? AND ?
ORDER BY mi.fecha, mi.id

-- Existencias: Join optimizado con todas las tablas relacionadas
SELECT e.*, p.codigo, p.nombre, c.nombre as categoria, u.simbolo
FROM existencias e
JOIN productos p ON e.producto_id = p.id
LEFT JOIN categorias c ON p.categoria_id = c.id
WHERE e.cantidad > 0

-- Asientos: Join condicional con movimientos
SELECT ac.*, mi.tipo_movimiento
FROM asientos_contables ac
LEFT JOIN movimientos_inventario mi ON ac.origen_id = mi.id 
  AND ac.origen_tabla = 'movimientos_inventario'
```

### **Cálculos Eficientes:**
- **Saldo Inicial**: Agregación por tipo de movimiento antes del período
- **Kardex**: Cálculo secuencial de saldos con un solo recorrido
- **Totales**: Uso de agregaciones SQL en lugar de cálculos PHP

### **Memoria y Rendimiento:**
- **Stream CSV**: Escritura directa sin cargar datos en memoria
- **Paginación**: DataTables server-side para grandes volúmenes
- **Lazy Loading**: Carga de datos por demanda via AJAX

## 🎨 Interfaz de Usuario

### **Dashboard Principal:**
- Cards con estadísticas generales del sistema
- Acceso directo a cada tipo de reporte
- Descripción detallada de cada funcionalidad
- Links de navegación intuitivos

### **Formularios de Filtros:**
- Dropdowns precargados con datos disponibles
- Rangos de fechas con calendarios
- Filtros rápidos por períodos comunes
- Botones de limpiar y aplicar filtros

### **Tablas de Resultados:**
- DataTables con ordenamiento y búsqueda
- Responsive design para móviles
- Botones de acción contextual
- Estadísticas en tiempo real

### **Características UX:**
- **Loading States**: Indicadores durante carga de datos
- **Toast Notifications**: Feedback inmediato de acciones
- **Responsive**: Adaptado para todos los dispositivos
- **Print Friendly**: Estilos optimizados para impresión

## 🔐 Seguridad y Validación

### **Validaciones de Entrada:**
```php
// Kardex
'producto_id' => 'required|exists:productos,id',
'bodega_id' => 'nullable|exists:bodegas,id',
'fecha_inicio' => 'required|date',
'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',

// Filtros de existencias y asientos
'producto_id' => 'nullable|exists:productos,id',
'bodega_id' => 'nullable|exists:bodegas,id',
'fecha_inicio' => 'nullable|date',
'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
```

### **Sanitización y Escape:**
- Escape automático de datos en vistas Blade
- Validación de tipos de datos en controladores
- Filtrado de parámetros SQL con prepared statements

### **Control de Errores:**
- Try-catch en todas las operaciones críticas
- Logging de errores para debugging
- Respuestas JSON consistentes
- Manejo graceful de fallos

## 📈 Casos de Uso

### **Kardex de Productos:**
1. **Auditoría de Movimientos**: Revisar historial completo de un producto
2. **Análisis de Rotación**: Identificar productos con alta/baja rotación
3. **Conciliación de Inventarios**: Verificar diferencias de saldos
4. **Costeo de Productos**: Analizar evolución de costos unitarios

### **Reporte de Existencias:**
1. **Inventario Físico**: Base para conteos físicos
2. **Planificación de Compras**: Identificar productos bajo stock mínimo
3. **Valorización**: Calcular valor total del inventario
4. **Distribución por Bodegas**: Analizar concentración de stock

### **Asientos Contables:**
1. **Auditoría Contable**: Revisar asientos generados automáticamente
2. **Conciliación**: Verificar que todos los movimientos generaron asientos
3. **Trazabilidad**: Seguir la pista desde asiento hasta movimiento origen
4. **Reportes Financieros**: Base para estados financieros

## 🚀 Acceso y Navegación

### **URLs del Sistema:**
- **Dashboard**: `/reportes`
- **Kardex**: `/reportes/kardex`
- **Existencias**: `/reportes/existencias`
- **Asientos**: `/reportes/asientos`

### **Navegación:**
- Menú lateral con enlace a "Reportes"
- Breadcrumbs en cada vista
- Botones de "Volver" en subvistas
- Enlaces contextuales entre reportes

## 📝 Próximas Mejoras Sugeridas

1. **Gráficos**: Visualización con Chart.js de tendencias y distribuciones
2. **Programación**: Reportes automáticos vía email
3. **Más Formatos**: Exportación a PDF y Excel nativo
4. **Filtros Avanzados**: Rangos de valores, múltiple selección
5. **Cache**: Almacenamiento temporal de consultas pesadas

---

**✅ Sistema de Reportes Completamente Funcional** - Implementado con consultas optimizadas, interfaz moderna y exportación eficiente.
