@extends('layouts.app')

@section('title', 'Gestión de Bodegas')

@section('content')
<div class="container-fluid">
    <!-- Button trigger modal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-warehouse me-2"></i>Gestión de Bodegas
        </h2>
        <button type="button" class="btn btn-primary" onclick="nuevaBodega()">
            <i class="fas fa-plus me-1"></i>Nueva Bodega
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card border-left-primary">
                <div class="card-body text-center">
                    <i class="fas fa-warehouse fa-2x mb-2 text-primary"></i>
                    <h4 class="mb-0" id="totalBodegas">0</h4>
                    <small>Total Bodegas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-left-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                    <h4 class="mb-0" id="bodegasActivas">0</h4>
                    <small>Activas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-left-warning">
                <div class="card-body text-center">
                    <i class="fas fa-pause-circle fa-2x mb-2 text-warning"></i>
                    <h4 class="mb-0" id="bodegasInactivas">0</h4>
                    <small>Inactivas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-left-info">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-2x mb-2 text-info"></i>
                    <h4 class="mb-0" id="conInventario">0</h4>
                    <small>Con Inventario</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bodegas Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Listado de Bodegas
            </h5>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" onclick="recargarTabla()">
                    <i class="fas fa-sync-alt me-1"></i>Actualizar
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="exportarBodegas()">
                    <i class="fas fa-download me-1"></i>Exportar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="bodegasTable" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Ubicación</th>
                            <th>Responsable</th>
                            <th>Estado</th>
                            <th width="200">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear/editar bodega -->
<div class="modal fade" id="bodegaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bodegaModalTitle">
                    <i class="fas fa-warehouse me-2"></i>Nueva Bodega
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bodegaForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="255">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ubicacion" class="form-label">Ubicación</label>
                        <textarea class="form-control" id="ubicacion" name="ubicacion" rows="3" maxlength="500"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="responsable_id" class="form-label">Responsable</label>
                                <select class="form-select" id="responsable_id" name="responsable_id">
                                    <option value="">Seleccionar responsable...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="activa" name="activa" checked>
                                    <label class="form-check-label" for="activa">
                                        Bodega activa
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Verificar que jQuery esté cargado antes de hacer cualquier cosa
$(document).ready(function() {
    console.log('=== DIAGNÓSTICO DE BODEGAS ===');
    console.log('jQuery version:', $.fn.jquery || 'NO CARGADO');
    console.log('DataTables available:', typeof $.fn.DataTable !== 'undefined');
    console.log('SweetAlert available:', typeof Swal !== 'undefined');
    console.log('Bootstrap available:', typeof window.bootstrap !== 'undefined');
    
    // Inicializar tabla con datos del servidor
    try {
        $('#bodegasTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ route("bodegas.index") }}',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                dataSrc: function(json) {
                    console.log('Datos recibidos:', json);
                    if (json.success && json.data) {
                        return json.data;
                    }
                    return [];
                }
            },
            columns: [
                { data: 'codigo' },
                { data: 'nombre' },
                { data: 'ubicacion' },
                { 
                    data: 'responsable',
                    render: function(data, type, row) {
                        return data ? data.name : 'Sin responsable';
                    }
                },
                { 
                    data: 'activa',
                    render: function(data, type, row) {
                        return data ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-danger">Inactiva</span>';
                    }
                },
                { 
                    data: 'id',
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info" onclick="verBodega(${data})" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="editarBodega(${data})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="toggleEstado(${data})" title="Cambiar Estado">
                                    <i class="fas fa-power-off"></i>
                                </button>
                                <button class="btn btn-sm btn-success" onclick="verInventario(${data})" title="Inventario">
                                    <i class="fas fa-boxes"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="eliminarBodega(${data})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros por página",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                paginate: {
                    first: "Primera",
                    last: "Última",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            }
        });
        console.log('✓ DataTable inicializado correctamente');
    } catch (error) {
        console.error('✗ Error inicializando DataTable:', error);
    }
    
    // Cargar estadísticas desde el servidor
    cargarEstadisticas();
    
    console.log('=== DIAGNÓSTICO COMPLETO ===');
});

