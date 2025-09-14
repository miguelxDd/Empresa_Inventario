@extends('layouts.app')

@section('title', 'Movimientos de Inventario')

@push('styles')
<link href="{{ asset('css/movimientos.css') }}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('header')
<div class="page-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="mb-0">
                    <i class="fas fa-exchange-alt me-3"></i>Movimientos de Inventario
                </h1>
                <p class="mb-0 mt-2 opacity-75">Gestiona entradas, salidas, ajustes y transferencias de inventario</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('movimientos.create') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-plus me-2"></i>Nuevo Movimiento
                </a>
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
            <div class="card stats-card border-left-primary">
                <div class="card-body text-center">
                    <i class="fas fa-arrow-down fa-2x mb-2 text-success"></i>
                    <h4 class="mb-0" id="totalEntradas">0</h4>
                    <small>Entradas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-left-danger">
                <div class="card-body text-center">
                    <i class="fas fa-arrow-up fa-2x mb-2 text-danger"></i>
                    <h4 class="mb-0" id="totalSalidas">0</h4>
                    <small>Salidas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-left-warning">
                <div class="card-body text-center">
                    <i class="fas fa-tools fa-2x mb-2 text-warning"></i>
                    <h4 class="mb-0" id="totalAjustes">0</h4>
                    <small>Ajustes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-left-info">
                <div class="card-body text-center">
                    <i class="fas fa-exchange-alt fa-2x mb-2 text-info"></i>
                    <h4 class="mb-0" id="totalTransferencias">0</h4>
                    <small>Transferencias</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Movements Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Historial de Movimientos
            </h5>
            <div class="card-tools">
                <button class="btn btn-outline-secondary btn-sm" onclick="recargarTabla()">
                    <i class="fas fa-sync"></i> Actualizar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="movimientosTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Referencia</th>
                            <th>Bodega Origen</th>
                            <th>Bodega Destino</th>
                            <th>Asiento Contable</th>
                            <th>Observaciones</th>
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
@endsection

