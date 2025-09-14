@extends('layouts.app')

@section('title', 'Asientos Contables')

@push('styles')
<link href="{{ asset('css/reportes.css') }}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('header')
<div class="page-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="mb-0">
                    <i class="fas fa-calculator me-3"></i>Asientos Contables
                </h1>
                <p class="mb-0 mt-2 opacity-75">Registro de asientos contables generados por movimientos de inventario</p>
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
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="origen_tabla" class="form-label">Tipo de Origen</label>
                            <select class="form-select" id="origen_tabla" name="origen_tabla">
                                <option value="">Todos los orígenes</option>
                                <option value="movimientos_inventario">Movimientos de Inventario</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="numero" class="form-label">Número de Asiento</label>
                            <input type="text" class="form-control" id="numero" name="numero" placeholder="Buscar por número...">
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
                        <button type="button" class="btn btn-info" onclick="setFiltroHoy()">
                            <i class="fas fa-calendar-day me-1"></i>Hoy
                        </button>
                        <button type="button" class="btn btn-info" onclick="setFiltroMes()">
                            <i class="fas fa-calendar-alt me-1"></i>Este Mes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stats-card border-left-primary">
                <div class="card-body text-center">
                    <i class="fas fa-file-invoice fa-2x mb-2 text-primary"></i>
                    <h4 class="mb-0" id="totalAsientos">0</h4>
                    <small>Total Asientos</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card border-left-success">
                <div class="card-body text-center">
                    <i class="fas fa-plus-circle fa-2x mb-2 text-success"></i>
                    <h4 class="mb-0" id="totalDebitos">$0</h4>
                    <small>Total Débitos</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card border-left-danger">
                <div class="card-body text-center">
                    <i class="fas fa-minus-circle fa-2x mb-2 text-danger"></i>
                    <h4 class="mb-0" id="totalCreditos">$0</h4>
                    <small>Total Créditos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Asientos -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>Listado de Asientos Contables
            </h5>
            <div class="card-tools">
                <button class="btn btn-outline-secondary btn-sm" onclick="recargarTabla()">
                    <i class="fas fa-sync"></i> Actualizar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="asientosTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th class="text-end">Débito</th>
                            <th class="text-end">Crédito</th>
                            <th>Origen</th>
                            <th>Tipo Movimiento</th>
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

<!-- Modal para Ver Detalle del Asiento -->
<div class="modal fade" id="detalleAsientoModal" tabindex="-1" aria-labelledby="detalleAsientoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detalleAsientoModalLabel">
                    <i class="fas fa-file-invoice me-2"></i>Detalle del Asiento Contable
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="detalleAsientoContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary" onclick="imprimirAsiento()">
                    <i class="fas fa-print me-1"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ver Movimiento Origen -->
