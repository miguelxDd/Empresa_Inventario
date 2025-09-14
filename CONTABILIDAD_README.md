# Sistema de Contabilidad - Empresa Inventario

## Módulos Implementados

El sistema incluye tres módulos principales de contabilidad accesibles desde el menú lateral:

### 1. Libro Diario (`/contabilidad/diario`)

**Funcionalidad:**
- Visualiza todos los asientos contables en orden cronológico
- Muestra fecha, número, descripción, cuenta (código/nombre), debe, haber y concepto
- Incluye totales de debe y haber al pie de la tabla
- Enlaces a asientos y movimientos de inventario relacionados

**Filtros:**
- Rango de fechas (obligatorio)

**Características:**
- Exportación a CSV con formato UTF-8
- Tabla responsive con DataTables
- Paginación y búsqueda
- Modales para ver detalles de asientos y movimientos

### 2. Libro Mayor (`/contabilidad/mayor`)

**Funcionalidad:**
- Muestra movimientos de una cuenta específica
- Incluye saldo inicial, movimientos del período y saldo final
- Cálculo de saldos acumulados por cada movimiento
- Resumen con saldos inicial y final

**Filtros:**
- Rango de fechas (obligatorio)
- Cuenta específica (autocomplete por código/nombre)

**Características:**
- Selector de cuentas con búsqueda inteligente (Select2)
- Cálculo automático de saldos acumulados
- Exportación detallada a CSV
- Información de la cuenta seleccionada

### 3. Balanza de Comprobación (`/contabilidad/balanza`)

**Funcionalidad:**
- Resume saldos de todas las cuentas con movimientos
- Agrupa por tipo de cuenta (activo, pasivo, patrimonio, ingreso, gasto)
- Muestra debe, haber, saldo deudor y saldo acreedor
- Verificación automática de balance contable

**Filtros:**
- Rango de fechas (obligatorio)

**Características:**
- Resumen por tipo de cuenta
- Indicadores visuales de balance
- Verificación de balance debe/haber y saldos
- Exportación completa con resúmenes

## Estructura Técnica

### Rutas
```php
Route::prefix('contabilidad')->name('contabilidad.')->group(function () {
    // Libro Diario
    Route::get('/diario', [DiarioController::class, 'index'])->name('diario');
    Route::get('/diario/data', [DiarioController::class, 'getData'])->name('diario.data');
    Route::get('/diario/export', [DiarioController::class, 'export'])->name('diario.export');
    
    // Libro Mayor
    Route::get('/mayor', [MayorController::class, 'index'])->name('mayor');
    Route::get('/mayor/data', [MayorController::class, 'getData'])->name('mayor.data');
    Route::get('/mayor/export', [MayorController::class, 'export'])->name('mayor.export');
    
    // Balanza de Comprobación
    Route::get('/balanza', [BalanzaController::class, 'index'])->name('balanza');
    Route::get('/balanza/data', [BalanzaController::class, 'getData'])->name('balanza.data');
    Route::get('/balanza/export', [BalanzaController::class, 'export'])->name('balanza.export');
    
    // API para autocomplete
    Route::get('/cuentas/search', [CuentaController::class, 'search'])->name('cuentas.search');
});
```

### Controladores
- **DiarioController**: Maneja el libro diario con consultas optimizadas
- **MayorController**: Gestiona el libro mayor con cálculos de saldos
- **BalanzaController**: Procesa la balanza con agrupaciones por tipo
- **CuentaController**: Proporciona API para búsqueda de cuentas

### Modelos Actualizados
- **Asiento**: Métodos para filtros y relaciones
- **AsientosDetalle**: Relaciones con asientos y cuentas
- **Cuenta**: Scopes para búsquedas y validaciones

### Vistas
- Blade templates responsive con Bootstrap 5
- DataTables para manejo eficiente de datos
- Select2 para búsquedas avanzadas
- SweetAlert2 para notificaciones
- Modales para detalles adicionales