// Funciones globales básicas
function nuevaBodega() {
    console.log('Nueva bodega');
    
    // Limpiar el formulario
    $('#bodegaForm')[0].reset();
    
    // Cambiar el título del modal
    $('#bodegaModalTitle').text('Nueva Bodega');
    
    // Configurar el formulario para creación
    $('#bodegaForm').attr('action', '{{ route("bodegas.store") }}');
    $('#bodegaForm').attr('method', 'POST');
    
    // Remover el método PUT si existe
    $('#bodegaForm input[name="_method"]').remove();
    
    // Agregar token CSRF si no existe
    if ($('#bodegaForm input[name="_token"]').length === 0) {
        $('#bodegaForm').append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
    }
    
    // Llenar el select de responsables
    $.get('{{ route("users.list") }}')
        .done(function(response) {
            console.log('Respuesta de usuarios:', response);
            const selectResponsable = $('#responsable_id');
            selectResponsable.empty();
            selectResponsable.append('<option value="">Seleccionar responsable...</option>');
            
            if (response.success && response.data) {
                response.data.forEach(function(usuario) {
                    selectResponsable.append(`<option value="${usuario.id}">${usuario.name}</option>`);
                });
            } else {
                console.log('No se encontraron usuarios o error en la respuesta');
            }
        })
        .fail(function(xhr, status, error) {
            console.log('Error al cargar usuarios:', error);
            console.log('Status:', status);
            console.log('Response:', xhr.responseText);
        });
    
    // Mostrar el modal
    $('#bodegaModal').modal('show');
}

function editarBodega(id) {
    console.log('Editar bodega ID:', id);
    
    // Realizar petición AJAX al backend
    $.get('{{ route("bodegas.edit", ":id") }}'.replace(':id', id))
        .done(function(response) {
            console.log('Respuesta del servidor:', response);
            
            if (response.success) {
                const bodega = response.data.bodega;
                const usuarios = response.data.usuarios;
                
                // Cambiar el título del modal
                $('#bodegaModalTitle').text('Editar Bodega');
                
                // Llenar los campos del modal con los datos de la bodega
                $('#nombre').val(bodega.nombre || '');
                $('#codigo').val(bodega.codigo || '');
                $('#ubicacion').val(bodega.ubicacion || '');
                
                // Para el campo estado/activa (checkbox)
                if (bodega.estado === 'activa') {
                    $('#activa').prop('checked', true);
                } else {
                    $('#activa').prop('checked', false);
                }
                
                // Llenar el select de responsables
                const selectResponsable = $('#responsable_id');
                selectResponsable.empty();
                selectResponsable.append('<option value="">Seleccionar responsable...</option>');
                
                usuarios.forEach(function(usuario) {
                    const selected = (usuario.id == bodega.responsable_id) ? 'selected' : '';
                    selectResponsable.append(`<option value="${usuario.id}" ${selected}>${usuario.name}</option>`);
                });
                
                // Configurar el formulario para actualización
                $('#bodegaForm').attr('action', '{{ route("bodegas.update", ":id") }}'.replace(':id', id));
                $('#bodegaForm').attr('method', 'POST');
                
                // Agregar el método PUT si no existe
                if ($('#bodegaForm input[name="_method"]').length === 0) {
                    $('#bodegaForm').append('<input type="hidden" name="_method" value="PUT">');
                }
                
                // Agregar token CSRF si no existe
                if ($('#bodegaForm input[name="_token"]').length === 0) {
                    $('#bodegaForm').append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
                }
                
                // Mostrar el modal
                $('#bodegaModal').modal('show');
            } else {
                alert('Error al cargar los datos de la bodega: ' + (response.message || 'Error desconocido'));
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Error en la petición AJAX:', error);
            console.error('Respuesta del servidor:', xhr.responseText);
            alert('Error al conectar con el servidor: ' + error);
        });
}

function eliminarBodega(id) {
    console.log('Eliminar bodega ID:', id);
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceder con la eliminación
                $.ajax({
                    url: '{{ route("bodegas.destroy", ":id") }}'.replace(':id', id),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('¡Eliminado!', response.message, 'success');
                            location.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al eliminar:', error);
                        Swal.fire('Error', 'No se pudo eliminar la bodega', 'error');
                    }
                });
            }
        });
    } else {
        if (confirm('¿Está seguro de que desea eliminar esta bodega?')) {
            $.ajax({
                url: '{{ route("bodegas.destroy", ":id") }}'.replace(':id', id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        alert('Bodega eliminada correctamente');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al eliminar:', error);
                    alert('No se pudo eliminar la bodega');
                }
            });
        }
    }
}

