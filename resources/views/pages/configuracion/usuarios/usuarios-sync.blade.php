<div class="modal fade" id="usuariosSyncFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textUsuariosCreate">Syncronizar usuarios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <div class="row" autocomplete="off" style="margin-top: 10px;">

                    <div class="form-group col-12">
                        <label for="formZonaLabel">Cédula / nit</label>
                        <select name="id_nit_sync_usuario" id="id_nit_sync_usuario" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12">
                        <label for="formZonaLabel">Zona</label>
                        <select name="id_zona_sync_usuario" id="id_zona_sync_usuario" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12">
                        <label for="formZonaLabel">Inmueble</label>
                        <select name="id_inmueble_sync_usuario" id="id_inmueble_sync_usuario" class="form-control form-control-sm">
                        </select>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveSyncUsuarios"type="button" class="btn bg-gradient-success btn-sm">Syncronizar</button>
                <button id="saveSyncUsuariosLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>