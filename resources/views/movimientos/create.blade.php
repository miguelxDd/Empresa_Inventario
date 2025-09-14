@extends('layouts.app')

@section('title', 'Nuevo Movimiento de Inventario')

@push('styles')
<link href="{{ asset('css/movimientos.css') }}" rel="stylesheet">
@endpush

@section('header')
<div class="page-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="mb-0">
                    <i class="fas fa-plus me-3"></i>Nuevo Movimiento de Inventario
                </h1>
                <p class="mb-0 mt-2 opacity-75">Registra entradas, salidas, ajustes y transferencias de inventario</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <form id="movimientoForm">
        <div class="row">
            <!-- Información Principal -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Información del Movimiento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tipo_movimiento" class="form-label">Tipo de Movimiento *</label>
                                    <select class="form-select" id="tipo_movimiento" name="tipo_movimiento" required>
                                        <option value="">Seleccionar tipo...</option>
                                        <option value="entrada">
                                            <i class="fas fa-arrow-down"></i> Entrada
                                        </option>
                                        <option value="salida">
                                            <i class="fas fa-arrow-up"></i> Salida
                                        </option>
                                        <option value="ajuste">
                                            <i class="fas fa-tools"></i> Ajuste
                                        </option>
                                        <option value="transferencia">
                                            <i class="fas fa-exchange-alt"></i> Transferencia
                                        </option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="col-md-4" id="bodegaOrigenContainer" style="display: none;">
                                <div class="mb-3">
                                    <label for="bodega_origen_id" class="form-label">Bodega Origen *</label>
                                    <select class="form-select" id="bodega_origen_id" name="bodega_origen_id">
                                        <option value="">Seleccionar bodega origen...</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="col-md-4" id="bodegaDestinoContainer" style="display: none;">
                                <div class="mb-3">
                                    <label for="bodega_destino_id" class="form-label">Bodega Destino *</label>
                                    <select class="form-select" id="bodega_destino_id" name="bodega_destino_id">
                                        <option value="">Seleccionar bodega destino...</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="2" 
                                              placeholder="Descripción adicional del movimiento..."></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Líneas de Productos -->
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-boxes me-2"></i>Productos del Movimiento
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" onclick="agregarLinea()">
                            <i class="fas fa-plus me-1"></i>Agregar Producto
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="lineasTable">
                                <thead>
                                    <tr>
                                        <th width="35%">Producto</th>
                                        <th width="15%">Cantidad</th>
                                        <th width="15%">Unidad</th>
                                        <th width="15%">Costo Unit.</th>
                                        <th width="15%">Subtotal</th>
                                        <th width="5%">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="lineasTableBody">
                                    <!-- Las líneas se agregarán dinámicamente -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-dark">
                                        <th colspan="4" class="text-end">TOTAL:</th>
                                        <th id="totalGeneral">$0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div id="noLineasMessage" class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay productos agregados. Haz clic en "Agregar Producto" para comenzar.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Acciones -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>Acciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="guardarBtn">
                                <i class="fas fa-save me-2"></i>Guardar Movimiento
                            </button>
                            
                            <button type="button" class="btn btn-warning" onclick="limpiarFormulario()">
                                <i class="fas fa-broom me-2"></i>Limpiar Formulario
                            </button>
                            
                            <a href="{{ route('movimientos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>

                        <!-- Información de Tipo -->
                        <div class="mt-4" id="tipoInfo" style="display: none;">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle me-1"></i>Información
                                </h6>
                                <p class="mb-0" id="tipoDescripcion"></p>
                            </div>
                        </div>

                        <!-- Resumen -->
                        <div class="mt-4" id="resumenPanel" style="display: none;">
                            <h6>Resumen del Movimiento</h6>
                            <ul class="list-unstyled">
                                <li><strong>Total de líneas:</strong> <span id="totalLineas">0</span></li>
                                <li><strong>Total productos:</strong> <span id="totalProductos">0</span></li>
                                <li><strong>Valor total:</strong> <span id="valorTotal">$0.00</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal de Resultado -->
