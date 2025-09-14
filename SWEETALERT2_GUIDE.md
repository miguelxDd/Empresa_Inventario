# Guía de Uso - SweetAlert2 y Utilidades

## Funciones Disponibles

### 1. Configuración de SweetAlert2
```javascript
// Funciones del objeto Swal2Config
Swal2Config.success('Título', 'Texto opcional');
Swal2Config.error('Error', 'Descripción del error');
Swal2Config.warning('Advertencia', 'Mensaje de advertencia');
Swal2Config.info('Información', 'Mensaje informativo');

// Confirmación
Swal2Config.confirm('¿Estás seguro?', 'Esta acción no se puede deshacer', 'Sí, eliminar')
    .then((result) => {
        if (result.isConfirmed) {
            // Acción confirmada
        }
    });
```

### 2. Funciones Compatibles (ya implementadas en el sistema)
```javascript
// Loading overlay
showLoading('Procesando datos...');
hideLoading();

// Toast notifications
showToast('Mensaje de éxito', 'success');
showToast('Mensaje de error', 'error');
showToast('Información', 'info');
showToast('Advertencia', 'warning');

// Manejo de errores AJAX
handleAjaxError(xhr, status, error);
```

### 3. DataTables en Español
```javascript
// Configuración automática para todas las tablas
$('#tabla').DataTable({
    // ... otras opciones
    ...window.DataTablesSpanish,  // Esto agrega el idioma español
    // ... más opciones
});
```

## Cambios Realizados

### ✅ Problema CORS Solucionado
- **Antes**: `url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'`
- **Después**: `...window.DataTablesSpanish` (configuración local)

### ✅ SweetAlert2 Implementado
- Todas las alertas ahora usan SweetAlert2
- Estilos Bootstrap 5 integrados
- Funciones compatibles con código existente

### ✅ Archivos Actualizados
- `public/js/app-utils.js` - Nuevas utilidades
- `resources/views/layouts/app.blade.php` - Layout limpiado
- `resources/views/productos/index.blade.php` - DataTables actualizado
- `resources/views/movimientos/index.blade.php` - DataTables actualizado
- `resources/views/reportes/existencias.blade.php` - DataTables actualizado
- `resources/views/reportes/asientos.blade.php` - DataTables actualizado

## Estado Actual
- ✅ Sin errores CORS
- ✅ SweetAlert2 funcionando
- ✅ DataTables en español
- ✅ Compatibilidad total con código existente