@section('scripts')
<script>
    let movimientosTable;

    $(document).ready(function() {
        // Initialize DataTable
        initializeTable();
        
        // Load statistics
        loadStatistics();
    });

    function initializeTable() {
        movimientosTable = $('#movimientosTable').DataTable({
            ajax: {
                url: '{{ route("movimientos.list") }}',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            },
            columns: [
                { data: 'id' },
                { 
                    data: 'tipo_movimiento',
                    render: function(data, type, row) {
                        let badgeClass = '';
                        let icon = '';
                        
                        switch(data.toLowerCase()) {
                            case 'entrada':
                                badgeClass = 'bg-success';
                                icon = 'fas fa-arrow-down';
                                break;
                            case 'salida':
                                badgeClass = 'bg-danger';
                                icon = 'fas fa-arrow-up';
                                break;
                            case 'ajuste':
                                badgeClass = 'bg-warning';
                                icon = 'fas fa-tools';
                                break;
                            case 'transferencia':
                                badgeClass = 'bg-info';
                                icon = 'fas fa-exchange-alt';
                                break;
                        }
                        
                        return `<span class="badge ${badgeClass}"><i class="${icon} me-1"></i>${data}</span>`;
                    }
                },
                { 
                    data: 'estado',
                    render: function(data, type, row) {
                        let badgeClass = '';
                        
                        switch(data.toLowerCase()) {
                            case 'borrador':
                                badgeClass = 'bg-secondary';
                                break;
                            case 'confirmado':
                                badgeClass = 'bg-success';
                                break;
                            case 'cancelado':
                                badgeClass = 'bg-danger';
                                break;
                        }
                        
                        return `<span class="badge ${badgeClass}">${data}</span>`;
                    }
                },
                { data: 'fecha' },
                { data: 'referencia' },
                { data: 'bodega_origen' },
                { data: 'bodega_destino' },
                { 
                    data: 'asiento_numero',
                    render: function(data, type, row) {
                        if (data && data !== 'N/A') {
                            return `<span class="badge bg-secondary"># ${data}</span>`;
                        }
                        return data;
                    }
                },
                { 
                    data: 'observaciones',
                    render: function(data, type, row) {
                        if (data && data.length > 50) {
                            return data.substring(0, 50) + '...';
                        }
                        return data || '';
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info" onclick="verDetalle(${data})" title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary" onclick="imprimirMovimiento(${data})" title="Imprimir">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[0, 'desc']],
            ...window.DataTablesSpanish,
            responsive: true,
            pageLength: 25
        });
    }

    function loadStatistics() {
        // Simular estadísticas - en producción esto vendría de una ruta específica
        $.get('{{ route("movimientos.list") }}')
            .done(function(response) {
                if (response.success && response.data) {
                    let entradas = 0, salidas = 0, ajustes = 0, transferencias = 0;
                    
                    response.data.forEach(function(movimiento) {
                        switch(movimiento.tipo_movimiento.toLowerCase()) {
                            case 'entrada':
                                entradas++;
                                break;
                            case 'salida':
                                salidas++;
                                break;
                            case 'ajuste':
                                ajustes++;
                                break;
                            case 'transferencia':
                                transferencias++;
                                break;
                        }
                    });
                    
                    $('#totalEntradas').text(entradas);
                    $('#totalSalidas').text(salidas);
                    $('#totalAjustes').text(ajustes);
                    $('#totalTransferencias').text(transferencias);
                }
            })
            .fail(function() {
                // Fail silently for statistics
            });
    }

    function recargarTabla() {
        movimientosTable.ajax.reload();
        loadStatistics();
        showToast('Tabla actualizada', 'info');
    }

    function verDetalle(id) {
        // Realizar petición AJAX para obtener los datos
        fetch(`/movimientos/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarModalDetalle(data.data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al cargar el detalle del movimiento'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión al cargar el detalle'
                });
            });
    }

    // Función para mostrar el modal con el detalle
    function mostrarModalDetalle(data) {
        const movimiento = data.movimiento;
        const detalles = data.detalles;
        const resumen = data.resumen;

        // Construir el contenido del modal
        let htmlContent = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Número:</strong> ${movimiento.numero}<br>
                    <strong>Tipo:</strong> <span class="badge bg-primary">${movimiento.tipo.toUpperCase()}</span><br>
                    <strong>Estado:</strong> <span class="badge bg-${movimiento.estado === 'confirmado' ? 'success' : 'warning'}">${movimiento.estado.toUpperCase()}</span>
                </div>
                <div class="col-md-6">
                    <strong>Fecha:</strong> ${new Date(movimiento.fecha).toLocaleString()}<br>
                    <strong>Total:</strong> $${parseFloat(movimiento.total || 0).toLocaleString('es-ES', {minimumFractionDigits: 2})}
                </div>
            </div>`;

        // Bodegas
        if (movimiento.bodega_origen || movimiento.bodega_destino) {
            htmlContent += `<div class="row mb-3">`;
            if (movimiento.bodega_origen) {
                htmlContent += `
                    <div class="col-md-6">
                        <h6 class="text-muted">📦 Bodega Origen</h6>
                        <strong>${movimiento.bodega_origen.nombre}</strong><br>
                        <small class="text-muted">Código: ${movimiento.bodega_origen.codigo}</small>
                    </div>`;
            }
            if (movimiento.bodega_destino) {
                htmlContent += `
                    <div class="col-md-6">
                        <h6 class="text-muted">📦 Bodega Destino</h6>
                        <strong>${movimiento.bodega_destino.nombre}</strong><br>
                        <small class="text-muted">Código: ${movimiento.bodega_destino.codigo}</small>
                    </div>`;
            }
            htmlContent += `</div>`;
        }

        // Observaciones
        if (movimiento.observaciones) {
            htmlContent += `
                <div class="mb-3">
                    <strong>Observaciones:</strong><br>
                    <div class="p-2 bg-light rounded">${movimiento.observaciones}</div>
                </div>`;
        }

        // Tabla de productos
        htmlContent += `
            <h6 class="mt-4 mb-3">Productos en el Movimiento</h6>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Costo Unit.</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>`;

        detalles.forEach(detalle => {
            htmlContent += `
                <tr>
                    <td><code>${detalle.producto_codigo}</code></td>
                    <td>${detalle.producto_nombre}</td>
                    <td class="text-center">${parseFloat(detalle.cantidad).toLocaleString('es-ES', {minimumFractionDigits: 2})}</td>
                    <td class="text-end">$${parseFloat(detalle.costo_unitario).toLocaleString('es-ES', {minimumFractionDigits: 2})}</td>
                    <td class="text-end">$${parseFloat(detalle.total).toLocaleString('es-ES', {minimumFractionDigits: 2})}</td>
                </tr>`;
        });

        htmlContent += `
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2">TOTALES</th>
                            <th class="text-center">${parseFloat(resumen.total_cantidad).toLocaleString('es-ES', {minimumFractionDigits: 2})}</th>
                            <th></th>
                            <th class="text-end">$${parseFloat(resumen.total_valor).toLocaleString('es-ES', {minimumFractionDigits: 2})}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>`;

        // Mostrar el modal
        Swal.fire({
            title: `<i class="fas fa-eye"></i> Detalle Movimiento #${movimiento.numero}`,
            html: htmlContent,
            width: '80%',
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                container: 'swal-wide'
            }
        });
    }

    function imprimirMovimiento(id) {
        // Abrir nueva ventana para imprimir
        const printWindow = window.open(`/movimientos/${id}/print`, '_blank', 'width=800,height=600');
        
        // Opcional: auto-cerrar la ventana después de imprimir
        printWindow.onload = function() {
            // Dar tiempo para que cargue completamente
            setTimeout(() => {
                printWindow.focus();
            }, 500);
        };
    }
</script>

<style>
    .border-left-primary {
        border-left: 4px solid #007bff;
    }
    .border-left-danger {
        border-left: 4px solid #dc3545;
    }
    .border-left-warning {
        border-left: 4px solid #ffc107;
    }
    .border-left-info {
        border-left: 4px solid #17a2b8;
    }
    
    /* Estilos para modal ancho de SweetAlert2 */
    .swal-wide {
        width: 90% !important;
        max-width: 1200px !important;
    }
    
    .swal-wide .swal2-popup {
        font-size: 14px;
    }
    
    .swal-wide .table {
        font-size: 13px;
    }
    
    .swal-wide .table th,
    .swal-wide .table td {
        padding: 8px;
        vertical-align: middle;
    }
    
    .swal-wide .badge {
        font-size: 11px;
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/app-utils.js') }}"></script>
@endpush
@endsection
