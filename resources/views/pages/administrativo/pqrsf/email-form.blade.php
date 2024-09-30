<div class="modal fade" id="emailFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        
        <div class="modal-content" style="margin-top: 10px;">
            <div class="modal-header">
                <h5 class="modal-title" id="textEmailCreate" style="display: block;">Agregar Email</h5>
                <h5 class="modal-title" id="textEmailUpdate" style="display: none;">Editar Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            
            <div class="row modal-body">
                <form id="form-email" style="margin-top: 10px;" class="row">

                    <div class="form-group  col-12 col-sm-6 col-md-6">
                        <label>Cédula / Nit</label>
                        <select name="id_nit_email_filter" id="id_nit_email_filter" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="formZonaLabel">Zona</label>
                        <select name="id_zona_email_filter" id="id_zona_email_filter" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="col-12" id="editor">
                    </div>
                </form>

            </div>

            <div class="modal-footer">
                <span href="javascript:void(0)" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <button id="sendEmail" type="button" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Enviar</button>
                <button id="sendEmailLoading" type="button" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Enviando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
            
        </div>
    </div>
</div>