## Consultas SQL Optimizadas

### Libro Diario
```sql
SELECT a.fecha, a.numero, a.descripcion, c.codigo, c.nombre, 
       ad.debe, ad.haber, ad.concepto
FROM asientos a
JOIN asientos_detalle ad ON a.id = ad.asiento_id
JOIN cuentas c ON ad.cuenta_id = c.id
WHERE a.fecha BETWEEN ? AND ?
  AND a.estado = 'confirmado'
ORDER BY a.fecha, a.numero, ad.id
```

### Libro Mayor
```sql
-- Saldo inicial
SELECT SUM(ad.debe - ad.haber) as saldo
FROM asientos a
JOIN asientos_detalle ad ON a.id = ad.asiento_id
WHERE ad.cuenta_id = ? AND a.fecha < ? AND a.estado = 'confirmado'

-- Movimientos del período
SELECT a.fecha, a.numero, ad.debe, ad.haber, ad.concepto
FROM asientos a
JOIN asientos_detalle ad ON a.id = ad.asiento_id
WHERE ad.cuenta_id = ? 
  AND a.fecha BETWEEN ? AND ?
  AND a.estado = 'confirmado'
ORDER BY a.fecha, a.numero
```

### Balanza de Comprobación
```sql
SELECT c.codigo, c.nombre, c.tipo,
       COALESCE(SUM(ad.debe), 0) as total_debe,
       COALESCE(SUM(ad.haber), 0) as total_haber,
       COALESCE(SUM(ad.debe - ad.haber), 0) as saldo
FROM cuentas c
LEFT JOIN asientos_detalle ad ON c.id = ad.cuenta_id
LEFT JOIN asientos a ON ad.asiento_id = a.id
WHERE c.activa = true
  AND a.fecha BETWEEN ? AND ?
  AND a.estado = 'confirmado'
GROUP BY c.id, c.codigo, c.nombre, c.tipo
HAVING total_debe > 0 OR total_haber > 0
ORDER BY c.codigo
```

## Datos de Prueba

Se incluye un seeder `AsientosContabilidadSeeder` que crea:

1. **8 asientos contables** con diferentes tipos de operaciones:
   - Compra de mercaderías
   - Ventas (contado y crédito)
   - Pagos y gastos
   - Nómina y depreciación
   - Ajustes de inventario

2. **16 detalles de asientos** balanceados
3. **Cuentas básicas** si no existen (12 cuentas principales)

Para ejecutar el seeder:
```bash
php artisan db:seed --class=AsientosContabilidadSeeder
```

## Rendimiento y Optimización

### Índices de Base de Datos
- `idx_asientos_fecha` en asientos.fecha
- `idx_asientos_numero` en asientos.numero  
- `idx_asientos_estado` en asientos.estado
- `idx_asientos_detalle_asiento` en asientos_detalle.asiento_id
- `idx_asientos_detalle_cuenta` en asientos_detalle.cuenta_id
- `idx_cuentas_codigo` en cuentas.codigo
- `idx_cuentas_tipo_activa` en (cuentas.tipo, cuentas.activa)

### Características de Rendimiento
- Consultas con INNER/LEFT JOIN optimizadas
- Uso de índices para filtros principales
- Paginación server-side disponible en DataTables
- Carga asíncrona de datos con AJAX
- Exportaciones streaming para archivos grandes

## Uso del Sistema

1. **Acceso**: Menú lateral > Contabilidad > [Módulo deseado]
2. **Filtros**: Seleccionar rango de fechas (obligatorio)
3. **Búsqueda**: En Libro Mayor, seleccionar cuenta específica
4. **Exportación**: Botón "Exportar CSV" disponible tras aplicar filtros
5. **Navegación**: Enlaces a asientos y movimientos relacionados

## Extensiones Futuras

- Reportes en Excel (Laravel-Excel)
- Gráficos interactivos
- Estados financieros automáticos
- Conciliaciones bancarias
- Presupuestos y análisis de variaciones
