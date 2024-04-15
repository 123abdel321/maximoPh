<div class="modal fade" id="porteriaEventoShowFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" >Evento de porteria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">

                <form id="porteriaEventoShowForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>

                    <input type="text" class="form-control" name="id_evento_porteria_up" id="id_evento_porteria_up" style="display: none;">

                    <div id="div-porteria-persona" class="col-12" style="text-align-last: center; margin-bottom: 0.4rem;">
                        <img id="persona_evento_imagen" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/maximo/empresas/3/imagen/porteria/YWGival1Kputs4KYNErEzLkYygG5Gaob4MiLSU5M.jpg" class="img-fluid border border-2 border-white" style="height: 60px; width: 60px; border-radius: 50%; object-fit: cover; object-position: top;">
                        <p style="font-size: 14px; margin-bottom: 0px; place-self: center;">
                            <b id="persona_evento_nombre"></b>
                        </p>
                    </div>

                    <div id="div-porteria-inmueble" class="col-12" style="display: flex; justify-content: center;">
                        <p style="font-size: 14px; margin-bottom: 0.4rem;"><b>INMUEBLE:&nbsp;</b></p>
                        <p id="inmueble_evento_nombre" style="font-size: 14px; margin-bottom: 0.4rem;">A1 - 117</p>
                    </div>
                    
                    <div class="col-12" style="display: flex; justify-content: center;">
                        <p style="font-size: 14px;"><b id="fecha_evento_text">FECHA INGRESO:</b>&nbsp;</p>
                        <p id="fecha_evento_valor" style="font-size: 14px;">2024-04-15 10:14:00</p>
                    </div>
                    
                    <div id="div-porteria-imagen" class="justify-content-center col-12 col-sm-12 col-md-12" style="margin-bottom: 15px;">
                        <div style="text-align: -webkit-center; height: 150px;">
                            <img id="porteria_evento_imagen" src="/img/no-photo.jpg" class="img-fluid border border-2 border-white" style="height: inherit; border-radius: 5%;">
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-12 col-md-12" >
                        <label for="observacion_evento_valor-input" class="form-control-label">Observación</label>
                        <textarea class="form-control form-control-sm" id="observacion_evento_valor" name="observacion_porteria_evento" rows="2"></textarea>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <button id="updatePorteriaEvento"type="button" class="btn bg-gradient-success btn-sm">Actualizar</button>
                <button id="updatePorteriaEventoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>