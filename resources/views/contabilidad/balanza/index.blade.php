@extends('layouts.app')

@section('title', 'Balanza de Comprobación')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-balance-scale me-2"></i>
                        Balanza de Comprobación
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
                                <div class="col-md-4">
                                    <label for="fecha_inicio" class="form-label">Fecha Inicio <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="fecha_fin" class="form-label">Fecha Fin <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i>
                                        Consultar
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="btnLimpiar">
                                        <i class="fas fa-broom me-1"></i>
                                        Limpiar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de resultados -->
                    <div id="tablaContainer" style="display: none;">
                        <div class="table-responsive">
                            <table id="balanzaTable" class="table table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Cuenta</th>
                                        <th>Tipo</th>
                                        <th class="text-end">Debe</th>
                                        <th class="text-end">Haber</th>
                                        <th class="text-end">Saldo Deudor</th>
                                        <th class="text-end">Saldo Acreedor</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="table-info fw-bold">
                                        <th colspan="3" class="text-end">TOTALES:</th>
                                        <th class="text-end" id="totalDebe">0.00</th>
                                        <th class="text-end" id="totalHaber">0.00</th>
                                        <th class="text-end" id="totalSaldoDeudor">0.00</th>
                                        <th class="text-end" id="totalSaldoAcreedor">0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Resumen por tipo de cuenta -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-chart-pie me-2"></i>
                                            Resumen por Tipo de Cuenta
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="resumenTable" class="table table-sm table-bordered">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th>Tipo de Cuenta</th>
                                                        <th class="text-end">Debe</th>
                                                        <th class="text-end">Haber</th>
                                                        <th class="text-end">Saldo Deudor</th>
                                                        <th class="text-end">Saldo Acreedor</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="resumenTableBody">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Indicadores de balance -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Debe vs Haber</h6>
                                        <div id="balanceDebeHaber" class="h4">
                                            <span class="text-primary" id="statusDebeHaber">Verificando...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Saldos Deudor vs Acreedor</h6>
                                        <div id="balanceSaldos" class="h4">
                                            <span class="text-success" id="statusSaldos">Verificando...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje cuando no hay datos -->
                    <div id="sinDatos" class="text-center py-5" style="display: block;">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Seleccione un rango de fechas para consultar la balanza de comprobación</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .balance-ok {
        color: #28a745 !important;
    }
    .balance-error {
        color: #dc3545 !important;
    }
    .balance-warning {
        color: #ffc107 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    let table = null;
    let filtrosActivos = false;

    // Establecer fecha por defecto (primer día del mes actual)
    const hoy = new Date();
    const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    
    $('#fecha_inicio').val(primerDia.toISOString().split('T')[0]);
    $('#fecha_fin').val(hoy.toISOString().split('T')[0]);

    // Manejar envío del formulario
    $('#filtrosForm').on('submit', function(e) {
        e.preventDefault();
        
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        
        if (!fechaInicio || !fechaFin) {
            Swal.fire({
                title: 'Error',
                text: 'Debe seleccionar ambas fechas',
                icon: 'error'
            });
            return;
        }
        
        if (fechaInicio > fechaFin) {
            Swal.fire({
                title: 'Error',
                text: 'La fecha de inicio debe ser menor o igual a la fecha fin',
                icon: 'error'
            });
            return;
        }
        
        cargarDatos();
    });

    // Limpiar filtros
    $('#btnLimpiar').on('click', function() {
        $('#filtrosForm')[0].reset();
        $('#fecha_inicio').val(primerDia.toISOString().split('T')[0]);
        $('#fecha_fin').val(hoy.toISOString().split('T')[0]);
        
        if (table) {
            table.destroy();
            table = null;
        }
        
        $('#tablaContainer').hide();
        $('#sinDatos').show();
        $('#btnExportar').prop('disabled', true);
        $('#resumenTableBody').empty();
        filtrosActivos = false;
    });

    // Cargar datos en la tabla
    function cargarDatos() {
        if (table) {
            table.destroy();
        }

        $('#sinDatos').hide();
        $('#tablaContainer').show();

        table = $('#balanzaTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ route("contabilidad.balanza.data") }}',
                type: 'GET',
                data: function(d) {
                    d.fecha_inicio = $('#fecha_inicio').val();
                    d.fecha_fin = $('#fecha_fin').val();
                },
                dataSrc: function(json) {
                    // Actualizar totales
                    if (json.totales) {
                        $('#totalDebe').text(json.totales.debe);
                        $('#totalHaber').text(json.totales.haber);
                        $('#totalSaldoDeudor').text(json.totales.saldo_deudor);
                        $('#totalSaldoAcreedor').text(json.totales.saldo_acreedor);
                        
                        // Verificar balance
                        verificarBalance(json.totales);
                    }
                    
                    // Mostrar resumen por tipo
                    if (json.resumen_por_tipo) {
                        mostrarResumenPorTipo(json.resumen_por_tipo);
                    }
                    
                    return json.data;
                }
            },
            columns: [
                { data: 'codigo', width: '10%' },
                { data: 'cuenta', width: '35%' },
                { data: 'tipo', width: '10%' },
                { 
                    data: 'debe', 
                    width: '11%',
                    className: 'text-end'
                },
                { 
                    data: 'haber', 
                    width: '11%',
                    className: 'text-end'
                },
                { 
                    data: 'saldo_deudor', 
                    width: '11%',
                    className: 'text-end text-primary',
                    render: function(data) {
                        return data || '';
                    }
                },
                { 
                    data: 'saldo_acreedor', 
                    width: '12%',
                    className: 'text-end text-success',
                    render: function(data) {
                        return data || '';
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[0, 'asc']],
            pageLength: 50,
            responsive: true,
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip'
        });

        filtrosActivos = true;
        $('#btnExportar').prop('disabled', false);
    }

    // Verificar balance de la balanza
    function verificarBalance(totales) {
        const debe = parseFloat(totales.debe.replace(/,/g, ''));
        const haber = parseFloat(totales.haber.replace(/,/g, ''));
        const saldoDeudor = parseFloat(totales.saldo_deudor.replace(/,/g, ''));
        const saldoAcreedor = parseFloat(totales.saldo_acreedor.replace(/,/g, ''));
        
        // Verificar balance Debe vs Haber
        const balanceDebeHaber = Math.abs(debe - haber) < 0.01;
        const statusDebeHaber = $('#statusDebeHaber');
        
        if (balanceDebeHaber) {
            statusDebeHaber.text('✓ Balanceado').removeClass('text-danger').addClass('balance-ok');
        } else {
            statusDebeHaber.text('✗ Desbalanceado').removeClass('text-success').addClass('balance-error');
        }
        
        // Verificar balance Saldos
        const balanceSaldos = Math.abs(saldoDeudor - saldoAcreedor) < 0.01;
        const statusSaldos = $('#statusSaldos');
        
        if (balanceSaldos) {
            statusSaldos.text('✓ Balanceado').removeClass('text-danger').addClass('balance-ok');
        } else {
            statusSaldos.text('✗ Desbalanceado').removeClass('text-success').addClass('balance-error');
        }
    }

    // Mostrar resumen por tipo de cuenta
    function mostrarResumenPorTipo(resumen) {
        const tbody = $('#resumenTableBody');
        tbody.empty();
        
        $.each(resumen, function(tipo, datos) {
            const row = `
                <tr>
                    <td><strong>${datos.tipo}</strong></td>
                    <td class="text-end">${datos.debe}</td>
                    <td class="text-end">${datos.haber}</td>
                    <td class="text-end text-primary">${datos.saldo_deudor}</td>
                    <td class="text-end text-success">${datos.saldo_acreedor}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Exportar a CSV
    $('#btnExportar').on('click', function() {
        if (!filtrosActivos) {
            Swal.fire({
                title: 'Error',
                text: 'Debe aplicar filtros antes de exportar',
                icon: 'error'
            });
            return;
        }

        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        
        const url = '{{ route("contabilidad.balanza.export") }}?' + 
                   'fecha_inicio=' + fechaInicio + 
                   '&fecha_fin=' + fechaFin;
        
        window.open(url, '_blank');
    });
});
</script>
@endpush
