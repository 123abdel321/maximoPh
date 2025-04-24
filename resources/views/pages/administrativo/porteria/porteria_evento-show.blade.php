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

                    <div id="div-porteria-persona" class="justify-content-center col-12 col-sm-12 col-md-12">
                        <div style="text-align: -webkit-center; margin-bottom: 10px;">
                            <img id="persona_evento_imagen"  src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/maximo/empresas/3/imagen/porteria/YWGival1Kputs4KYNErEzLkYygG5Gaob4MiLSU5M.jpg" class="img-fluid border border-2 border-white" style="height: 130px; border-radius: 5%; width: auto; object-fit: contain;">
                        </div>
                    </div>

                    <div style="font-size: 14px; margin-bottom: 0px; text-align: center;">
                        <h3 id="persona_evento_nombre"></h3>
                    </div>

                    <div id="div-porteria-inmueble" class="col-12" style="display: flex; justify-content: center;">
                        <p style="font-size: 14px; margin-bottom: 0.4rem;"><b>INMUEBLE:&nbsp;</b></p>
                        <p id="inmueble_evento_nombre" style="font-size: 14px; margin-bottom: 0.4rem;">A1 - 117</p>
                    </div>
                    
                    <div id="div-fecha-ingreso-porteria" class="col-12" style="display: flex; justify-content: center;">
                        <p style="font-size: 14px; margin-bottom: 0.4rem;"><b >FECHA INGRESO:</b>&nbsp;</p>
                        <p id="fecha_ingreso_portafolio" style="font-size: 14px; margin-bottom: 0.4rem;"></p>
                    </div>

                    <div id="div-fecha-salida-porteria" class="col-12" style="display: flex; justify-content: center;">
                        <p style="font-size: 14px; margin-bottom: 0.4rem;"><b >FECHA SALIDA:</b>&nbsp;</p>
                        <p id="fecha_salida_portafolio" style="font-size: 14px; margin-bottom: 0.4rem;"></p>
                    </div>
                    
                    <div id="div-porteria-imagen" class="justify-content-center col-12 col-sm-12 col-md-12" style="margin-bottom: 15px;">
                        <div style="text-align: -webkit-center; height: 150px;">
                            <img id="porteria_evento_imagen" src="/img/no-photo.jpg" class="img-fluid border border-2 border-white" style="height: inherit; border-radius: 5%;">
                        </div>
                    </div>

                    <div id="div-porteria-fecha-ingreso" class="form-group col-12 col-sm-12 col-md-12" style="display: none;">
                        <label for="fecha_salida_porteria_evento-input" class="form-control-label">Fecha ingreso</label>
                        <input type="datetime-local" class="form-control form-control-sm" name="fecha_ingreso_evento_valor" id="fecha_ingreso_evento_valor">
                    </div>

                    <div id="div-porteria-fecha-salida" class="form-group col-12 col-sm-12 col-md-12" style="display: none;">
                        <label for="fecha_salida_porteria_evento-input" class="form-control-label">Fecha salida</label>
                        <input type="datetime-local" class="form-control form-control-sm" name="fecha_salida_evento_valor" id="fecha_salida_evento_valor">
                    </div>

                    <div class="form-group col-12 col-sm-12 col-md-12" >
                        <label for="observacion_evento_valor-input" class="form-control-label">Observación</label>
                        <textarea class="form-control form-control-sm" id="observacion_evento_valor" name="observacion_evento_valor" rows="2"></textarea>
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