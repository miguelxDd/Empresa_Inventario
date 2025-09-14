@extends('layouts.app')

@section('title', 'Reporte de Existencias')

@push('styles')
<link href="{{ asset('css/reportes.css') }}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css"             ],
            order: [[0, 'asc']],
            ...window.DataTablesSpanish,
            responsive: true,tylesheet">
@endpush

@section('header')
<div class="page-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="mb-0">
                    <i class="fas fa-clipboard-list me-3"></i>Reporte de Existencias
                </h1>
                <p class="mb-0 mt-2 opacity-75">Estado actual de inventario por producto y bodega</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Reportes
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filtros de Consulta
            </h5>
        </div>
        <div class="card-body">
            <form id="filtrosForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="producto_id" class="form-label">Producto</label>
                            <select class="form-select" id="producto_id" name="producto_id">
                                <option value="">Todos los productos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="bodega_id" class="form-label">Bodega</label>
                            <select class="form-select" id="bodega_id" name="bodega_id">
                                <option value="">Todas las bodegas</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="categoria_id" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria_id" name="categoria_id">
                                <option value="">Todas las categorías</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="stock_minimo" name="stock_minimo" value="1">
                                <label class="form-check-label" for="stock_minimo">
                                    Solo productos con stock bajo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" onclick="aplicarFiltros()">
                            <i class="fas fa-search me-1"></i>Aplicar Filtros
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-broom me-1"></i>Limpiar
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportarCSV()">
                            <i class="fas fa-file-csv me-1"></i>Exportar CSV
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card border-left-primary">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-2x mb-2 text-primary"></i>
                    <h4 class="mb-0" id="totalRegistros">0</h4>
                    <small>Registros de Existencias</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-left-success">
                <div class="card-body text-center">
                    <i class="fas fa-cubes fa-2x mb-2 text-success"></i>
                    <h4 class="mb-0" id="totalCantidad">0</h4>
                    <small>Cantidad Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-left-warning">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x mb-2 text-warning"></i>
                    <h4 class="mb-0" id="totalValor">$0</h4>
                    <small>Valor Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-left-info">
                <div class="card-body text-center">
                    <i class="fas fa-warehouse fa-2x mb-2 text-info"></i>
                    <h4 class="mb-0" id="bodegasConStock">0</h4>
                    <small>Bodegas con Stock</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Existencias -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>Existencias Actuales
            </h5>
            <div class="card-tools">
                <button class="btn btn-outline-secondary btn-sm" onclick="recargarTabla()">
                    <i class="fas fa-sync"></i> Actualizar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="existenciasTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Unidad</th>
                            <th>Bodega</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Costo Promedio</th>
                            <th class="text-end">Valor Total</th>
                            <th>Última Actualización</th>
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
@endsection

@section('scripts')
<script>
    let existenciasTable;
    let currentFilters = {};

    $(document).ready(function() {
        // Initialize DataTable
        initializeTable();
        
        // Load form options
        loadFormOptions();
        
        // Load initial data
        loadExistencias();

        // URL parameters for initial filtering
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('stock_minimo')) {
            $('#stock_minimo').prop('checked', true);
            aplicarFiltros();
        }
    });

    function initializeTable() {
        existenciasTable = $('#existenciasTable').DataTable({
            data: [],
            columns: [
                { data: 'producto_codigo' },
                { data: 'producto_nombre' },
                { data: 'categoria_nombre' },
                { data: 'unidad_display' },
                { 
                    data: null,
                    render: function(data, type, row) {
                        return `${row.bodega_codigo} - ${row.bodega_nombre}`;
                    }
                },
                { 
                    data: 'cantidad',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return `<span class="fw-bold">${data}</span>`;
                    }
                },
                { 
                    data: 'costo_promedio',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return `$${data}`;
                    }
                },
                { 
                    data: 'valor_total',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return `<span class="fw-bold text-success">$${data}</span>`;
                    }
                },
                { data: 'updated_at' }
            ],
            order: [[1, 'asc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            responsive: true,
            pageLength: 50,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'pageLength',
                    text: 'Mostrar'
                }
            ]
        });
    }

    function loadFormOptions() {
        $.get('{{ route("reportes.options") }}')
            .done(function(response) {
                if (response.success) {
                    // Load products
                    const productoSelect = $('#producto_id');
                    response.data.productos.forEach(function(producto) {
                        productoSelect.append(`<option value="${producto.id}">${producto.codigo} - ${producto.nombre}</option>`);
                    });

                    // Load warehouses
                    const bodegaSelect = $('#bodega_id');
                    response.data.bodegas.forEach(function(bodega) {
                        bodegaSelect.append(`<option value="${bodega.id}">${bodega.codigo} - ${bodega.nombre}</option>`);
                    });

                    // Load categories
                    const categoriaSelect = $('#categoria_id');
                    response.data.categorias.forEach(function(categoria) {
                        categoriaSelect.append(`<option value="${categoria.id}">${categoria.nombre}</option>`);
                    });
                }
            })
            .fail(handleAjaxError);
    }

    function loadExistencias(filters = {}) {
        showLoading();
        
        $.get('{{ route("reportes.existencias.data") }}', filters)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    // Update table
                    existenciasTable.clear().rows.add(response.data).draw();
                    
                    // Update summary
                    updateSummary(response.resumen);
                }
            })
            .fail(function(xhr) {
                hideLoading();
                handleAjaxError(xhr);
            });
    }

    function updateSummary(resumen) {
        $('#totalRegistros').text(resumen.total_registros.toLocaleString());
        $('#totalCantidad').text(resumen.total_cantidad.toLocaleString('es-ES', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        $('#totalValor').text('$' + resumen.total_valor.toLocaleString('es-ES', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        $('#bodegasConStock').text(resumen.bodegas_con_stock.toLocaleString());
    }

    function aplicarFiltros() {
        const filters = {
            producto_id: $('#producto_id').val(),
            bodega_id: $('#bodega_id').val(),
            categoria_id: $('#categoria_id').val(),
            stock_minimo: $('#stock_minimo').is(':checked') ? 1 : null
        };

        // Remove empty filters
        Object.keys(filters).forEach(key => {
            if (filters[key] === '' || filters[key] === null) {
                delete filters[key];
            }
        });

        currentFilters = filters;
        loadExistencias(filters);
    }

    function limpiarFiltros() {
        $('#filtrosForm')[0].reset();
        currentFilters = {};
        loadExistencias();
    }

    function exportarCSV() {
        showLoading();
        
        const params = new URLSearchParams(currentFilters);
        window.location.href = '{{ route("reportes.existencias.export") }}?' + params.toString();
        
        setTimeout(hideLoading, 2000);
        showToast('Descarga iniciada', 'success');
    }

    function recargarTabla() {
        loadExistencias(currentFilters);
        showToast('Tabla actualizada', 'info');
    }
</script>

<style>
    .stats-card {
        transition: transform 0.2s;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
    }
    
    .border-left-primary {
        border-left: 4px solid #007bff;
    }
    
    .border-left-success {
        border-left: 4px solid #28a745;
    }
    
    .border-left-warning {
        border-left: 4px solid #ffc107;
    }
    
    .border-left-info {
        border-left: 4px solid #17a2b8;
    }

    .table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-weight: 600;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing,
    .dataTables_wrapper .dataTables_paginate {
        color: #495057;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white !important;
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/app-utils.js') }}"></script>
@endpush
@endsection
