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
                    data: 'descripcion',
                    render: function(data, type, row) {
                        if (data && data.length > 50) {
                            return `<span title="${data}">${data.substring(0, 50)}...</span>`;
                        }
                        return data || '';
                    }
                },
                { 
                    data: 'total_debe',
                    className: 'text-end',
                    render: function(data, type, row) {
                        const value = parseFloat(data || 0).toLocaleString('es-ES', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        return `<span class="text-success fw-bold">$${value}</span>`;
                    }
                },
                { 
                    data: 'total_haber',
                    className: 'text-end',
                    render: function(data, type, row) {
                        const value = parseFloat(data || 0).toLocaleString('es-ES', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        return `<span class="text-danger fw-bold">$${value}</span>`;
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
                    data: 'descripcion',
                    render: function(data, type, row) {
                        if (!data) return '-';
                        
                        // Extraer el tipo de movimiento de la descripción
                        let tipoMovimiento = '';
                        let badgeClass = 'bg-secondary';
                        
                        if (data.includes('entrada')) {
                            tipoMovimiento = 'Entrada';
                            badgeClass = 'bg-success';
                        } else if (data.includes('salida')) {
                            tipoMovimiento = 'Salida';
                            badgeClass = 'bg-danger';
                        } else if (data.includes('ajuste')) {
                            tipoMovimiento = 'Ajuste';
                            badgeClass = 'bg-warning';
                        } else if (data.includes('transferencia')) {
                            tipoMovimiento = 'Transferencia';
                            badgeClass = 'bg-info';
                        } else {
                            tipoMovimiento = 'Otro';
                        }
                        
                        return `<span class="badge ${badgeClass}">${tipoMovimiento}</span>`;
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
            // Los campos del API son total_debe y total_haber, no total_debito y total_credito
            if (asiento.total_debe) {
                totalDebitos += parseFloat(asiento.total_debe.toString().replace(/[,$]/g, ''));
            }
            if (asiento.total_haber) {
                totalCreditos += parseFloat(asiento.total_haber.toString().replace(/[,$]/g, ''));
            }
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
        
        $.get(`{{ url('/reportes/asientos') }}/${asientoId}/detalle`)
            .done(function(response) {
                if (response.success) {
                    const asiento = response.data.asiento;
                    const cuentas = response.data.cuentas;
                    const productos = response.data.productos;
                    
                    let htmlContent = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Número:</strong> ${asiento.numero}<br>
                                <strong>Fecha:</strong> ${asiento.fecha}<br>
                                <strong>Estado:</strong> <span class="badge bg-success">${asiento.estado}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Total Debe:</strong> $${parseFloat(asiento.total_debe).toLocaleString('es-ES', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })}<br>
                                <strong>Total Haber:</strong> $${parseFloat(asiento.total_haber).toLocaleString('es-ES', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })}
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Descripción:</strong><br>
                            <p class="text-muted">${asiento.descripcion}</p>
                        </div>
                    `;
                    
                    // Mostrar las cuentas contables del asiento
                    if (cuentas && cuentas.length > 0) {
                        htmlContent += `
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-calculator"></i> Cuentas Contables</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Cuenta</th>
                                                    <th>Concepto</th>
                                                    <th class="text-end">Debe</th>
                                                    <th class="text-end">Haber</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                        `;
                        
                        cuentas.forEach(function(cuenta) {
                            const debe = cuenta.debe && parseFloat(cuenta.debe) > 0 ? 
                                parseFloat(cuenta.debe).toLocaleString('es-ES', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) : '-';
                            
                            const haber = cuenta.haber && parseFloat(cuenta.haber) > 0 ? 
                                parseFloat(cuenta.haber).toLocaleString('es-ES', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) : '-';
                            
                            // Definir color del badge según el tipo de cuenta
                            let tipoBadgeClass = 'bg-secondary';
                            switch(cuenta.cuenta_tipo) {
                                case 'activo': tipoBadgeClass = 'bg-success'; break;
                                case 'pasivo': tipoBadgeClass = 'bg-danger'; break;
                                case 'patrimonio': tipoBadgeClass = 'bg-primary'; break;
                                case 'ingreso': tipoBadgeClass = 'bg-info'; break;
                                case 'gasto': tipoBadgeClass = 'bg-warning'; break;
                            }
                            
                            htmlContent += `
                                <tr>
                                    <td>
                                        <strong>${cuenta.cuenta_codigo}</strong><br>
                                        <small class="text-muted">${cuenta.cuenta_nombre}</small><br>
                                        <span class="badge ${tipoBadgeClass} badge-sm">${cuenta.cuenta_tipo.toUpperCase()}</span>
                                    </td>
                                    <td>${cuenta.concepto || '-'}</td>
                                    <td class="text-end ${cuenta.debe && parseFloat(cuenta.debe) > 0 ? 'text-success fw-bold' : ''}">${debe !== '-' ? '$' + debe : '-'}</td>
                                    <td class="text-end ${cuenta.haber && parseFloat(cuenta.haber) > 0 ? 'text-danger fw-bold' : ''}">${haber !== '-' ? '$' + haber : '-'}</td>
                                </tr>
                            `;
                        });
                        
                        htmlContent += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Mostrar información del movimiento si existe
                    if (asiento.movimiento_id) {
                        htmlContent += `
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-exchange-alt"></i> Información del Movimiento de Inventario</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Tipo:</strong> ${asiento.tipo_movimiento || 'N/A'}<br>
                                            <strong>Movimiento ID:</strong> ${asiento.movimiento_id}
                                        </div>
                                        <div class="col-md-6">
                        `;
                        
                        if (asiento.bodega_origen_nombre) {
                            htmlContent += `<strong>Bodega Origen:</strong> ${asiento.bodega_origen_nombre}<br>`;
                        }
                        if (asiento.bodega_destino_nombre) {
                            htmlContent += `<strong>Bodega Destino:</strong> ${asiento.bodega_destino_nombre}<br>`;
                        }
                        
                        htmlContent += `
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Mostrar detalles de productos si existen
                    if (productos && productos.length > 0) {
                        htmlContent += `
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-boxes"></i> Productos del Movimiento</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th class="text-end">Cantidad</th>
                                                    <th class="text-end">Costo Unitario</th>
                                                    <th class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                        `;
                        
                        productos.forEach(function(producto) {
                            const cantidad = parseFloat(producto.cantidad).toLocaleString('es-ES', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            
                            const costoUnitario = parseFloat(producto.costo_unitario).toLocaleString('es-ES', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            
                            const total = parseFloat(producto.total).toLocaleString('es-ES', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            
                            htmlContent += `
                                <tr>
                                    <td>
                                        <strong>${producto.sku}</strong><br>
                                        <small class="text-muted">${producto.producto_nombre}</small>
                                    </td>
                                    <td class="text-end">${cantidad}</td>
                                    <td class="text-end">$${costoUnitario}</td>
                                    <td class="text-end fw-bold">$${total}</td>
                                </tr>
                            `;
                        });
                        
                        htmlContent += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Si no hay cuentas ni productos
                    if ((!cuentas || cuentas.length === 0) && (!productos || productos.length === 0)) {
                        htmlContent += `
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Este asiento no tiene detalles de cuentas contables o productos asociados.
                            </div>
                        `;
                    }
                    
                    htmlContent += `
                        <div class="mt-3 text-center">
                            <button class="btn btn-primary" onclick="imprimirAsiento(${asientoId})">
                                <i class="fas fa-print"></i> Imprimir Asiento
                            </button>
                        </div>
                    `;
                    
                    $('#detalleAsientoContent').html(htmlContent);
                    $('#detalleAsientoModal').modal('show');
                } else {
                    showNotification('Error al cargar detalle del asiento', 'error');
                }
            })
            .fail(function() {
                showNotification('Error de conexión al obtener detalle del asiento', 'error');
            })
            .always(function() {
                hideLoading();
            });
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

    function imprimirAsiento(asientoId) {
        // Obtener el contenido del modal para imprimir
        const contenido = document.getElementById('detalleAsientoContent').innerHTML;
        
        // Crear ventana de impresión
        const ventanaImpresion = window.open('', 'PRINT', 'height=600,width=800');
        
        ventanaImpresion.document.write(`
            <html>
                <head>
                    <title>Asiento Contable - Detalle</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @media print {
                            .btn { display: none !important; }
                            body { font-size: 12px; }
                            .table { border-collapse: collapse; }
                            .table th, .table td { border: 1px solid #000 !important; }
                        }
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .company-name { font-size: 18px; font-weight: bold; }
                        .report-title { font-size: 16px; margin-top: 10px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="company-name">Sistema de Inventario</div>
                        <div class="report-title">Detalle del Asiento Contable</div>
                        <div class="small text-muted">Generado el: ${new Date().toLocaleDateString('es-ES')} ${new Date().toLocaleTimeString('es-ES')}</div>
                    </div>
                    <div class="content">
                        ${contenido}
                    </div>
                </body>
            </html>
        `);
        
        ventanaImpresion.document.close();
        ventanaImpresion.focus();
        
        // Imprimir automáticamente
        setTimeout(function() {
            ventanaImpresion.print();
            ventanaImpresion.close();
        }, 250);
    }

    function imprimirAsientoDirecto(asientoId) {
        // Cargar los datos del asiento y luego imprimir
        $.get(`{{ url('/reportes/asientos') }}/${asientoId}/detalle`)
            .done(function(response) {
                if (response.success) {
                    // Simular que el modal se llena con los datos
                    const asiento = response.data.asiento;
                    const cuentas = response.data.cuentas;
                    const productos = response.data.productos;
                    
                    let htmlContent = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Número:</strong> ${asiento.numero}<br>
                                <strong>Fecha:</strong> ${asiento.fecha}<br>
                                <strong>Estado:</strong> <span class="badge bg-success">${asiento.estado}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Total Debe:</strong> $${parseFloat(asiento.total_debe).toLocaleString('es-ES', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })}<br>
                                <strong>Total Haber:</strong> $${parseFloat(asiento.total_haber).toLocaleString('es-ES', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })}
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Descripción:</strong><br>
                            <p class="text-muted">${asiento.descripcion}</p>
                        </div>
                    `;
                    
                    // Mostrar información del movimiento si existe
                    if (asiento.movimiento_id) {
                        htmlContent += `
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Información del Movimiento de Inventario</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Tipo:</strong> ${asiento.tipo_movimiento || 'N/A'}<br>
                                            <strong>Movimiento ID:</strong> ${asiento.movimiento_id}
                                        </div>
                                        <div class="col-md-6">
                        `;
                        
                        if (asiento.bodega_origen_nombre) {
                            htmlContent += `<strong>Bodega Origen:</strong> ${asiento.bodega_origen_nombre}<br>`;
                        }
                        if (asiento.bodega_destino_nombre) {
                            htmlContent += `<strong>Bodega Destino:</strong> ${asiento.bodega_destino_nombre}<br>`;
                        }
                        
                        htmlContent += `
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Mostrar detalles de productos si existen
                    if (detalles && detalles.length > 0) {
                        htmlContent += `
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-end">Cantidad</th>
                                            <th class="text-end">Costo Unitario</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        detalles.forEach(function(detalle) {
                            const cantidad = parseFloat(detalle.cantidad).toLocaleString('es-ES', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            
                            const costoUnitario = parseFloat(detalle.costo_unitario).toLocaleString('es-ES', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            
                            const total = parseFloat(detalle.total).toLocaleString('es-ES', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            
                            htmlContent += `
                                <tr>
                                    <td>
                                        <strong>${detalle.sku}</strong><br>
                                        <small class="text-muted">${detalle.producto_nombre}</small>
                                    </td>
                                    <td class="text-end">${cantidad}</td>
                                    <td class="text-end">$${costoUnitario}</td>
                                    <td class="text-end fw-bold">$${total}</td>
                                </tr>
                            `;
                        });
                        
                        htmlContent += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }
                    
                    // Crear ventana de impresión directamente
                    const ventanaImpresion = window.open('', 'PRINT', 'height=600,width=800');
                    
                    ventanaImpresion.document.write(`
                        <html>
                            <head>
                                <title>Asiento Contable - ${asiento.numero}</title>
                                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                                <style>
                                    @media print {
                                        body { font-size: 12px; }
                                        .table { border-collapse: collapse; }
                                        .table th, .table td { border: 1px solid #000 !important; }
                                    }
                                    body { font-family: Arial, sans-serif; margin: 20px; }
                                    .header { text-align: center; margin-bottom: 30px; }
                                    .company-name { font-size: 18px; font-weight: bold; }
                                    .report-title { font-size: 16px; margin-top: 10px; }
                                </style>
                            </head>
                            <body>
                                <div class="header">
                                    <div class="company-name">Sistema de Inventario</div>
                                    <div class="report-title">Detalle del Asiento Contable</div>
                                    <div class="small text-muted">Generado el: ${new Date().toLocaleDateString('es-ES')} ${new Date().toLocaleTimeString('es-ES')}</div>
                                </div>
                                <div class="content">
                                    ${htmlContent}
                                </div>
                            </body>
                        </html>
                    `);
                    
                    ventanaImpresion.document.close();
                    ventanaImpresion.focus();
                    
                    setTimeout(function() {
                        ventanaImpresion.print();
                        ventanaImpresion.close();
                    }, 250);
                } else {
                    showNotification('Error al cargar datos para impresión', 'error');
                }
            })
            .fail(function() {
                showNotification('Error de conexión al cargar datos para impresión', 'error');
            });
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
