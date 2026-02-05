<div class="modal fade" id="porteriaEventoFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-2 px-3">
                <div class="d-flex align-items-center w-100">
                    <i class="fas fa-shield-alt me-2 fs-6"></i>
                    <h6 class="modal-title mb-0 fw-bold">Control de Acceso</h6>
                    <span id="evento_tipo_texto" class="badge bg-white text-primary ms-auto" style="font-size: 10px;"></span>
                </div>
            </div>
            
            <form id="form-porteria-evento" class="modal-body p-3">
                <input type="hidden" name="id_porteria_evento" id="id_porteria_evento">
                
                <!-- Todo agrupado -->
                <div class="text-center mb-3">
                    <img id="img_porteria_evento" 
                        src="/img/add-imagen.png"
                        class="rounded-circle border border-3 border-primary mb-2"
                        style="width: 70px; height: 70px; object-fit: cover;">
                    <h6 id="evento_persona_nombre" class="fw-bold text-dark mb-2 fs-6"></h6>
                </div>
                
                <!-- Grid de información -->
                <div class="bg-light rounded p-1 mb-1 border">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-muted small" style="font-size: 11px;">DOCUMENTO</div>
                            <div id="evento_persona_documento" class="fw-bold text-dark"></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small" style="font-size: 11px;">TELÉFONO</div>
                            <div id="evento_persona_telefono" class="fw-bold text-dark"></div>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="text-muted small" style="font-size: 11px;">UBICACIÓN</div>
                            <div id="evento_persona_ubicacion" class="fw-bold text-dark"></div>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="text-muted small" style="font-size: 11px;">NIT / IDENTIFICACIÓN</div>
                            <div id="evento_persona_nit" class="fw-bold text-dark"></div>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="form-group col-8" style="align-self: center; margin-bottom: 0px !important;">
                        <label for="tipo_evento_porteria" class="small text-muted mb-1">Tipo evento</label>
                        <select class="form-control form-control-sm border-primary" id="tipo_evento_porteria" name="tipo_evento_porteria">
                            <option value="1">INGRESO</option>
                            <option value="2">SALIDA</option>
                            <option value="3">MINUTA</option>
                        </select>
                    </div>
    
                    <div class="form-group col-4" style="align-self: end; margin-bottom: 0px !important;">
                        <span id="mostrarPorteriaEvento" href="javascript:void(0)" class="btn badge bg-gradient-success btn-sm w-100" style="margin-bottom: 0px !important; height: 30px; place-content: center; font-size: 12px;">
                            Agregar
                        </span>
                        <span id="cancelarPorteriaEvento" href="javascript:void(0)" class="btn badge bg-gradient-danger btn-sm w-100" style="margin-bottom: 0px !important; height: 30px; place-content: center; font-size: 12px; display: none;">
                            Cancelar
                        </span>
                    </div>
                </div>

                <div class="row" id="divEventoPorteriaInputs" style="display: none;">

                    <div id="fechaIngresoPorteriaEventoInput" class="form-group col-12 mb-2">
                        <label for="fecha_ingreso_porteria_evento-input" class="form-control-label small text-muted mb-1">Fecha ingreso</label>
                        <input type="datetime-local" class="form-control form-control-sm border-primary" name="fecha_ingreso_porteria_evento" id="fecha_ingreso_porteria_evento">
                    </div>
    
                    <div id="fechaSalidaPorteriaEventoInput" class="form-group col-12 mb-2">
                        <label for="fecha_salida_porteria_evento-input" class="form-control-label small text-muted mb-1">Fecha salida</label>
                        <input type="datetime-local" class="form-control form-control-sm border-primary" name="fecha_salida_porteria_evento" id="fecha_salida_porteria_evento">
                    </div>
                    
                    <div class="form-group col-12 mb-2">
                        <label for="observacion_porteria_evento" class="form-control-label small text-muted mb-1">Observaciones</label>
                        <textarea class="form-control border-primary" placeholder="Observaciones" style="height: 70px;" id="observacion_porteria_evento"></textarea>
                    </div>
    
                    <div class="col-12">
                        <label for="porteria-eventos-files-input" class="form-control-label small text-muted mb-1">Registro de novedades</label>
                        <input type="file" class="form-control form-control-sm border-primary" id="porteria-eventos-files" name="images[]" multiple>
                    </div>
                </div>
            </form>
            
            <div class="modal-footer py-2 px-3">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button id="savePorteriaEvento" type="button" class="btn btn-success btn-sm">
                    Guardar
                </button>
                <button id="savePorteriaEventoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    Cargando
                </button>
            </div>
        </div>
    </div>
</div>