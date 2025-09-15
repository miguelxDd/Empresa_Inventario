@extends('layouts.app')

@section('title', 'Libro Mayor')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Libro Mayor
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" id="btnExportar" disabled>
                            <i class="fas fa-file-excel me-1"></i>
                            Exportar CSV
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form id="filtrosForm" class="row g-3">
                                <div class="col-md-3">
                                    <label for="fecha_inicio" class="form-label">Fecha Inicio <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="fecha_fin" class="form-label">Fecha Fin <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="tipo_reporte" class="form-label">Tipo de Reporte <span class="text-danger">*</span></label>
                                    <select class="form-control" id="tipo_reporte" name="tipo_reporte" required>
                                        <option value="">Seleccione tipo de reporte...</option>
                                        <option value="general">Mayor General (Todas las cuentas)</option>
                                        <option value="cuenta">Mayor por Cuenta (Una sola cuenta)</option>
                                    </select>
                                </div>
                                <div class="col-md-3" id="cuenta_selector" style="display: none;">
                                    <label for="cuenta_id" class="form-label">Cuenta <span class="text-danger">*</span></label>
                                    <select class="form-control" id="cuenta_id" name="cuenta_id">
                                        <option value="">Seleccione una cuenta...</option>
                                    </select>
                                </div>
                                <div class="col-md-12 d-flex justify-content-start gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>
                                        Generar Reporte
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="btnLimpiar">
                                        <i class="fas fa-broom me-1"></i>
                                        Limpiar
                                    </button>
                                    <button type="button" class="btn btn-info" id="btnFiltrosRapidos" data-bs-toggle="dropdown">
                                        <i class="fas fa-filter me-1"></i>
                                        Filtros Rápidos
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="setFiltroHoy()">Hoy</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="setFiltroMes()">Este Mes</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="setFiltroAnio()">Este Año</a></li>
                                    </ul>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Resultados del Reporte -->
                    <div id="resultadosContainer" style="display: none;">
                        <!-- Información del reporte -->
                        <div id="infoReporte" class="alert alert-info">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="mb-2">
                                        <i class="fas fa-clipboard-list me-2"></i>
                                        <span id="tituloReporte"></span>
                                    </h5>
                                    <p class="mb-1"><strong>Período:</strong> <span id="periodoReporte"></span></p>
                                    <p class="mb-0" id="cuentaReporte" style="display: none;"><strong>Cuenta:</strong> <span id="cuentaInfo"></span></p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnImprimir">
                                        <i class="fas fa-print me-1"></i>
                                        Imprimir
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" id="btnExportarResultados">
                                        <i class="fas fa-file-excel me-1"></i>
                                        Exportar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Contenedor del Mayor General -->
                        <div id="mayorGeneral" style="display: none;">
                            <div id="cuentasSecciones"></div>
                        </div>

                        <!-- Contenedor del Mayor por Cuenta -->
                        <div id="mayorCuenta" style="display: none;">
                            <div id="cuentaIndividual"></div>
                        </div>

                        <!-- Resumen General -->
                        <div id="resumenGeneral" class="row mt-4" style="display: none;">
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-primary">Total Debe</h6>
                                        <h4 class="text-primary" id="resumenTotalDebe">$0.00</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-success">Total Haber</h6>
                                        <h4 class="text-success" id="resumenTotalHaber">$0.00</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-info">Diferencia</h6>
                                        <h4 class="text-info" id="resumenDiferencia">$0.00</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h6 class="card-title">Saldo Final</h6>
                                        <h4 class="text-success" id="saldoFinalCard">0.00</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje cuando no hay datos -->
                    <div id="sinDatos" class="text-center py-5" style="display: block;">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Seleccione un rango de fechas y una cuenta para consultar el libro mayor</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver asiento -->
<div class="modal fade" id="modalAsiento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Asiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalAsientoContent">
                <!-- Contenido del asiento -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.4rem;
    }
    .select2-container {
        width: 100% !important;
    }
