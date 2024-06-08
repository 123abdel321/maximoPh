<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header" id="filtrosCuotasExtrasImport">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                Para importar cuotas extras & multas sigue estos pasos:
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="filtrosCuotasExtrasImport" data-bs-parent="#accordionRental" >
            <div class="accordion-body text-sm" style="padding: 0 !important;">
                <br>
                <div>
                    <p style="font-size: 14px !important; margin-bottom: 0px; color: black;" >
                        <b>1.</b> Descarga la plantilla para cargar cuotas extras & multas&nbsp;
                        <span id="descargarPlantillaCuotasExtras" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 3px; margin-bottom: 0px !important;">
                            <i class="fas fa-download" style="font-size: 17px;"></i>
                            <b style="vertical-align: text-top;">Descargar plantilla</b>
                        </span>
                    </p>
                    <p style="font-size: 14px !important; margin-bottom: 0px; color: black;" ><b>2.</b> Realiza los cambios en cada archivo y guárdalo en formato Excel (.xlsx)</p>
                    <p style="font-size: 14px !important; margin-bottom: 0px; color: black;" ><b>3.</b> Adjunta el archivo y haz click en "Cargar plantilla"</p>
                    <p style="font-size: 14px !important; margin-bottom: 0px; color: black;" ><b>4.</b> Valida los registros que seran cargados y haz click en "Cargar pagos"</p>
                </div>
                <br>
                <div class="row">
                    <form id="form-importador-cuotasExtras" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="col-6">
                            <input class="form-control form-control-sm" id="file_import_cuotas_multas" name="file_import_cuotas_multas" type="file" style="box-shadow: 0px 0px 0px rgba(50, 50, 93, 0.1), 2px 2px 2px rgb(0 0 0 / 57%); font-size: 15px; color: black; font-weight: bold;">
                        </div>
                        <br/>
                        <div class="col-12">
                            <button id="cargarPlantillaCuotasExtras" href="javascript:void(0)" class="btn btn-sm badge bg-gradient-success">
                                <i class="far fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                                <b style="vertical-align: text-top;">Cargar plantilla</b>
                            </button>
                            <button id="actualizarPlantillaCuotasExtras" href="javascript:void(0)" class="btn btn-sm badge bg-gradient-primary" style="float: right; display: none;">
                                <i class="fas fa-upload" style="font-size: 17px;"></i>&nbsp;
                                <b style="vertical-align: text-top;">Cargar cuotas extras</b>
                            </button>&nbsp;
                            <button id="cargarPlantillaCuotasExtrasLoagind" class="btn btn-sm badge bg-gradient-primary" style="display:none; float: left;" disabled>
                                <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>&nbsp;
                                <b style="vertical-align: text-top;">Cargando</b>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>