function toggleEstado(id) {
    console.log('Alternar estado bodega ID:', id);
    
    $.ajax({
        url: '{{ route("bodegas.toggle", ":id") }}'.replace(':id', id),
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Estado alternado:', response);
            
            if (response.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('¡Éxito!', response.message, 'success');
                } else {
                    alert(response.message);
                }
                location.reload();
            } else {
                alert('Error: ' + (response.message || 'Error desconocido'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al alternar estado:', error);
            alert('Error al cambiar el estado de la bodega');
        }
    });
}

function verBodega(id) {
    console.log('Ver bodega ID:', id);
    
    // Realizar petición AJAX al backend para obtener datos de la bodega
    $.get('{{ route("bodegas.show", ":id") }}'.replace(':id', id))
        .done(function(response) {
            if (response.success) {
                const bodega = response.data;
                
                let modalContent = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Código:</strong> ${bodega.codigo || 'N/A'}<br>
                            <strong>Nombre:</strong> ${bodega.nombre || 'N/A'}<br>
                            <strong>Estado:</strong> ${bodega.activa ? 'Activa' : 'Inactiva'}<br>
                        </div>
                        <div class="col-md-6">
                            <strong>Responsable:</strong> ${bodega.responsable ? bodega.responsable.name : 'Sin responsable'}<br>
                            <strong>Creado:</strong> ${bodega.created_at || 'N/A'}<br>
                            <strong>Actualizado:</strong> ${bodega.updated_at || 'N/A'}<br>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Ubicación:</strong><br>
                            ${bodega.ubicacion || 'Sin ubicación especificada'}
                        </div>
                    </div>
                `;
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Detalles de la Bodega',
                        html: modalContent,
                        icon: 'info',
                        confirmButtonText: 'Cerrar',
                        width: '600px'
                    });
                } else {
                    alert('Ver detalles de bodega - función básica sin SweetAlert');
                }
            } else {
                alert('Error al cargar los datos de la bodega');
            }
        })
        .fail(function() {
            alert('Error al conectar con el servidor');
        });
}

function verInventario(id) {
    console.log('Ver inventario:', id);
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Inventario de Bodega',
            text: 'Cargando inventario...',
            icon: 'info',
            showConfirmButton: false,
            timer: 2000
        });
    } else {
        alert(`Ver inventario de bodega ID: ${id} - función en desarrollo`);
    }
}

function recargarTabla() {
    console.log('Recargar tabla');
    
    try {
        const table = $('#bodegasTable').DataTable();
        table.ajax.reload(function(json) {
            console.log('Tabla recargada:', json);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Tabla actualizada!',
                    text: 'Los datos han sido recargados',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }, false);
        
        // Recargar estadísticas también
        cargarEstadisticas();
    } catch (error) {
        console.error('Error al recargar tabla:', error);
        alert('Error al recargar los datos');
    }
}

function cargarEstadisticas() {
    $.get('{{ route("bodegas.stats") }}')
        .done(function(response) {
            if (response.success) {
                $('#totalBodegas').text(response.data.total || 0);
                $('#bodegasActivas').text(response.data.activas || 0);
                $('#bodegasInactivas').text(response.data.inactivas || 0);
                $('#conInventario').text(response.data.con_inventario || 0);
            }
        })
        .fail(function() {
            console.log('Error al cargar estadísticas');
        });
}

function exportarBodegas() {
    console.log('Exportar bodegas');
    alert('Exportar bodegas - función en desarrollo');
}

// Manejar el envío del formulario de bodega
$(document).on('submit', '#bodegaForm', function(e) {
    e.preventDefault();
    
    // Crear un objeto normal en lugar de FormData para mejor control
    const formDataObj = {};
    
    // Recopilar todos los campos del formulario
    $(this).serializeArray().forEach(function(field) {
        formDataObj[field.name] = field.value;
    });
    
    // Manejar el checkbox activa específicamente
    const activaCheckbox = $('#activa');
    formDataObj.activa = activaCheckbox.is(':checked') ? 'true' : 'false';
    
    const action = $(this).attr('action');
    const method = $(this).attr('method');
    
    console.log('Enviando formulario a:', action);
    console.log('Método:', method);
    console.log('Datos del formulario:', formDataObj);
    
    $.ajax({
        url: action,
        method: method,
        data: formDataObj,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Respuesta exitosa:', response);
            
            if (response.success) {
                $('#bodegaModal').modal('hide');
                
                // Mostrar mensaje de éxito
                if (typeof Swal !== 'undefined') {
                    Swal.fire('¡Éxito!', response.message || 'Operación completada correctamente', 'success');
                } else {
                    alert(response.message || 'Operación completada correctamente');
                }
                
                // Recargar la página o actualizar la tabla
                location.reload();
            } else {
                alert('Error: ' + (response.message || 'Error desconocido'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en el formulario:', error);
            console.error('Respuesta del servidor:', xhr.responseText);
            
            if (xhr.status === 422) {
                // Errores de validación
                const errors = xhr.responseJSON.errors;
                for (const field in errors) {
                    const fieldElement = $(`#${field}`);
                    fieldElement.addClass('is-invalid');
                    fieldElement.siblings('.invalid-feedback').text(errors[field][0]);
                }
            } else {
                alert('Error al procesar la solicitud: ' + error);
            }
        }
    });
});

// Limpiar errores de validación cuando se cambia un campo
$(document).on('input change', '#bodegaForm input, #bodegaForm select, #bodegaForm textarea', function() {
    $(this).removeClass('is-invalid');
    $(this).siblings('.invalid-feedback').text('');
});
</script>
@endpush