// productos.js - JavaScript for products management

let productosTable;
let editingProductId = null;

$(document).ready(function() {
    // Initialize DataTable
    initializeTable();
    
    // Load form options
    loadFormOptions();
    
    // Load statistics
    loadStatistics();
    
    // Form submission
    $('#productoForm').on('submit', handleFormSubmit);
});

function initializeTable() {
    productosTable = $('#productosTable').DataTable({
        ajax: {
            url: window.productosUrls.tableData,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        },
        columns: [
            { data: 'codigo' },
            { data: 'nombre' },
            { data: 'categoria_nombre' },
            { data: 'unidade_nombre' },
            { data: 'precio_formateado' },
            { 
                data: 'estado',
                render: function(data, type, row) {
                    const badgeClass = row.activo ? 'bg-success' : 'bg-danger';
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            {
                data: 'id',
                orderable: false,
                render: function(data, type, row) {
                    const toggleClass = row.activo ? 'btn-secondary' : 'btn-success';
                    const toggleTitle = row.activo ? 'Desactivar' : 'Activar';
                    const toggleIcon = row.activo ? 'fas fa-eye-slash' : 'fas fa-eye';
                    
                    return `
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-info me-1" onclick="verExistencias(${data})" title="Ver Existencias">
                                <i class="fas fa-boxes"></i>
                            </button>
                            <button class="btn btn-sm btn-warning me-1" onclick="editarProducto(${data})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm ${toggleClass} me-1" onclick="toggleEstado(${data})" title="${toggleTitle}">
                                <i class="${toggleIcon}"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${data})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        ...window.DataTablesSpanish,
        responsive: true,
        pageLength: 25
    });
}

function loadFormOptions() {
    $.get(window.productosUrls.options)
        .done(function(response) {
            if (response.success) {
                // Load categories
                const categoriaSelect = $('#categoria_id');
                categoriaSelect.empty().append('<option value="">Seleccionar categoría...</option>');
                response.data.categorias.forEach(function(categoria) {
                    categoriaSelect.append(`<option value="${categoria.id}">${categoria.nombre}</option>`);
                });

                // Load units
                const unidadSelect = $('#unidad_id');
                unidadSelect.empty().append('<option value="">Seleccionar unidad...</option>');
                response.data.unidades.forEach(function(unidade) {
                    unidadSelect.append(`<option value="${unidade.id}">${unidade.nombre} (${unidade.abreviatura || ''})</option>`);
                });

                // Load accounts
                const cuentaSelects = ['#cuenta_inventario_id', '#cuenta_costo_id', '#cuenta_contraparte_id'];
                cuentaSelects.forEach(function(selector) {
                    const select = $(selector);
                    select.empty().append('<option value="">Seleccionar cuenta...</option>');
                    response.data.cuentas.forEach(function(cuenta) {
                        select.append(`<option value="${cuenta.id}">${cuenta.codigo} - ${cuenta.nombre}</option>`);
                    });
                });
            }
        })
        .fail(handleAjaxError);
}

function loadStatistics() {
    $.get('/api/inventario/estadisticas')
        .done(function(response) {
            if (response.estadisticas) {
                $('#totalProductos').text(response.estadisticas.total_productos);
                $('#productosActivos').text(response.estadisticas.productos_activos);
                $('#sinStock').text(response.estadisticas.sin_stock);
                $('#categorias').text(response.estadisticas.categorias_utilizadas);
            }
        })
        .fail(function() {
            // Fail silently for statistics
        });
}

function nuevoProducto() {
    editingProductId = null;
    $('#productoForm')[0].reset();
    $('#productoModalLabel').html('<i class="fas fa-box me-2"></i>Nuevo Producto');
    clearFormErrors();
    $('#productoModal').modal('show');
}

function editarProducto(id) {
    editingProductId = id;
    showLoading();
    
    console.log('Editando producto ID:', id);
    console.log('URL de edición:', window.productosUrls.edit.replace(':id', id));
    
    $.get(window.productosUrls.edit.replace(':id', id))
        .done(function(response) {
            console.log('Respuesta del servidor:', response);
            hideLoading();
            
            if (response.success) {
                const producto = response.data.producto;
                
                console.log('Datos del producto:', producto);
                
                // Fill basic information
                $('#sku').val(producto.codigo || '');
                $('#nombre').val(producto.nombre || '');
                $('#descripcion').val(producto.descripcion || '');
                
                // Fill classification
                $('#categoria_id').val(producto.categoria_id || '');
                $('#unidad_id').val(producto.unidad_id || '');
                $('#activo').prop('checked', Boolean(producto.activo));
                
                // Fill prices and stock
                $('#precio_compra').val(producto.precio_compra || '');
                $('#precio_venta').val(producto.precio_venta || '');
                $('#stock_minimo').val(producto.stock_minimo || '');
                $('#stock_maximo').val(producto.stock_maximo || '');
                
                // Fill accounting accounts
                $('#cuenta_inventario_id').val(producto.cuenta_inventario_id || '');
                $('#cuenta_costo_id').val(producto.cuenta_costo_id || '');
                $('#cuenta_contraparte_id').val(producto.cuenta_contraparte_id || '');
                
                $('#productoModalLabel').html('<i class="fas fa-edit me-2"></i>Editar Producto');
                clearFormErrors();
                $('#productoModal').modal('show');
                
                console.log('Modal configurado para edición');
            } else {
                console.error('Error en respuesta:', response.message);
                alert('Error al cargar los datos del producto: ' + (response.message || 'Error desconocido'));
            }
        })
        .fail(function(xhr, status, error) {
            hideLoading();
            console.error('Error AJAX:', error);
            console.error('Respuesta del servidor:', xhr.responseText);
            handleAjaxError(xhr);
        });
}

function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = editingProductId ? 
        window.productosUrls.update.replace(':id', editingProductId) : 
        window.productosUrls.store;
    
    if (editingProductId) {
        formData.append('_method', 'PUT');
    }
    
    showLoading();
    clearFormErrors();
    
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function(response) {
        hideLoading();
        if (response.success) {
            $('#productoModal').modal('hide');
            productosTable.ajax.reload();
            loadStatistics();
            showToast(response.message);
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

function toggleEstado(id) {
    Swal.fire({
        title: '¿Cambiar estado?',
        text: '¿Está seguro de cambiar el estado del producto?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: window.productosUrls.toggle.replace(':id', id),
                type: 'PATCH',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function(response) {
                if (response.success) {
                    productosTable.ajax.reload();
                    loadStatistics();
                    showToast(response.message);
                }
            })
            .fail(handleAjaxError);
        }
    });
}

function eliminarProducto(id) {
    Swal.fire({
        title: '¿Eliminar producto?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: window.productosUrls.destroy.replace(':id', id),
                type: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function(response) {
                if (response.success) {
                    productosTable.ajax.reload();
                    loadStatistics();
                    showToast(response.message);
                }
            })
            .fail(handleAjaxError);
        }
    });
}

function verExistencias(id) {
    showLoading();
    
    $.get(window.productosUrls.existencias.replace(':id', id))
        .done(function(response) {
            hideLoading();
            if (response.success) {
                displayExistencias(response.data);
                $('#existenciasModal').modal('show');
            }
        })
        .fail(function(xhr) {
            hideLoading();
            handleAjaxError(xhr);
        });
}

function displayExistencias(data) {
    const producto = data.producto;
    const existencias = data.existencias;
    const resumen = data.resumen;
    
    let html = `
        <div class="mb-4">
            <h6><strong>Producto:</strong> ${producto.codigo} - ${producto.nombre}</h6>
        </div>
    `;
    
    if (existencias.length > 0) {
        html += `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Bodega</th>
                            <th>Código</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        existencias.forEach(function(existencia) {
            const stockClass = existencia.stock_actual <= existencia.stock_minimo ? 'text-danger' : 'text-success';
            html += `
                <tr>
                    <td>${existencia.bodega_nombre}</td>
                    <td>${existencia.bodega_codigo}</td>
                    <td class="${stockClass}">${existencia.stock_actual}</td>
                    <td>${existencia.stock_minimo || 0}</td>
                    <td>
                        ${existencia.stock_actual <= existencia.stock_minimo ? 
                            '<span class="badge bg-warning">Stock Bajo</span>' : 
                            '<span class="badge bg-success">Normal</span>'}
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="2">Total General</th>
                            <th class="text-light">${resumen.stock_total}</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
    } else {
        html += `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No hay existencias registradas para este producto en ninguna bodega.
            </div>
        `;
    }
    
    $('#existenciasContent').html(html);
}

function recargarTabla() {
    productosTable.ajax.reload();
    loadStatistics();
    showToast('Tabla actualizada', 'info');
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