<div class="modal fade" id="movimientoOrigenModal" tabindex="-1" aria-labelledby="movimientoOrigenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="movimientoOrigenModalLabel">
                    <i class="fas fa-exchange-alt me-2"></i>Movimiento de Inventario Origen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="movimientoOrigenContent">
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
    let asientosTable;
    let currentFilters = {};

    $(document).ready(function() {
        // Set default dates (current month)
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        
        $('#fecha_inicio').val(firstDay.toISOString().split('T')[0]);
        $('#fecha_fin').val(today.toISOString().split('T')[0]);

        // Initialize DataTable
        initializeTable();
        
        // Load initial data
        aplicarFiltros();
    });

    function initializeTable() {
        asientosTable = $('#asientosTable').DataTable({
            data: [],
            columns: [
                { 
                    data: 'numero',
                    render: function(data, type, row) {
                        return `<span class="badge bg-primary fs-6">${data}</span>`;
                    }
                },
                { data: 'fecha' },
                { 
                    data: 'concepto',
                    render: function(data, type, row) {
                        if (data && data.length > 50) {
                            return `<span title="${data}">${data.substring(0, 50)}...</span>`;
                        }
                        return data || '';
                    }
                },
                { 
                    data: 'total_debito',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return `<span class="text-success fw-bold">$${data}</span>`;
                    }
                },
                { 
                    data: 'total_credito',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return `<span class="text-danger fw-bold">$${data}</span>`;
                    }
                },
                { 
                    data: 'origen_tabla',
                    render: function(data, type, row) {
                        if (data === 'movimientos_inventario') {
                            return '<span class="badge bg-info">Movimiento Inventario</span>';
                        }
                        return data || 'N/A';
                    }
                },
                { 
                    data: 'tipo_movimiento',
                    render: function(data, type, row) {
                        if (!data) return '-';
                        
                        let badgeClass = '';
                        switch(data.toLowerCase()) {
                            case 'entrada': badgeClass = 'bg-success'; break;
                            case 'salida': badgeClass = 'bg-danger'; break;
                            case 'ajuste': badgeClass = 'bg-warning'; break;
                            case 'transferencia': badgeClass = 'bg-info'; break;
                            default: badgeClass = 'bg-secondary';
                        }
                        
                        return `<span class="badge ${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data, type, row) {
                        let buttons = `
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="verDetalleAsiento(${data})" title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </button>
                        `;
                        
                        if (row.tiene_movimiento) {
                            buttons += `
                                <button class="btn btn-sm btn-outline-info" onclick="verMovimientoOrigen(${row.origen_id})" title="Ver Movimiento Origen">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>
                            `;
                        }
                        
                        buttons += `
                                <button class="btn btn-sm btn-outline-secondary" onclick="imprimirAsientoDirecto(${data})" title="Imprimir">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        `;
                        
                        return buttons;
                    }
                }
            ],
            order: [[0, 'desc']],
            ...window.DataTablesSpanish,
            responsive: true,
            pageLength: 25
        });
    }

    function loadAsientos(filters = {}) {
        showLoading();
        
        $.get('{{ route("reportes.asientos.data") }}', filters)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    // Update table
                    asientosTable.clear().rows.add(response.data).draw();
                    
                    // Update summary
                    updateSummary(response.data);
                }
            })
            .fail(function(xhr) {
                hideLoading();
                handleAjaxError(xhr);
            });
    }

    function updateSummary(data) {
        let totalDebitos = 0;
        let totalCreditos = 0;
        
        data.forEach(function(asiento) {
            totalDebitos += parseFloat(asiento.total_debito.replace(/[,$]/g, ''));
            totalCreditos += parseFloat(asiento.total_credito.replace(/[,$]/g, ''));
        });
        
        $('#totalAsientos').text(data.length.toLocaleString());
        $('#totalDebitos').text('$' + totalDebitos.toLocaleString('es-ES', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        $('#totalCreditos').text('$' + totalCreditos.toLocaleString('es-ES', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
    }

    function aplicarFiltros() {
        const filters = {
            fecha_inicio: $('#fecha_inicio').val(),
            fecha_fin: $('#fecha_fin').val(),
            origen_tabla: $('#origen_tabla').val(),
            numero: $('#numero').val()
        };

        // Remove empty filters
        Object.keys(filters).forEach(key => {
            if (filters[key] === '') {
                delete filters[key];
            }
        });

        currentFilters = filters;
        loadAsientos(filters);
    }

    function limpiarFiltros() {
        $('#filtrosForm')[0].reset();
        currentFilters = {};
        loadAsientos();
    }

    function setFiltroHoy() {
        const today = new Date().toISOString().split('T')[0];
        $('#fecha_inicio').val(today);
        $('#fecha_fin').val(today);
        aplicarFiltros();
    }

    function setFiltroMes() {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        
        $('#fecha_inicio').val(firstDay.toISOString().split('T')[0]);
        $('#fecha_fin').val(today.toISOString().split('T')[0]);
        aplicarFiltros();
    }

    function recargarTabla() {
        loadAsientos(currentFilters);
        showToast('Tabla actualizada', 'info');
    }

    function verDetalleAsiento(asientoId) {
        showLoading();
        
        // TODO: Implementar endpoint para obtener detalle del asiento
        setTimeout(function() {
            hideLoading();
            $('#detalleAsientoContent').html(`
                <div class="alert alert-info">
                    <h6>Asiento #${asientoId}</h6>
                    <p>Detalle del asiento contable en desarrollo...</p>
                    <p>Aquí se mostrarían todas las partidas (débitos y créditos) del asiento.</p>
                </div>
            `);
            $('#detalleAsientoModal').modal('show');
        }, 500);
    }

    function verMovimientoOrigen(movimientoId) {
        showLoading();
        
        // TODO: Implementar endpoint para obtener detalle del movimiento
        setTimeout(function() {
            hideLoading();
            $('#movimientoOrigenContent').html(`
                <div class="alert alert-info">
                    <h6>Movimiento de Inventario #${movimientoId}</h6>
                    <p>Detalle del movimiento de inventario que generó este asiento...</p>
                    <p>Aquí se mostrarían las líneas de productos, bodegas, cantidades, etc.</p>
                </div>
            `);
            $('#movimientoOrigenModal').modal('show');
        }, 500);
    }

    function imprimirAsiento() {
        showToast('Funcionalidad de impresión en desarrollo', 'info');
    }

    function imprimirAsientoDirecto(asientoId) {
        showToast('Funcionalidad de impresión en desarrollo', 'info');
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
    
    .border-left-danger {
        border-left: 4px solid #dc3545;
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

    .badge {
        font-size: 0.8em;
    }

    .btn-group .btn {
        transition: all 0.2s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-1px);
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/app-utils.js') }}"></script>
@endpush
@endsection
