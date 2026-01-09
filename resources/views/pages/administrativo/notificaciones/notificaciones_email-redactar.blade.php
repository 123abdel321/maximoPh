<div class="modal fade" id="notificacionesEmailRedactarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <div class="modal-content" style="margin-top: 10px;">
            <div class="modal-header">
                <h5 class="modal-title" id="textPorteriaEventoCreate" style="display: block;">Redactar correo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            
            <form id="form-notificaciones-email" class="row modal-body">

                <div class="form-group col-md-6">
                    <label for="id_nit_email">Nit</label>
                    <select class="form-control form-control-sm" name="id_nit_email" id="id_nit_email">
                        <option value="">Seleccionar</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="id_zona_email">Zona</label>
                    <select class="form-control form-control-sm" name="id_zona_email" id="id_zona_email">
                        <option value="">Seleccionar</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="asunto_correo">Asunto del correo</label>
                    <input type="text" class="form-control form-control-sm" name="asunto_email" id="asunto_email" >
                </div>
                <div class="form-group col-md-6">
                    <label for="asunto_correo">Correos adicionales, separados por coma</label>
                    <input type="text" class="form-control form-control-sm" name="correos_adicionales_email" id="correos_adicionales_email" >
                </div>  
                <!-- EDITOR DE TEXTO -->
                <div class="col-12 mb-3">
                    <label class="form-label">Redactar correo:</label>
                    <div id="editor-correo" style="height: 300px;"></div>
                </div>
            </form>
            
            <div class="modal-footer">
                <span href="javascript:void(0)" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <button id="sendEmailRedactado" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="sendEmailRedactadoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>