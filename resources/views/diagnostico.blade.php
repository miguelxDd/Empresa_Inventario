<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - Sistema de Inventario</title>
    <!-- Bootstrap 5 CSS local fallback -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" 
          onerror="this.onerror=null; this.href='/css/bootstrap.min.css';">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"
          onerror="this.onerror=null; this.href='/css/fontawesome.min.css';">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-tools me-2"></i>Diagnóstico del Sistema
                </h1>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Estado de Dependencias</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Framework</h5>
                                <ul id="framework-status" class="list-group">
                                    <li class="list-group-item">Laravel: <span class="text-success">✓ Funcionando</span></li>
                                    <li class="list-group-item">PHP: <span class="text-success">✓ {{ phpversion() }}</span></li>
                                    <li class="list-group-item">Base de datos: <span id="db-status">Verificando...</span></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Dependencias Frontend</h5>
                                <ul id="frontend-status" class="list-group">
                                    <li class="list-group-item">Bootstrap: <span id="bootstrap-status">Verificando...</span></li>
                                    <li class="list-group-item">jQuery: <span id="jquery-status">Verificando...</span></li>
                                    <li class="list-group-item">DataTables: <span id="datatables-status">Verificando...</span></li>
                                    <li class="list-group-item">SweetAlert2: <span id="sweetalert-status">Verificando...</span></li>
                                </ul>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Rutas del Sistema</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <a href="{{ route('bodegas.index') }}" class="btn btn-primary w-100 mb-2">
                                            <i class="fas fa-warehouse me-2"></i>Bodegas
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="{{ route('productos.index') }}" class="btn btn-secondary w-100 mb-2">
                                            <i class="fas fa-box me-2"></i>Productos
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="{{ route('movimientos.index') }}" class="btn btn-info w-100 mb-2">
                                            <i class="fas fa-exchange-alt me-2"></i>Movimientos
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Pruebas de Funcionalidad</h5>
                                <button class="btn btn-success me-2" onclick="testAjax()">
                                    <i class="fas fa-wifi me-1"></i>Probar AJAX
                                </button>
                                <button class="btn btn-warning me-2" onclick="testModal()">
                                    <i class="fas fa-window-maximize me-1"></i>Probar Modal
                                </button>
                                <button class="btn btn-danger me-2" onclick="testAlert()">
                                    <i class="fas fa-bell me-1"></i>Probar Alertas
                                </button>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Log de Consola</h5>
                                <div id="console-log" class="border p-3 bg-light" style="height: 200px; overflow-y: scroll; font-family: monospace; font-size: 12px;">
                                    Iniciando diagnóstico...<br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de prueba -->
    <div class="modal fade" id="testModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modal de Prueba</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Este es un modal de prueba para verificar que Bootstrap está funcionando correctamente.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery con fallback -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" 
            onerror="document.write('<script src=\'/js/jquery.min.js\'><\/script>')"></script>
    <!-- Bootstrap JS con fallback -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
            onerror="document.write('<script src=\'/js/bootstrap.bundle.min.js\'><\/script>')"></script>
    <!-- DataTables con fallback -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"
            onerror="document.write('<script src=\'/js/datatables.min.js\'><\/script>')"></script>
    <!-- SweetAlert2 con fallback -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"
            onerror="document.write('<script src=\'/js/sweetalert2.min.js\'><\/script>')"></script>

    <script>
        function log(message) {
            const logDiv = document.getElementById('console-log');
            logDiv.innerHTML += new Date().toLocaleTimeString() + ' - ' + message + '<br>';
            logDiv.scrollTop = logDiv.scrollHeight;
        }

        function updateStatus(elementId, status, isSuccess = true) {
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = isSuccess ? 
                    '<span class="text-success">✓ ' + status + '</span>' :
                    '<span class="text-danger">✗ ' + status + '</span>';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            log('DOM cargado');
            
            // Verificar Bootstrap
            if (typeof window.bootstrap !== 'undefined') {
                updateStatus('bootstrap-status', 'Cargado correctamente');
                log('Bootstrap: OK');
            } else {
                updateStatus('bootstrap-status', 'No disponible', false);
                log('Bootstrap: ERROR');
            }

            // Verificar jQuery cuando esté disponible
            $(document).ready(function() {
                log('jQuery listo: ' + $.fn.jquery);
                updateStatus('jquery-status', 'v' + $.fn.jquery);

                // Verificar DataTables
                if (typeof $.fn.DataTable !== 'undefined') {
                    updateStatus('datatables-status', 'Disponible');
                    log('DataTables: OK');
                } else {
                    updateStatus('datatables-status', 'No disponible', false);
                    log('DataTables: ERROR');
                }

                // Verificar SweetAlert2
                if (typeof Swal !== 'undefined') {
                    updateStatus('sweetalert-status', 'Disponible');
                    log('SweetAlert2: OK');
                } else {
                    updateStatus('sweetalert-status', 'No disponible', false);
                    log('SweetAlert2: ERROR');
                }

                // Probar conexión a base de datos
                $.get('{{ route("bodegas.statistics") }}')
                    .done(function(response) {
                        updateStatus('db-status', 'Conectada correctamente');
                        log('Base de datos: OK - ' + JSON.stringify(response.data));
                    })
                    .fail(function(xhr) {
                        updateStatus('db-status', 'Error de conexión', false);
                        log('Base de datos: ERROR - ' + xhr.status + ' ' + xhr.statusText);
                    });

                log('Diagnóstico completado');
            });
        });

        function testAjax() {
            log('Probando AJAX...');
            $.get('{{ route("bodegas.statistics") }}')
                .done(function(response) {
                    alert('AJAX funciona correctamente: ' + JSON.stringify(response.data));
                    log('AJAX: Éxito');
                })
                .fail(function(xhr) {
                    alert('Error en AJAX: ' + xhr.status + ' ' + xhr.statusText);
                    log('AJAX: Error - ' + xhr.status);
                });
        }

        function testModal() {
            log('Abriendo modal de prueba...');
            const modal = new bootstrap.Modal(document.getElementById('testModal'));
            modal.show();
        }

        function testAlert() {
            log('Probando SweetAlert2...');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'SweetAlert2 funciona!',
                    text: 'Las alertas están funcionando correctamente.',
                    timer: 3000
                });
            } else {
                alert('SweetAlert2 no está disponible, usando alert nativo');
            }
        }
    </script>
</body>
</html>