<div class="modal fade" id="resultadoModal" tabindex="-1" aria-labelledby="resultadoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="resultadoModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Movimiento Creado Exitosamente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h4>¡Movimiento Registrado!</h4>
                    <p class="mb-3">El movimiento de inventario ha sido creado y contabilizado exitosamente.</p>
                    
                    <div class="alert alert-success">
                        <h6 class="alert-heading">Información del Asiento Contable</h6>
                        <p class="mb-0">
                            <strong>Número de Asiento:</strong> <span id="asientoNumero" class="fw-bold"></span><br>
                            <strong>ID del Movimiento:</strong> <span id="movimientoId" class="fw-bold"></span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="nuevoMovimiento()">
                    <i class="fas fa-plus me-1"></i>Crear Nuevo Movimiento
                </button>
                <a href="{{ route('movimientos.index') }}" class="btn btn-success">
                    <i class="fas fa-list me-1"></i>Ver Todos los Movimientos
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let bodegas = [];
    let productos = [];
    let lineaCounter = 0;
    let costoEditable = false;

    $(document).ready(function() {
        // Load form options
        loadFormOptions();
        
        // Form events
        $('#tipo_movimiento').on('change', handleTipoChange);
        $('#movimientoForm').on('submit', handleFormSubmit);
        
        // Initialize
        updateBodegaVisibility();
        updateNoLineasMessage();
    });

    function loadFormOptions() {
        showLoading();
        
        $.get('{{ route("movimientos.options") }}')
            .done(function(response) {
                hideLoading();
                console.log('Respuesta de opciones del formulario:', response);
                if (response.success) {
                    bodegas = response.data.bodegas;
                    productos = response.data.productos;
                    
                    console.log('Bodegas cargadas:', bodegas.length);
                    console.log('Productos cargados:', productos.length);
                    
                    // Populate bodegas
                    populateBodegas();
                } else {
                    console.error('Error en respuesta de opciones:', response);
                }
            })
            .fail(function(xhr) {
                hideLoading();
                console.error('Error al cargar opciones:', xhr);
                handleAjaxError(xhr);
            });
    }

    function populateBodegas() {
        const origenSelect = $('#bodega_origen_id');
        const destinoSelect = $('#bodega_destino_id');
        
        console.log('Bodegas para popular:', bodegas);
        
        [origenSelect, destinoSelect].forEach(function(select) {
            select.empty().append('<option value="">Seleccionar bodega...</option>');
            bodegas.forEach(function(bodega) {
                console.log(`Agregando bodega: ID=${bodega.id}, Codigo=${bodega.codigo}, Nombre=${bodega.nombre}`);
                select.append(`<option value="${bodega.id}">${bodega.codigo} - ${bodega.nombre}</option>`);
            });
        });
        
        console.log('Select de origen opciones:', origenSelect.find('option').length);
        console.log('Select de destino opciones:', destinoSelect.find('option').length);
    }

    function handleTipoChange() {
        const tipo = $(this).val();
        costoEditable = ['entrada', 'ajuste'].includes(tipo);
        
        updateBodegaVisibility();
        updateTipoInfo(tipo);
        updateExistingLines();
    }

    function updateBodegaVisibility() {
        const tipo = $('#tipo_movimiento').val();
        const origenContainer = $('#bodegaOrigenContainer');
        const destinoContainer = $('#bodegaDestinoContainer');
        
        // Reset visibility
        origenContainer.hide();
        destinoContainer.hide();
        
        // Show based on type
        switch(tipo) {
            case 'entrada':
                destinoContainer.show();
                break;
            case 'salida':
                origenContainer.show();
                break;
            case 'ajuste':
                destinoContainer.show();
                break;
            case 'transferencia':
                origenContainer.show();
                destinoContainer.show();
                break;
        }
    }

    function updateTipoInfo(tipo) {
        const tipoInfo = $('#tipoInfo');
        const tipoDescripcion = $('#tipoDescripcion');
        
        const descripciones = {
            'entrada': 'Registra productos que ingresan al inventario. Puedes editar el costo unitario.',
            'salida': 'Registra productos que salen del inventario. El costo se calcula automáticamente.',
            'ajuste': 'Ajusta las existencias de productos. Puedes editar el costo unitario.',
            'transferencia': 'Transfiere productos entre bodegas. El costo se mantiene automáticamente.'
        };
        
        if (tipo && descripciones[tipo]) {
            tipoDescripcion.text(descripciones[tipo]);
            tipoInfo.show();
        } else {
            tipoInfo.hide();
        }
    }

    function agregarLinea() {
        const tipo = $('#tipo_movimiento').val();
        if (!tipo) {
            showToast('Selecciona primero el tipo de movimiento', 'warning');
            return;
        }
        
        lineaCounter++;
        const lineaId = `linea_${lineaCounter}`;
        
        const costoReadonly = costoEditable ? '' : 'readonly';
        const costoClass = costoEditable ? '' : 'bg-light';
        
        const lineaHtml = `
            <tr id="${lineaId}" data-linea-id="${lineaCounter}">
                <td>
                    <select class="form-select producto-select" name="lineas[${lineaCounter}][producto_id]" required>
                        <option value="">Seleccionar producto...</option>
                        ${productos.map(p => `<option value="${p.id}" data-precio-compra="${p.precio_compra}" data-precio-venta="${p.precio_venta}" data-unidad="${p.unidade_simbolo}">${p.texto_completo}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control cantidad-input" name="lineas[${lineaCounter}][cantidad]" 
                           step="0.01" min="0.01" placeholder="0.00" required>
                </td>
                <td>
                    <span class="unidad-display">-</span>
                </td>
                <td>
                    <input type="number" class="form-control costo-input ${costoClass}" name="lineas[${lineaCounter}][costo_unitario]" 
                           step="0.01" min="0" placeholder="0.00" ${costoReadonly}>
                </td>
                <td>
                    <span class="subtotal-display fw-bold">$0.00</span>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarLinea('${lineaId}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#lineasTableBody').append(lineaHtml);
        
        // Add event listeners
        const row = $(`#${lineaId}`);
        row.find('.producto-select').on('change', handleProductoChange);
        row.find('.cantidad-input, .costo-input').on('input', calculateSubtotal);
        
        updateNoLineasMessage();
        updateResumen();
    }

    function eliminarLinea(lineaId) {
        $(`#${lineaId}`).remove();
        updateNoLineasMessage();
        updateResumen();
        calculateTotal();
    }

    function handleProductoChange() {
        const row = $(this).closest('tr');
        const selectedOption = $(this).find(':selected');
        const unidadDisplay = row.find('.unidad-display');
        const costoInput = row.find('.costo-input');
        
        if (selectedOption.val()) {
            const unidad = selectedOption.data('unidad') || '-';
            unidadDisplay.text(unidad);
            
            if (costoEditable) {
                const precioCompra = selectedOption.data('precio-compra') || 0;
                costoInput.val(precioCompra);
            } else {
                // Load cost from warehouse if needed
                loadProductCost(selectedOption.val(), row);
            }
        } else {
            unidadDisplay.text('-');
            costoInput.val('');
        }
        
        calculateSubtotal.call(this);
    }

    function loadProductCost(productoId, row) {
        const bodegaId = $('#bodega_origen_id').val() || $('#bodega_destino_id').val();
        
        if (!bodegaId) return;
        
        $.get('{{ route("producto.cost") }}', {
            producto_id: productoId,
            bodega_id: bodegaId
        })
        .done(function(response) {
            if (response.success) {
                row.find('.costo-input').val(response.data.costo_promedio || 0);
                calculateSubtotal.call(row.find('.producto-select')[0]);
            }
        })
        .fail(function() {
            // Fail silently
        });
    }

    function calculateSubtotal() {
        const row = $(this).closest('tr');
        const cantidad = parseFloat(row.find('.cantidad-input').val()) || 0;
        const costo = parseFloat(row.find('.costo-input').val()) || 0;
        const subtotal = cantidad * costo;
        
        row.find('.subtotal-display').text(`$${subtotal.toFixed(2)}`);
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        $('.subtotal-display').each(function() {
            const subtotal = parseFloat($(this).text().replace('$', '')) || 0;
            total += subtotal;
        });
        
        $('#totalGeneral').text(`$${total.toFixed(2)}`);
        updateResumen();
    }

    function updateNoLineasMessage() {
        const hasLines = $('#lineasTableBody tr').length > 0;
        $('#noLineasMessage').toggle(!hasLines);
        $('#lineasTable').toggle(hasLines);
    }

    function updateResumen() {
        const totalLineas = $('#lineasTableBody tr').length;
        let totalProductos = 0;
        
        $('.cantidad-input').each(function() {
            totalProductos += parseFloat($(this).val()) || 0;
        });
        
        const valorTotal = $('#totalGeneral').text();
        
        $('#totalLineas').text(totalLineas);
        $('#totalProductos').text(totalProductos.toFixed(2));
        $('#valorTotal').text(valorTotal);
        
        $('#resumenPanel').toggle(totalLineas > 0);
    }

    function updateExistingLines() {
        $('#lineasTableBody tr').each(function() {
            const row = $(this);
            const costoInput = row.find('.costo-input');
            
            if (costoEditable) {
                costoInput.prop('readonly', false).removeClass('bg-light');
            } else {
                costoInput.prop('readonly', true).addClass('bg-light');
                
                // Reload cost if product is selected
                const productoSelect = row.find('.producto-select');
                if (productoSelect.val()) {
                    loadProductCost(productoSelect.val(), row);
                }
            }
        });
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        showLoading();
        clearFormErrors();
        
        const formData = new FormData(this);
        
        // Debug: mostrar todos los datos del formulario
        console.log('=== DATOS DEL FORMULARIO ===');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        console.log('=== FIN DATOS ===');
        
        $.ajax({
            url: '{{ route("movimientos.store") }}',
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
                showResultModal(response.data);
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
        const tipo = $('#tipo_movimiento').val();
        const lineas = $('#lineasTableBody tr').length;
        
        if (!tipo) {
            showToast('Selecciona el tipo de movimiento', 'error');
            return false;
        }
        
        if (lineas === 0) {
            showToast('Debes agregar al menos un producto', 'error');
            return false;
        }
        
        return true;
    }

    function showResultModal(data) {
        $('#asientoNumero').text(data.asiento_numero);
        $('#movimientoId').text(data.movimiento_id);
        $('#resultadoModal').modal('show');
    }

    function nuevoMovimiento() {
        $('#resultadoModal').modal('hide');
        limpiarFormulario();
    }

    function limpiarFormulario() {
        $('#movimientoForm')[0].reset();
        $('#lineasTableBody').empty();
        $('#tipoInfo').hide();
        $('#resumenPanel').hide();
        updateBodegaVisibility();
        updateNoLineasMessage();
        clearFormErrors();
        lineaCounter = 0;
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

    // Funciones auxiliares
    function showLoading() {
        console.log('Cargando...');
        // Aquí podrías mostrar un spinner de carga
    }

    function hideLoading() {
        console.log('Carga completada');
        // Aquí podrías ocultar el spinner de carga
    }

    function showToast(message, type = 'info') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: message,
                icon: type === 'error' ? 'error' : type === 'warning' ? 'warning' : 'info',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert(message);
        }
    }

    function handleAjaxError(xhr) {
        console.error('Error AJAX:', xhr);
        let message = 'Error en la petición';
        
        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        } else if (xhr.statusText) {
            message = xhr.statusText;
        }
        
        showToast(message, 'error');
    }
</script>

<style>
    .stats-card {
        transition: transform 0.2s;
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
    }
    
    .producto-select {
        font-size: 0.9em;
    }
    
    .table th {
        border-top: none;
        font-size: 0.9em;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    #guardarBtn {
        font-size: 1.1em;
        font-weight: bold;
    }
</style>
@endsection