</style>
@endpush
@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>
<script>
$(document).ready(function() {
    let filtrosActivos = false;
    
    // Establecer fecha por defecto (primer día del mes actual)
    const hoy = new Date();
    const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    
    $('#fecha_inicio').val(primerDia.toISOString().split('T')[0]);
    $('#fecha_fin').val(hoy.toISOString().split('T')[0]);

    // Inicializar Select2 para cuentas
    $('#cuenta_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccione una cuenta...',
        allowClear: true
    });
    
    // Cargar cuentas disponibles
    cargarCuentas();
    
    // Event listeners
    $('#tipo_reporte').change(function() {
        const tipoReporte = $(this).val();
        if (tipoReporte === 'cuenta') {
            $('#cuenta_selector').show();
            $('#cuenta_id').prop('required', true);
        } else {
            $('#cuenta_selector').hide();
            $('#cuenta_id').prop('required', false);
        }
    });
    
    $('#filtrosForm').submit(function(e) {
        e.preventDefault();
        generarReporte();
    });
    
    $('#btnLimpiar').click(function() {
        limpiarFiltros();
    });
    
    $('#btnImprimir').click(function() {
        window.print();
    });
    
    $('#btnExportarResultados').click(function() {
        exportarReporte();
    });
});

function cargarCuentas() {
    $.get('{{ route("reportes.options") }}')
        .done(function(response) {
            if (response.success && response.data && response.data.cuentas) {
                const cuentas = response.data.cuentas;
                let options = '<option value="">Seleccione una cuenta...</option>';
                
                cuentas.forEach(function(cuenta) {
                    options += `<option value="${cuenta.id}">${cuenta.codigo} - ${cuenta.nombre}</option>`;
                });
                
                $('#cuenta_id').html(options);
            }
        })
        .fail(function() {
            showToast('Error al cargar las cuentas', 'error');
        });
}

function generarReporte() {
    const formData = {
        fecha_inicio: $('#fecha_inicio').val(),
        fecha_fin: $('#fecha_fin').val(),
        tipo_reporte: $('#tipo_reporte').val()
    };
    
    // Solo incluir cuenta_id si el tipo es 'cuenta'
    if (formData.tipo_reporte === 'cuenta') {
        formData.cuenta_id = $('#cuenta_id').val();
    }
    
    // Validaciones
    if (!formData.fecha_inicio || !formData.fecha_fin || !formData.tipo_reporte) {
        showToast('Por favor complete todos los campos requeridos', 'warning');
        return;
    }
    
    if (formData.tipo_reporte === 'cuenta' && !formData.cuenta_id) {
        showToast('Por favor seleccione una cuenta', 'warning');
        return;
    }
    
    // Mostrar loading
    $('#resultadosContainer').hide();
    $('#sinDatos').hide();
    showToast('Generando reporte, por favor espere...', 'info');
    
    // Llamada AJAX
    $.get('{{ route("contabilidad.mayor.data") }}', formData)
        .done(function(response) {
            if (response.success) {
                mostrarResultados(response.data, formData);
            } else {
                showToast('Error: ' + (response.message || 'Error desconocido'), 'error');
            }
        })
        .fail(function(xhr) {
            console.log('Error response:', xhr.responseJSON);
            let errorMessage = 'Error de conexión';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                errorMessage = errors.join(', ');
            }
            showToast('Error al generar el reporte: ' + errorMessage, 'error');
        });
}

function mostrarResultados(data, filtros) {
    // Actualizar información del reporte
    const fechaInicio = new Date(filtros.fecha_inicio).toLocaleDateString('es-ES');
    const fechaFin = new Date(filtros.fecha_fin).toLocaleDateString('es-ES');
    
    $('#periodoReporte').text(`${fechaInicio} - ${fechaFin}`);
    
    if (filtros.tipo_reporte === 'general') {
        $('#tituloReporte').text('Libro Mayor General');
        $('#cuentaReporte').hide();
        mostrarMayorGeneral(data);
    } else {
        $('#tituloReporte').text('Libro Mayor por Cuenta');
        $('#cuentaInfo').text(data.cuenta_info.codigo + ' - ' + data.cuenta_info.nombre);
        $('#cuentaReporte').show();
        mostrarMayorCuenta(data);
    }
    
    // Mostrar resumen general
    if (data.resumen_general) {
        $('#resumenTotalDebe').text('$' + parseFloat(data.resumen_general.total_debe || 0).toLocaleString('es-CO', {minimumFractionDigits: 2}));
        $('#resumenTotalHaber').text('$' + parseFloat(data.resumen_general.total_haber || 0).toLocaleString('es-CO', {minimumFractionDigits: 2}));
        $('#resumenDiferencia').text('$' + parseFloat(data.resumen_general.diferencia || 0).toLocaleString('es-CO', {minimumFractionDigits: 2}));
        $('#resumenGeneral').show();
    }
    
    $('#resultadosContainer').show();
    $('#sinDatos').hide();
    filtrosActivos = true;
}

