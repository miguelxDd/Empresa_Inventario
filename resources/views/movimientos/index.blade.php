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
        // TODO: Implementar modal de detalle
        showToast('Funcionalidad en desarrollo', 'info');
    }

    function imprimirMovimiento(id) {
        // TODO: Implementar impresión
        showToast('Funcionalidad en desarrollo', 'info');
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
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/app-utils.js') }}"></script>
@endpush
@endsection
