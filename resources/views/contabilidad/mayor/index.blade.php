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
                                <div class="col-md-4">
                                    <label for="cuenta_id" class="form-label">Cuenta <span class="text-danger">*</span></label>
                                    <select class="form-control" id="cuenta_id" name="cuenta_id" required>
                                        <option value="">Seleccione una cuenta...</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="btn-group w-100">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i>
                                            Consultar
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="btnLimpiar">
                                            <i class="fas fa-broom me-1"></i>
                                            Limpiar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Información de la cuenta seleccionada -->
                    <div id="infoCuenta" class="alert alert-info" style="display: none;">
                        <strong>Cuenta:</strong> <span id="cuentaInfo"></span><br>
                        <strong>Tipo:</strong> <span id="tipoInfo"></span>
                    </div>

                    <!-- Tabla de resultados -->
                    <div id="tablaContainer" style="display: none;">
                        <div class="table-responsive">
                            <table id="mayorTable" class="table table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Número</th>
                                        <th class="text-end">Debe</th>
                                        <th class="text-end">Haber</th>
                                        <th class="text-end">Saldo</th>
                                        <th>Concepto</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <th colspan="2" class="text-end">TOTALES PERÍODO:</th>
                                        <th class="text-end" id="totalDebe">0.00</th>
                                        <th class="text-end" id="totalHaber">0.00</th>
                                        <th class="text-end" id="saldoFinal">0.00</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <!-- Resumen de saldos -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="card-title">Saldo Inicial</h6>
                                        <h4 class="text-primary" id="saldoInicial">0.00</h4>
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
    let table = null;
    let filtrosActivos = false;

    // Establecer fecha por defecto (primer día del mes actual)
    const hoy = new Date();
    const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    
    $('#fecha_inicio').val(primerDia.toISOString().split('T')[0]);
    $('#fecha_fin').val(hoy.toISOString().split('T')[0]);

    // Inicializar Select2 para cuentas
    $('#cuenta_id').select2({
        theme: 'bootstrap-5',
        language: 'es',
        placeholder: 'Busque por código o nombre de cuenta...',
        allowClear: true,
        ajax: {
            url: '{{ route("contabilidad.cuentas.search") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        minimumInputLength: 2
    });

    // Manejar envío del formulario
    $('#filtrosForm').on('submit', function(e) {
        e.preventDefault();
        
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        const cuentaId = $('#cuenta_id').val();
        
        if (!fechaInicio || !fechaFin || !cuentaId) {
            Swal.fire({
                title: 'Error',
                text: 'Debe seleccionar fechas y cuenta',
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
        $('#cuenta_id').val(null).trigger('change');
        
        if (table) {
            table.destroy();
            table = null;
        }
        
        $('#tablaContainer').hide();
        $('#infoCuenta').hide();
        $('#sinDatos').show();
        $('#btnExportar').prop('disabled', true);
        filtrosActivos = false;
    });

    // Cargar datos en la tabla
    function cargarDatos() {
        if (table) {
            table.destroy();
        }

        $('#sinDatos').hide();
        
        table = $('#mayorTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ route("contabilidad.mayor.data") }}',
                type: 'GET',
                data: function(d) {
                    d.fecha_inicio = $('#fecha_inicio').val();
                    d.fecha_fin = $('#fecha_fin').val();
                    d.cuenta_id = $('#cuenta_id').val();
                },
                dataSrc: function(json) {
                    // Mostrar información de la cuenta
                    if (json.cuenta) {
                        $('#cuentaInfo').text(json.cuenta.codigo + ' - ' + json.cuenta.nombre);
                        $('#tipoInfo').text(json.cuenta.tipo.charAt(0).toUpperCase() + json.cuenta.tipo.slice(1));
                        $('#infoCuenta').show();
                    }
                    
                    // Actualizar totales
                    if (json.totales) {
                        $('#totalDebe').text(json.totales.debe);
                        $('#totalHaber').text(json.totales.haber);
                        $('#saldoFinal').text(json.totales.saldo_final);
                        $('#saldoInicial').text(json.totales.saldo_inicial);
                        $('#saldoFinalCard').text(json.totales.saldo_final);
                    }
                    
                    return json.data;
                }
            },
            columns: [
                { data: 'fecha', width: '12%' },
                { data: 'numero', width: '12%' },
                { 
                    data: 'debe', 
                    width: '12%',
                    className: 'text-end',
                    render: function(data) {
                        return data || '';
                    }
                },
                { 
                    data: 'haber', 
                    width: '12%',
                    className: 'text-end',
                    render: function(data) {
                        return data || '';
                    }
                },
                { 
                    data: 'saldo', 
                    width: '12%',
                    className: 'text-end fw-bold'
                },
                { data: 'concepto', width: '30%' },
                { 
                    data: 'acciones', 
                    width: '10%',
                    className: 'text-center',
                    orderable: false
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[0, 'asc'], [1, 'asc']],
            pageLength: 25,
            responsive: true,
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip'
        });

        $('#tablaContainer').show();
        filtrosActivos = true;
        $('#btnExportar').prop('disabled', false);
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
        const cuentaId = $('#cuenta_id').val();
        
        const url = '{{ route("contabilidad.mayor.export") }}?' + 
                   'fecha_inicio=' + fechaInicio + 
                   '&fecha_fin=' + fechaFin +
                   '&cuenta_id=' + cuentaId;
        
        window.open(url, '_blank');
    });
});

// Función global para modal de asiento
function verAsiento(asientoId) {
    $('#modalAsientoContent').html('<p>Cargando asiento #' + asientoId + '...</p>');
    $('#modalAsiento').modal('show');
}
</script>
@endpush
