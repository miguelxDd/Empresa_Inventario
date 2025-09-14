<!-- Modal para Crear/Editar Producto -->
<div class="modal fade" id="productoModal" tabindex="-1" aria-labelledby="productoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productoModalLabel">
                    <i class="fas fa-box me-2"></i>Nuevo Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="productoForm">
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-info-circle me-1"></i>Información Básica
                            </h6>
                            
                            <div class="mb-3">
                                <label for="sku" class="form-label">SKU/Código *</label>
                                <input type="text" class="form-control" id="sku" name="sku" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Clasificación -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-tags me-1"></i>Clasificación
                            </h6>
                            
                            <div class="mb-3">
                                <label for="categoria_id" class="form-label">Categoría *</label>
                                <select class="form-select" id="categoria_id" name="categoria_id" required>
                                    <option value="">Seleccionar categoría...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="unidad_id" class="form-label">Unidad de Medida *</label>
                                <select class="form-select" id="unidad_id" name="unidad_id" required>
                                    <option value="">Seleccionar unidad...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                                <label class="form-check-label" for="activo">
                                    Producto Activo
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Precios y Stock -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-dollar-sign me-1"></i>Precios y Stock
                            </h6>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="precio_compra" class="form-label">Precio Compra</label>
                                <input type="number" class="form-control" id="precio_compra" name="precio_compra" step="0.01" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="precio_venta" class="form-label">Precio Venta</label>
                                <input type="number" class="form-control" id="precio_venta" name="precio_venta" step="0.01" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                                <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" step="0.01" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="stock_maximo" class="form-label">Stock Máximo</label>
                                <input type="number" class="form-control" id="stock_maximo" name="stock_maximo" step="0.01" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cuentas Contables -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-calculator me-1"></i>Cuentas Contables
                            </h6>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cuenta_inventario_id" class="form-label">Cuenta Inventario *</label>
                                <select class="form-select" id="cuenta_inventario_id" name="cuenta_inventario_id" required>
                                    <option value="">Seleccionar cuenta...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cuenta_costo_id" class="form-label">Cuenta Costo *</label>
                                <select class="form-select" id="cuenta_costo_id" name="cuenta_costo_id" required>
                                    <option value="">Seleccionar cuenta...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cuenta_contraparte_id" class="form-label">Cuenta Contraparte *</label>
                                <select class="form-select" id="cuenta_contraparte_id" name="cuenta_contraparte_id" required>
                                    <option value="">Seleccionar cuenta...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