function mostrarMayorGeneral(data) {
    $('#mayorCuenta').hide();
    
    let html = '';
    
    if (data.cuentas && data.cuentas.length > 0) {
        data.cuentas.forEach(function(cuenta) {
            html += generarSeccionCuenta(cuenta);
        });
    } else {
        html = '<div class="alert alert-warning">No se encontraron movimientos para el período seleccionado.</div>';
    }
    
    $('#cuentasSecciones').html(html);
    $('#mayorGeneral').show();
}

function mostrarMayorCuenta(data) {
    $('#mayorGeneral').hide();
    
    let html = '';
    
    if (data.cuenta) {
        html = generarSeccionCuenta(data.cuenta);
    } else {
        html = '<div class="alert alert-warning">No se encontraron movimientos para la cuenta seleccionada en el período indicado.</div>';
    }
    
    $('#cuentaIndividual').html(html);
    $('#mayorCuenta').show();
}

function generarSeccionCuenta(cuenta) {
    const saldoInicial = parseFloat(cuenta.saldo_inicial || 0);
    const totalDebe = parseFloat(cuenta.total_debe || 0);
    const totalHaber = parseFloat(cuenta.total_haber || 0);
    const saldoFinal = parseFloat(cuenta.saldo_final || 0);
    
    // Determinar naturaleza del saldo final
    let naturalezaSaldoFinal = '';
    let colorSaldo = '';
    if (Math.abs(saldoFinal) < 0.01) {
        naturalezaSaldoFinal = 'Saldado';
        colorSaldo = 'text-success';
    } else if (saldoFinal > 0) {
        if (cuenta.tipo === 'ACTIVO' || cuenta.tipo === 'GASTO') {
            naturalezaSaldoFinal = 'Deudor';
            colorSaldo = 'text-primary';
        } else {
            naturalezaSaldoFinal = 'Acreedor';
            colorSaldo = 'text-danger';
        }
    } else {
        if (cuenta.tipo === 'ACTIVO' || cuenta.tipo === 'GASTO') {
            naturalezaSaldoFinal = 'Acreedor';
            colorSaldo = 'text-danger';
        } else {
            naturalezaSaldoFinal = 'Deudor';
            colorSaldo = 'text-primary';
        }
    }
    
    let html = `
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-ledger me-2"></i>
                    ${cuenta.codigo} - ${cuenta.nombre}
                    <span class="badge bg-light text-dark ms-2">${cuenta.tipo}</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="12%">Fecha</th>
                                <th width="12%">Póliza/Número</th>
                                <th width="35%">Concepto</th>
                                <th width="12%" class="text-end">Debe</th>
                                <th width="12%" class="text-end">Haber</th>
                                <th width="12%" class="text-end">Saldo</th>
                                <th width="5%" class="text-center">Ver</th>
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    // Fila de saldo inicial
    if (Math.abs(saldoInicial) > 0.01) {
        html += `
            <tr class="table-info">
                <td colspan="3"><strong>SALDO INICIAL</strong></td>
                <td class="text-end">${saldoInicial > 0 ? '$' + Math.abs(saldoInicial).toLocaleString('es-CO', {minimumFractionDigits: 2}) : '-'}</td>
                <td class="text-end">${saldoInicial < 0 ? '$' + Math.abs(saldoInicial).toLocaleString('es-CO', {minimumFractionDigits: 2}) : '-'}</td>
                <td class="text-end"><strong>$${Math.abs(saldoInicial).toLocaleString('es-CO', {minimumFractionDigits: 2})}</strong></td>
                <td></td>
            </tr>
        `;
    }
    
    // Movimientos
    if (cuenta.movimientos && cuenta.movimientos.length > 0) {
        cuenta.movimientos.forEach(function(mov) {
            html += `
                <tr>
                    <td>${mov.fecha}</td>
                    <td>${mov.numero}</td>
                    <td>${mov.concepto}</td>
                    <td class="text-end">${parseFloat(mov.debe || 0) > 0 ? '$' + parseFloat(mov.debe).toLocaleString('es-CO', {minimumFractionDigits: 2}) : '-'}</td>
                    <td class="text-end">${parseFloat(mov.haber || 0) > 0 ? '$' + parseFloat(mov.haber).toLocaleString('es-CO', {minimumFractionDigits: 2}) : '-'}</td>
                    <td class="text-end"><strong>$${Math.abs(parseFloat(mov.saldo_acumulado || 0)).toLocaleString('es-CO', {minimumFractionDigits: 2})}</strong></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="verAsiento(${mov.asiento_id})" title="Ver asiento">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td colspan="7" class="text-center text-muted">No hay movimientos en el período seleccionado</td>
            </tr>
        `;
    }
    
    // Fila de totales
    html += `
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="3" class="text-end">TOTALES PERÍODO:</th>
                                <th class="text-end">$${totalDebe.toLocaleString('es-CO', {minimumFractionDigits: 2})}</th>
                                <th class="text-end">$${totalHaber.toLocaleString('es-CO', {minimumFractionDigits: 2})}</th>
                                <th class="text-end">$${Math.abs(saldoFinal).toLocaleString('es-CO', {minimumFractionDigits: 2})}</th>
                                <th></th>
                            </tr>
                            <tr class="table-info">
                                <th colspan="5" class="text-end">SALDO FINAL (${naturalezaSaldoFinal}):</th>
                                <th class="text-end ${colorSaldo}">$${Math.abs(saldoFinal).toLocaleString('es-CO', {minimumFractionDigits: 2})}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    return html;
}

