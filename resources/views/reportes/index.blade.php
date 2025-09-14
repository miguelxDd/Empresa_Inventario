@extends('layouts.app')

@section('title', 'Reportes de Inventario')

@push('styles')
<link href="{{ asset('css/reportes.css') }}" rel="stylesheet">
@endpush

@section('header')
<div class="page-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="mb-0">
                    <i class="fas fa-chart-bar me-3"></i>Reportes de Inventario
                </h1>
                <p class="mb-0 mt-2 opacity-75">Panel de reportes y análisis del sistema de inventario</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card border-left-primary">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-2x mb-2 text-primary"></i>
                    <h4 class="mb-0" id="totalProductos">-</h4>
                    <small>Productos Activos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-left-success">
                <div class="card-body text-center">
                    <i class="fas fa-warehouse fa-2x mb-2 text-success"></i>
                    <h4 class="mb-0" id="totalBodegas">-</h4>
                    <small>Bodegas Activas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-left-warning">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x mb-2 text-warning"></i>
                    <h4 class="mb-0" id="valorInventario">-</h4>
                    <small>Valor Total Inventario</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-left-info">
                <div class="card-body text-center">
                    <i class="fas fa-exchange-alt fa-2x mb-2 text-info"></i>
                    <h4 class="mb-0" id="movimientosHoy">-</h4>
                    <small>Movimientos Hoy</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Cards -->
    <div class="row">
        <!-- Kardex Report -->
        <div class="col-lg-4 mb-4">
            <div class="card report-card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-alt me-2"></i>Kardex de Productos
                    </h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="card-text">
                        Consulta el historial detallado de movimientos de un producto específico, 
                        incluyendo entradas, salidas y saldos por período.
                    </p>
                    <div class="mt-auto">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Movimientos por período</li>
                            <li><i class="fas fa-check text-success me-2"></i>Saldos iniciales y finales</li>
                            <li><i class="fas fa-check text-success me-2"></i>Filtro por bodega</li>
                            <li><i class="fas fa-check text-success me-2"></i>Valores de entrada y salida</li>
                        </ul>
                        <a href="{{ route('reportes.kardex') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-search me-1"></i>Generar Kardex
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Existencias Report -->
        <div class="col-lg-4 mb-4">
            <div class="card report-card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Reporte de Existencias
                    </h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="card-text">
                        Visualiza las existencias actuales de todos los productos en todas las bodegas, 
                        con opciones de filtrado y exportación.
                    </p>
                    <div class="mt-auto">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Existencias por bodega</li>
                            <li><i class="fas fa-check text-success me-2"></i>Costos promedio</li>
                            <li><i class="fas fa-check text-success me-2"></i>Filtros avanzados</li>
                            <li><i class="fas fa-check text-success me-2"></i>Exportación a CSV</li>
                        </ul>
                        <a href="{{ route('reportes.existencias') }}" class="btn btn-success btn-block">
                            <i class="fas fa-boxes me-1"></i>Ver Existencias
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asientos Contables Report -->
        <div class="col-lg-4 mb-4">
            <div class="card report-card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Asientos Contables
                    </h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="card-text">
                        Consulta todos los asientos contables generados por movimientos de inventario, 
                        con enlaces directos a los movimientos origen.
                    </p>
                    <div class="mt-auto">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Asientos por período</li>
                            <li><i class="fas fa-check text-success me-2"></i>Enlace a movimientos</li>
                            <li><i class="fas fa-check text-success me-2"></i>Débitos y créditos</li>
                            <li><i class="fas fa-check text-success me-2"></i>Filtros por origen</li>
                        </ul>
                        <a href="{{ route('reportes.asientos') }}" class="btn btn-info btn-block">
                            <i class="fas fa-file-invoice me-1"></i>Ver Asientos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Reports Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Reportes Adicionales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-exclamation-triangle text-warning me-2"></i>Productos con Stock Bajo</h6>
                            <p class="text-muted">Identifica productos que están por debajo del stock mínimo.</p>
                            <button class="btn btn-warning btn-sm" onclick="reporteStockBajo()">
                                <i class="fas fa-search me-1"></i>Ver Reporte
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-chart-pie text-primary me-2"></i>Rotación de Inventario</h6>
                            <p class="text-muted">Análisis de rotación y productos más/menos movidos.</p>
                            <button class="btn btn-primary btn-sm" onclick="reporteRotacion()">
                                <i class="fas fa-chart-bar me-1"></i>Próximamente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        loadDashboardStats();
    });

    function loadDashboardStats() {
        // Cargar estadísticas generales
        $.get('{{ route("reportes.options") }}')
            .done(function(response) {
                if (response.success) {
                    $('#totalProductos').text(response.data.productos.length);
                    $('#totalBodegas').text(response.data.bodegas.length);
                }
            })
            .fail(function() {
                // Fail silently
            });

        // Simular otras estadísticas - en producción vendrían de endpoints específicos
        $('#valorInventario').text('$0.00');
        $('#movimientosHoy').text('0');
    }

    function reporteStockBajo() {
        // Redirigir a existencias con filtro de stock bajo
        window.location.href = '{{ route("reportes.existencias") }}?stock_minimo=1';
    }

    function reporteRotacion() {
        showToast('Esta funcionalidad estará disponible próximamente', 'info');
    }
</script>

<style>
    .report-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .report-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
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
    
    .btn-block {
        width: 100%;
    }
    
    .card-header {
        border-radius: 10px 10px 0 0;
    }
    
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
</style>
@endsection
