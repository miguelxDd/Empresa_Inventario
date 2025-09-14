@extends('layouts.app')

@section('title', 'Kardex de Productos')

@push('styles')
<link href="{{ asset('css/reportes.css') }}" rel="stylesheet">
@endpush

@section('header')
<div class="page-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="mb-0">
                    <i class="fas fa-list-alt me-3"></i>Kardex de Productos
                </h1>
                <p class="mb-0 mt-2 opacity-75">Historial detallado de movimientos por producto y período</p>
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
    <div class="row">
        <!-- Formulario de Filtros -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filtros de Consulta
                    </h5>
                </div>
                <div class="card-body">
                    <form id="kardexForm">
                        <div class="mb-3">
                            <label for="producto_id" class="form-label">Producto *</label>
                            <select class="form-select" id="producto_id" name="producto_id" required>
                                <option value="">Seleccionar producto...</option>
                                @foreach($productos as $producto)
                                    <option value="{{ $producto->id }}">{{ $producto->codigo }} - {{ $producto->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="bodega_id" class="form-label">Bodega (Opcional)</label>
                            <select class="form-select" id="bodega_id" name="bodega_id">
                                <option value="">Todas las bodegas</option>
                                @foreach($bodegas as $bodega)
                                    <option value="{{ $bodega->id }}">{{ $bodega->codigo }} - {{ $bodega->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha Inicio *</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_fin" class="form-label">Fecha Fin *</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search me-2"></i>Generar Kardex
                            </button>
                        </div>
                    </form>

                    <!-- Quick Date Filters -->
                    <div class="mt-3">
                        <h6>Períodos Predefinidos:</h6>
                        <div class="btn-group-vertical w-100" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('today')">
                                <i class="fas fa-calendar-day me-1"></i>Hoy
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('week')">
                                <i class="fas fa-calendar-week me-1"></i>Esta Semana
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('month')">
                                <i class="fas fa-calendar-alt me-1"></i>Este Mes
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('year')">
                                <i class="fas fa-calendar me-1"></i>Este Año
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados del Kardex -->
        <div class="col-md-8">
            <div id="kardexResults" style="display: none;">
                <!-- Header del Reporte -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Información del Kardex
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Producto:</strong> <span id="reporteProducto"></span><br>
                                <strong>Bodega:</strong> <span id="reporteBodega"></span><br>
                                <strong>Período:</strong> <span id="reportePeriodo"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Saldo Inicial:</strong> <span id="reporteSaldoInicial" class="fw-bold"></span><br>
                                <strong>Saldo Final:</strong> <span id="reporteSaldoFinal" class="fw-bold"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen del Período -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Resumen del Período
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="stat-box">
                                    <h4 class="text-success mb-0" id="totalEntradas">0</h4>
                                    <small>Total Entradas</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="stat-box">
                                    <h4 class="text-danger mb-0" id="totalSalidas">0</h4>
                                    <small>Total Salidas</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="stat-box">
                                    <h4 class="text-primary mb-0" id="valorEntradas">$0</h4>
                                    <small>Valor Entradas</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="stat-box">
                                    <h4 class="text-warning mb-0" id="valorSalidas">$0</h4>
                                    <small>Valor Salidas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Movimientos -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>Detalle de Movimientos
                        </h5>
                        <div>
                            <button class="btn btn-outline-success btn-sm" onclick="exportKardexCSV()">
                                <i class="fas fa-file-csv me-1"></i>Exportar CSV
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="printKardex()">
                                <i class="fas fa-print me-1"></i>Imprimir
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="kardexTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Observaciones</th>
                                        <th>Bodega Origen</th>
                                        <th>Bodega Destino</th>
                                        <th class="text-end">Entrada</th>
                                        <th class="text-end">Salida</th>
                                        <th class="text-end">Saldo</th>
                                        <th class="text-end">Costo Unit.</th>
                                        <th class="text-end">Valor</th>
                                    </tr>
                                </thead>
                                <tbody id="kardexTableBody">
                                    <!-- Los datos se cargarán via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado inicial -->
            <div id="kardexPlaceholder">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Generar Kardex</h4>
                        <p class="text-muted">Selecciona un producto y período para generar el reporte kardex</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let kardexData = null;

    $(document).ready(function() {
        // Set default dates
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        
        $('#fecha_inicio').val(firstDay.toISOString().split('T')[0]);
        $('#fecha_fin').val(today.toISOString().split('T')[0]);

        // Form submission
        $('#kardexForm').on('submit', handleFormSubmit);
    });

    function handleFormSubmit(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }

        showLoading();
        clearFormErrors();

        const formData = new FormData(this);

        $.ajax({
            url: '{{ route("reportes.kardex.generar") }}',
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
                kardexData = response.data;
                displayKardexResults(response.data);
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

    function validateForm() {
        const productoId = $('#producto_id').val();
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();

        if (!productoId) {
            showToast('Selecciona un producto', 'error');
            return false;
        }

        if (!fechaInicio || !fechaFin) {
            showToast('Selecciona las fechas de inicio y fin', 'error');
            return false;
        }

        if (new Date(fechaInicio) > new Date(fechaFin)) {
            showToast('La fecha de inicio debe ser menor o igual a la fecha fin', 'error');
            return false;
        }

        return true;
    }

    function displayKardexResults(data) {
        // Update report header
        $('#reporteProducto').text(`${data.producto.codigo} - ${data.producto.nombre}`);
        $('#reporteBodega').text(data.bodega ? `${data.bodega.codigo} - ${data.bodega.nombre}` : 'Todas las bodegas');
        $('#reportePeriodo').text(`${formatDate(data.periodo.fecha_inicio)} al ${formatDate(data.periodo.fecha_fin)}`);

        // Update summary
        $('#reporteSaldoInicial').text(data.saldo_inicial.toFixed(2));
        $('#reporteSaldoFinal').text(data.resumen.saldo_final.toFixed(2));
        $('#totalEntradas').text(data.resumen.total_entradas.toFixed(2));
        $('#totalSalidas').text(data.resumen.total_salidas.toFixed(2));
        $('#valorEntradas').text(`$${data.resumen.valor_total_entradas.toFixed(2)}`);
        $('#valorSalidas').text(`$${data.resumen.valor_total_salidas.toFixed(2)}`);

        // Update table
        const tbody = $('#kardexTableBody');
        tbody.empty();

        // Add initial balance row
        tbody.append(`
            <tr class="table-info">
                <td colspan="5"><strong>SALDO INICIAL</strong></td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end"><strong>${data.saldo_inicial.toFixed(2)}</strong></td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
            </tr>
        `);

        // Add movement rows
        data.movimientos.forEach(function(mov) {
            const tipoClass = getTipoMovimientoClass(mov.tipo_movimiento);
            const valorEntrada = mov.valor_entrada > 0 ? `$${mov.valor_entrada.toFixed(2)}` : '-';
            const valorSalida = mov.valor_salida > 0 ? `$${mov.valor_salida.toFixed(2)}` : '-';
            
            tbody.append(`
                <tr>
                    <td>${formatDate(mov.fecha)}</td>
                    <td><span class="badge ${tipoClass}">${mov.tipo_movimiento}</span></td>
                    <td>${mov.observaciones || '-'}</td>
                    <td>${mov.bodega_origen || '-'}</td>
                    <td>${mov.bodega_destino || '-'}</td>
                    <td class="text-end">${mov.entrada > 0 ? mov.entrada.toFixed(2) : '-'}</td>
                    <td class="text-end">${mov.salida > 0 ? mov.salida.toFixed(2) : '-'}</td>
                    <td class="text-end"><strong>${mov.saldo.toFixed(2)}</strong></td>
                    <td class="text-end">$${mov.costo_unitario.toFixed(2)}</td>
                    <td class="text-end">${mov.valor_entrada > 0 ? valorEntrada : valorSalida}</td>
                </tr>
            `);
        });

        // Show results and hide placeholder
        $('#kardexPlaceholder').hide();
        $('#kardexResults').show();
    }

    function getTipoMovimientoClass(tipo) {
        switch(tipo.toLowerCase()) {
            case 'entrada': return 'bg-success';
            case 'salida': return 'bg-danger';
            case 'ajuste': return 'bg-warning';
            case 'transferencia': return 'bg-info';
            default: return 'bg-secondary';
        }
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES');
    }

    function setDateRange(period) {
        const today = new Date();
        let startDate, endDate = today;

        switch(period) {
            case 'today':
                startDate = today;
                break;
            case 'week':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - today.getDay());
                break;
            case 'month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                break;
            case 'year':
                startDate = new Date(today.getFullYear(), 0, 1);
                break;
        }

        $('#fecha_inicio').val(startDate.toISOString().split('T')[0]);
        $('#fecha_fin').val(endDate.toISOString().split('T')[0]);
    }

    function exportKardexCSV() {
        if (!kardexData) {
            showToast('Genera primero el kardex', 'warning');
            return;
        }
        
        // TODO: Implementar exportación CSV
        showToast('Funcionalidad en desarrollo', 'info');
    }

    function printKardex() {
        if (!kardexData) {
            showToast('Genera primero el kardex', 'warning');
            return;
        }
        
        window.print();
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

<style>
    .stat-box {
        padding: 1rem;
        border-radius: 8px;
        background: rgba(255,255,255,0.1);
        margin-bottom: 0.5rem;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    @media print {
        .card-header,
        .btn,
        #kardexForm,
        .page-header {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .table {
            font-size: 0.8em;
        }
    }
</style>
@endsection