function limpiarFiltros() {
    $('#filtrosForm')[0].reset();
    $('#cuenta_selector').hide();
    $('#cuenta_id').prop('required', false);
    $('#resultadosContainer').hide();
    $('#resumenGeneral').hide();
    $('#sinDatos').show();
    filtrosActivos = false;
    
    // Restaurar fechas por defecto
    const hoy = new Date();
    const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    $('#fecha_inicio').val(primerDia.toISOString().split('T')[0]);
    $('#fecha_fin').val(hoy.toISOString().split('T')[0]);
}

function setFiltroHoy() {
    const today = new Date().toISOString().split('T')[0];
    $('#fecha_inicio').val(today);
    $('#fecha_fin').val(today);
}

function setFiltroMes() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    $('#fecha_inicio').val(firstDay.toISOString().split('T')[0]);
    $('#fecha_fin').val(today.toISOString().split('T')[0]);
}

function setFiltroAnio() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), 0, 1);
    $('#fecha_inicio').val(firstDay.toISOString().split('T')[0]);
    $('#fecha_fin').val(today.toISOString().split('T')[0]);
}

function exportarReporte() {
    if (!filtrosActivos) {
        showToast('Debe generar un reporte antes de exportar', 'warning');
        return;
    }
    
    const formData = {
        fecha_inicio: $('#fecha_inicio').val(),
        fecha_fin: $('#fecha_fin').val(),
        tipo_reporte: $('#tipo_reporte').val(),
        cuenta_id: $('#cuenta_id').val() || null
    };
    
    const params = new URLSearchParams(formData);
    const url = '{{ route("contabilidad.mayor.export") }}?' + params.toString();
    window.open(url, '_blank');
}

function verAsiento(asientoId) {
    window.open('{{ route("reportes.asientos") }}#asiento-' + asientoId, '_blank');
}

function showToast(message, type = 'info') {
    const alertClass = type === 'error' ? 'alert-danger' : 
                     type === 'warning' ? 'alert-warning' : 
                     type === 'success' ? 'alert-success' : 'alert-info';
    
    const toast = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 1050;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(function() {
        toast.alert('close');
    }, 5000);
}
</script>
@endpush 
