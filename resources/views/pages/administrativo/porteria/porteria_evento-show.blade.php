<div class="modal fade" id="porteriaEventoShowFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down" role="document">
        <div class="modal-content border-0 shadow">
            
            <div class="modal-header bg-dark text-white py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        <i class="fas fa-id-card text-white"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0">Detalle de Acceso</h6>
                        <small class="opacity-75">Registro de Portería</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body bg-light p-0">
                <form id="porteriaEventoShowForm" class="needs-invalidation" noinvalidate>
                    
                    <input type="text" name="id_evento_porteria_up" id="id_evento_porteria_up" style="display: none;">

                    <div class="container-fluid p-0">
                        <div class="row g-0">
                            
                            <div class="col-12 col-md-5 bg-white p-2 border-end">
                                <div class="text-center mb-4">
                                    <div class="position-relative d-inline-block shadow-sm rounded-circle p-1 bg-light border">
                                        <img id="persona_evento_imagen"
                                            srsrc="/img/add-imagen.png"
                                            class="img-fluid rounded-circle" 
                                            style="width: 140px; height: 140px; object-fit: cover;">
                                    </div>
                                    <h5 id="persona_evento_nombre" class="mt-3 fw-bold text-dark text-uppercase"></h5>

                                    <div class="bg-light rounded p-1 mb-1 border">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="text-muted small" style="font-size: 11px;">DOCUMENTO</div>
                                                <div id="persona_evento_documento" class="fw-bold text-dark"></div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted small" style="font-size: 11px;">TELÉFONO</div>
                                                <div id="persona_evento_telefono" class="fw-bold text-dark"></div>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <div class="text-muted small" style="font-size: 11px;">UBICACIÓN</div>
                                                <div id="persona_evento_ubicacion" class="fw-bold text-dark"></div>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <div class="text-muted small" style="font-size: 11px;">NIT / IDENTIFICACIÓN</div>
                                                <div id="persona_evento_nit" class="fw-bold text-dark"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-7 p-2">

                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-muted small">FECHA DE INGRESO</div>
                                        <div id="fecha_ingreso_detalle_evento" class="fw-bold text-dark"></div>
                                    </div>

                                    <div class="col-6">
                                        <div class="text-muted small">FECHA DE SALIDA</div>
                                        <div id="fecha_salida_detalle_evento" class="fw-bold text-dark"></div>
                                    </div>

                                    <div class="col-12">
                                        <div class="text-muted small" style="font-size: 11px;">OBSERVACIÓN</div>
                                        <div id="observacion_detalle_evento" class="fw-bold text-dark"></div>
                                    </div>
                                </div>
                                
                                <div class="mb-4 mt-2">
                                    <label class="form-label small fw-bold text-uppercase text-muted mb-2">Registro Fotográfico de Portería</label>
                                    <div id="div-porteria-imagen" class="ratio ratio-16x9 shadow-sm">
                                        <img
                                            id="porteria_evento_imagen"
                                            src="/img/no-photo.jpg" 
                                            class="img-fluid rounded-3 border border-2 border-white bg-secondary" 
                                            style="object-fit: cover;"
                                        >
                                    </div>
                                </div>

                                
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>