@extends('layouts.app')

@section('title', 'Gestión de Productos')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('header')
<div class="page-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="mb-0">
                    <i class="fas fa-box me-3"></i>Gestión de Productos
                </h1>
                <p class="mb-0 mt-2 opacity-75">Administra el catálogo de productos del inventario</p>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-light btn-lg" onclick="nuevoProducto()">
                    <i class="fas fa-plus me-2"></i>Nuevo Producto
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="fas fa-box fa-2x mb-2"></i>
                    <h4 class="mb-0" id="totalProductos">0</h4>
                    <small>Total Productos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h4 class="mb-0" id="productosActivos">0</h4>
                    <small>Productos Activos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h4 class="mb-0" id="sinStock">0</h4>
                    <small>Sin Stock</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="fas fa-tags fa-2x mb-2"></i>
                    <h4 class="mb-0" id="categorias">0</h4>
                    <small>Categorías</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Lista de Productos
            </h5>
            <div class="card-tools">
                <button class="btn btn-outline-secondary btn-sm" onclick="recargarTabla()">
                    <i class="fas fa-sync"></i> Actualizar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="productosTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Unidad</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Include modals --}}
@include('productos.modal-form')
@include('productos.modal-existencias')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/app-utils.js') }}"></script>

<script>
// URLs configuration for productos.js
window.productosUrls = {
    tableData: '{{ route("productos.index") }}',
    options: '{{ route("productos.options") }}',
    store: '{{ route("productos.store") }}',
    edit: '{{ route("productos.edit", ":id") }}',
    update: '{{ route("productos.update", ":id") }}',
    destroy: '{{ route("productos.destroy", ":id") }}',
    toggle: '{{ route("productos.toggle", ":id") }}',
    existencias: '{{ route("productos.existencias", ":id") }}'
};
</script>

<script src="{{ asset('js/productos.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="fas fa-box fa-2x mb-2"></i>
                    <h4 class="mb-0" id="totalProductos">0</h4>
                    <small>Total Productos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h4 class="mb-0" id="productosActivos">0</h4>
                    <small>Productos Activos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h4 class="mb-0" id="sinStock">0</h4>
                    <small>Sin Stock</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="fas fa-tags fa-2x mb-2"></i>
                    <h4 class="mb-0" id="categorias">0</h4>
                    <small>Categorías</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Lista de Productos
            </h5>
            <div class="card-tools">
                <button class="btn btn-outline-secondary btn-sm" onclick="recargarTabla()">
                    <i class="fas fa-sync"></i> Actualizar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="productosTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Unidad</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar Producto -->
<div class="modal fade" id="productoModal" tabindex="-1" aria-labelledby="productoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productoModalLabel">
                    <i class="fas fa-box me-2"></i>Nuevo Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="productoForm">
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-info-circle me-1"></i>Información Básica
                            </h6>
                            
                            <div class="mb-3">
                                <label for="sku" class="form-label">SKU/Código *</label>
                                <input type="text" class="form-control" id="sku" name="sku" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Clasificación -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-tags me-1"></i>Clasificación
                            </h6>
                            
                            <div class="mb-3">
                                <label for="categoria_id" class="form-label">Categoría *</label>
                                <select class="form-select" id="categoria_id" name="categoria_id" required>
                                    <option value="">Seleccionar categoría...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="unidad_id" class="form-label">Unidad de Medida *</label>
                                <select class="form-select" id="unidad_id" name="unidad_id" required>
                                    <option value="">Seleccionar unidad...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                                <label class="form-check-label" for="activo">
                                    Producto Activo
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Precios y Stock -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-dollar-sign me-1"></i>Precios y Stock
                            </h6>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="precio_compra" class="form-label">Precio Compra</label>
                                <input type="number" class="form-control" id="precio_compra" name="precio_compra" step="0.01" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="precio_venta" class="form-label">Precio Venta</label>
                                <input type="number" class="form-control" id="precio_venta" name="precio_venta" step="0.01" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                                <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" step="0.01" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="stock_maximo" class="form-label">Stock Máximo</label>
                                <input type="number" class="form-control" id="stock_maximo" name="stock_maximo" step="0.01" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cuentas Contables -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-calculator me-1"></i>Cuentas Contables
                            </h6>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cuenta_inventario_id" class="form-label">Cuenta Inventario *</label>
                                <select class="form-select" id="cuenta_inventario_id" name="cuenta_inventario_id" required>
                                    <option value="">Seleccionar cuenta...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cuenta_costo_id" class="form-label">Cuenta Costo *</label>
                                <select class="form-select" id="cuenta_costo_id" name="cuenta_costo_id" required>
                                    <option value="">Seleccionar cuenta...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cuenta_contraparte_id" class="form-label">Cuenta Contraparte *</label>
                                <select class="form-select" id="cuenta_contraparte_id" name="cuenta_contraparte_id" required>
                                    <option value="">Seleccionar cuenta...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Ver Existencias -->
