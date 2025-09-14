@extends('layouts.app')

@section('title', 'Libro Diario')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-book me-2"></i>
                        Libro Diario
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
                            <table id="diarioTable" class="table table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Número</th>
                                        <th>Descripción</th>
                                        <th>Cuenta</th>
                                        <th class="text-end">Debe</th>
                                        <th class="text-end">Haber</th>
                                        <th>Concepto</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <th colspan="4" class="text-end">TOTALES:</th>
                                        <th class="text-end" id="totalDebe">0.00</th>
                                        <th class="text-end" id="totalHaber">0.00</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Mensaje cuando no hay datos -->
                    <div id="sinDatos" class="text-center py-5" style="display: block;">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Seleccione un rango de fechas para consultar el libro diario</h5>
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

<!-- Modal para ver movimiento -->
<div class="modal fade" id="modalMovimiento" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Movimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalMovimientoContent">
                <!-- Contenido del movimiento -->
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
    .btn-group-sm > .btn {
        padding: 0.25rem 0.4rem;
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
        filtrosActivos = false;
    });

    // Cargar datos en la tabla
    function cargarDatos() {
        if (table) {
            table.destroy();
        }

        $('#sinDatos').hide();
        $('#tablaContainer').show();

        table = $('#diarioTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ route("contabilidad.diario.data") }}',
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
                    }
                    return json.data;
                }
            },
            columns: [
                { data: 'fecha', width: '8%' },
                { data: 'numero', width: '8%' },
                { data: 'descripcion', width: '20%' },
                { data: 'cuenta', width: '25%' },
                { 
                    data: 'debe', 
                    width: '10%',
                    className: 'text-end',
                    render: function(data) {
                        return data || '';
                    }
                },
                { 
                    data: 'haber', 
                    width: '10%',
                    className: 'text-end',
                    render: function(data) {
                        return data || '';
                    }
                },
                { data: 'concepto', width: '14%' },
                { 
                    data: 'acciones', 
                    width: '5%',
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
        
        const url = '{{ route("contabilidad.diario.export") }}?' + 
                   'fecha_inicio=' + fechaInicio + 
                   '&fecha_fin=' + fechaFin;
        
        window.open(url, '_blank');
    });
});

// Funciones globales para modales
function verAsiento(asientoId) {
    // Implementar lógica para mostrar el asiento
    $('#modalAsientoContent').html('<p>Cargando asiento #' + asientoId + '...</p>');
    $('#modalAsiento').modal('show');
    
    // Aquí puedes agregar una llamada AJAX para obtener el detalle del asiento
}

function verMovimiento(movimientoId) {
    // Implementar lógica para mostrar el movimiento
    $('#modalMovimientoContent').html('<p>Cargando movimiento #' + movimientoId + '...</p>');
    $('#modalMovimiento').modal('show');
    
    // Aquí puedes agregar una llamada AJAX para obtener el detalle del movimiento
}
</script>
@endpush
