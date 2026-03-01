<div class="modal fade" id="notificacionesEmailRedactarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <div class="modal-content" style="margin-top: 10px;">
            <div class="modal-header">
                <h5 class="modal-title" id="textNotificacionesRedactar" style="display: block;">Redactar correo</h5>
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

                <div id="input_asunto_email" class="form-group col-md-6">
                    <label for="asunto_email">Asunto del correo</label>
                    <input type="text" class="form-control form-control-sm" name="asunto_email" id="asunto_email" >
                </div>

                <div id="input_correos_adicionales_email" class="form-group col-md-6">
                    <label for="correos_adicionales_email">Correos adicionales, separados por coma</label>
                    <input type="text" class="form-control form-control-sm" name="correos_adicionales_email" id="correos_adicionales_email" >
                </div>

                <div id="input_whatsapp_adicionales_email" class="form-group col-md-6">
                    <label for="whatsapp_adicionales_email">Whatsapp adicionales, separados por coma</label>
                    <input type="text" class="form-control form-control-sm" name="whatsapp_adicionales_email" id="whatsapp_adicionales_email" >
                </div>

                <!-- EDITOR DE TEXTO -->
                <div id="input_mensaje_email" class="col-12 mb-3">
                    <label class="editor-correo">Redactar mensaje:</label>
                    <div id="editor-correo" style="height: 200px;"></div>
                </div>

                <div id="input_mensaje_whatsapp" class="col-12 mb-3">
                    <label class="whatsapp_mensaje">Redactar mensaje:</label>
                    <textarea class="form-control" id="whatsapp_mensaje" rows="3"></textarea>
                </div>                

                <div class="container">
                    <label for="email-files-input" class="form-control-label">Documentos</label>
                    <input type="file" class="filepond" id="email-files" name="documentos[]" multiple>
                </div>

            </form>
            
            <div class="modal-footer">
                <span href="javascript:void(0)" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <button id="sendEmailRedactado" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Enviar correos</button>
                <button id="sendWhatsappRedactado" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Enviar whatsapp</button>
                <button id="sendEmailRedactadoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>