<div class="modal fade" id="existenciasModal" tabindex="-1" aria-labelledby="existenciasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="existenciasModalLabel">
                    <i class="fas fa-boxes me-2"></i>Existencias por Bodega
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="existenciasContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let productosTable;
    let editingProductId = null;

    $(document).ready(function() {
        // Initialize DataTable
        initializeTable();
        
        // Load form options
        loadFormOptions();
        
        // Load statistics
        loadStatistics();
        
        // Form submission
        $('#productoForm').on('submit', handleFormSubmit);
    });

    function initializeTable() {
        productosTable = $('#productosTable').DataTable({
            ajax: {
                url: '{{ route("productos.index") }}',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            },
            columns: [
                { data: 'codigo' },
                { data: 'nombre' },
                { data: 'categoria_nombre' },
                { data: 'unidade_nombre' },
                { data: 'precio_formateado' },
                { 
                    data: 'estado',
                    render: function(data, type, row) {
                        const badgeClass = row.activo ? 'bg-success' : 'bg-danger';
                        return `<span class="badge ${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data, type, row) {
                        const toggleClass = row.activo ? 'btn-secondary' : 'btn-success';
                        const toggleTitle = row.activo ? 'Desactivar' : 'Activar';
                        const toggleIcon = row.activo ? 'fas fa-eye-slash' : 'fas fa-eye';
                        
                        return `
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info me-1" onclick="verExistencias(${data})" title="Ver Existencias">
                                    <i class="fas fa-boxes"></i>
                                </button>
                                <button class="btn btn-sm btn-warning me-1" onclick="editarProducto(${data})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm ${toggleClass} me-1" onclick="toggleEstado(${data})" title="${toggleTitle}">
                                    <i class="${toggleIcon}"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${data})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            ...window.DataTablesSpanish,
            responsive: true,
            pageLength: 25
        });
    }

    function loadFormOptions() {
        $.get('{{ route("productos.options") }}')
            .done(function(response) {
                if (response.success) {
                    // Load categories
                    const categoriaSelect = $('#categoria_id');
                    categoriaSelect.empty().append('<option value="">Seleccionar categoría...</option>');
                    response.data.categorias.forEach(function(categoria) {
                        categoriaSelect.append(`<option value="${categoria.id}">${categoria.nombre}</option>`);
                    });

                    // Load units
                    const unidadSelect = $('#unidad_id');
                    unidadSelect.empty().append('<option value="">Seleccionar unidad...</option>');
                    response.data.unidades.forEach(function(unidade) {
                        unidadSelect.append(`<option value="${unidade.id}">${unidade.nombre} (${unidade.abreviatura || ''})</option>`);
                    });

                    // Load accounts
                    const cuentaSelects = ['#cuenta_inventario_id', '#cuenta_costo_id', '#cuenta_contraparte_id'];
                    cuentaSelects.forEach(function(selector) {
                        const select = $(selector);
                        select.empty().append('<option value="">Seleccionar cuenta...</option>');
                        response.data.cuentas.forEach(function(cuenta) {
                            select.append(`<option value="${cuenta.id}">${cuenta.codigo} - ${cuenta.nombre}</option>`);
                        });
                    });
                }
            })
            .fail(handleAjaxError);
    }

    function loadStatistics() {
        $.get('/api/inventario/estadisticas')
            .done(function(response) {
                if (response.estadisticas) {
                    $('#totalProductos').text(response.estadisticas.total_productos);
                    $('#productosActivos').text(response.estadisticas.productos_activos);
                    $('#sinStock').text(response.estadisticas.sin_stock);
                    $('#categorias').text(response.estadisticas.categorias_utilizadas);
                }
            })
            .fail(function() {
                // Fail silently for statistics
            });
    }

    function nuevoProducto() {
        editingProductId = null;
        $('#productoForm')[0].reset();
        $('#productoModalLabel').html('<i class="fas fa-box me-2"></i>Nuevo Producto');
        clearFormErrors();
        $('#productoModal').modal('show');
    }

    function editarProducto(id) {
        editingProductId = id;
        showLoading();
        
        $.get(`{{ route("productos.index") }}/${id}/edit`)
            .done(function(response) {
                if (response.success) {
                    const producto = response.data.producto;
                    
                    // Fill form
                    $('#sku').val(producto.codigo);
                    $('#nombre').val(producto.nombre);
                    $('#descripcion').val(producto.descripcion);
                    $('#categoria_id').val(producto.categoria_id);
                    $('#unidad_id').val(producto.unidad_id);
                    $('#precio_compra').val(producto.precio_compra);
                    $('#precio_venta').val(producto.precio_venta);
                    $('#stock_minimo').val(producto.stock_minimo);
                    $('#stock_maximo').val(producto.stock_maximo);
                    $('#cuenta_inventario_id').val(producto.cuenta_inventario_id);
                    $('#cuenta_costo_id').val(producto.cuenta_costo_id);
                    $('#cuenta_contraparte_id').val(producto.cuenta_contraparte_id);
                    $('#activo').prop('checked', producto.activo);
                    
                    $('#productoModalLabel').html('<i class="fas fa-edit me-2"></i>Editar Producto');
                    clearFormErrors();
                    $('#productoModal').modal('show');
                }
                hideLoading();
            })
            .fail(function(xhr) {
                hideLoading();
                handleAjaxError(xhr);
            });
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const url = editingProductId ? 
            `{{ route("productos.index") }}/${editingProductId}` : 
            '{{ route("productos.store") }}';
        
        if (editingProductId) {
            formData.append('_method', 'PUT');
        }
        
        showLoading();
        clearFormErrors();
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .done(function(response) {
            hideLoading();
            if (response.success) {
                $('#productoModal').modal('hide');
                productosTable.ajax.reload();
                loadStatistics();
                showToast(response.message);
            }
        })
        .fail(function(xhr) {
            hideLoading();
            if (xhr.status === 422) {
                displayFormErrors(xhr.responseJSON.errors);
            } else {
                handleAjaxError(xhr);
            }
        });
    }

    function toggleEstado(id) {
        Swal.fire({
            title: '¿Cambiar estado?',
            text: '¿Está seguro de cambiar el estado del producto?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ route("productos.index") }}/${id}/toggle`,
                    type: 'PATCH',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .done(function(response) {
                    if (response.success) {
                        productosTable.ajax.reload();
                        loadStatistics();
                        showToast(response.message);
                    }
                })
                .fail(handleAjaxError);
            }
        });
    }

    function eliminarProducto(id) {
        Swal.fire({
            title: '¿Eliminar producto?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ route("productos.destroy", ":id") }}`.replace(':id', id),
                    type: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .done(function(response) {
                    if (response.success) {
                        productosTable.ajax.reload();
                        loadStatistics();
                        showToast(response.message);
                    }
                })
                .fail(handleAjaxError);
            }
        });
    }

    function verExistencias(id) {
        showLoading();
        
        $.get(`{{ route("productos.index") }}/${id}/existencias`)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    displayExistencias(response.data);
                    $('#existenciasModal').modal('show');
                }
            })
            .fail(function(xhr) {
                hideLoading();
                handleAjaxError(xhr);
            });
    }

    function displayExistencias(data) {
        const producto = data.producto;
        const existencias = data.existencias;
        const resumen = data.resumen;
        
        let html = `
            <div class="mb-4">
                <h6><strong>Producto:</strong> ${producto.codigo} - ${producto.nombre}</h6>
            </div>
        `;
        
        if (existencias.length > 0) {
            html += `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Bodega</th>
                                <th>Código</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Costo Promedio</th>
                                <th class="text-end">Valor Total</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            existencias.forEach(function(existencia) {
                html += `
                    <tr>
                        <td>${existencia.bodega_nombre}</td>
                        <td>${existencia.bodega_codigo}</td>
                        <td class="text-end">${parseFloat(existencia.cantidad).toFixed(2)}</td>
                        <td class="text-end">$${parseFloat(existencia.costo_promedio).toFixed(2)}</td>
                        <td class="text-end">$${parseFloat(existencia.valor_total).toFixed(2)}</td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="2">TOTAL</th>
                                <th class="text-end">${parseFloat(resumen.total_existencias).toFixed(2)}</th>
                                <th class="text-end">$${parseFloat(resumen.costo_promedio_general).toFixed(2)}</th>
                                <th class="text-end">$${parseFloat(resumen.valor_total_inventario).toFixed(2)}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
        } else {
            html += `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Este producto no tiene existencias en ninguna bodega.
                </div>
            `;
        }
        
        $('#existenciasContent').html(html);
    }

    function recargarTabla() {
        productosTable.ajax.reload();
        loadStatistics();
        showToast('Tabla actualizada', 'info');
    }

    function clearFormErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    function displayFormErrors(errors) {
        Object.keys(errors).forEach(function(field) {
            const input = $(`[name="${field}"]`);
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(errors[field][0]);
        });
    }
</script>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/app-utils.js') }}"></script>
@endpush
@endsection
