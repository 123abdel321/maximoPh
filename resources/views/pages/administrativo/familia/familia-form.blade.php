<div class="modal fade" id="familiaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <form id="form-familia" class="modal-content" style="margin-top: 10px;" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="textFamiliaCreate">Crear nuevo Registro</h5>
                <h5 class="modal-title" id="textFamiliaUpdate">Editar Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            
            <div class="row modal-body">

                {{ csrf_field() }}
                <input type="text" class="form-control" name="id_familia_up" id="id_familia_up" style="display: none;">

                <input type="file" name="imagen_familia" id="imagen_familia" onchange="readURLFamilia(this);" style="display: none" />

                @if (auth()->user()->can('familia terceros'))
                    <div class="form-group  col-12 col-sm-6 col-md-6">
                        <label>Cédula / Nit<span style="color: red">*</span></label>
                        <select name="id_nit_familia" id="id_nit_familia" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            La cédula / nit es requerida
                        </div>
                    </div>

                    <div class="form-group  col-12 col-sm-6 col-md-6">
                        <label>Inmueble<span style="color: red">*</span></label>
                        <select name="id_inmueble_familia" id="id_inmueble_familia" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            El inmueble es requerido
                        </div>
                    </div>
                @endif

                <div class="form-group col-12 col-sm-6 col-md-6" style="align-self: center;">
                    <label for="exampleFormControlSelect1">Categorias<span style="color: red">*</span></label>
                    <select class="form-control form-control-sm" id="tipo_familia_create" name="tipo_familia_create">
                        <option value="1">FAMILIA</option>
                        <option value="2">MASCOTA</option>
                        <option value="3">VEHICULO</option>
                    </select>
                </div>

                <div id="input_documento_persona_familia" style="display: block;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Documento</label>
                    <input type="text" class="form-control form-control-sm" name="documento_persona_familia" id="documento_persona_familia" onfocus="this.select();">
                </div>

                <div id="input_nombre_persona_familia" style="display: block;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Nombre</label>
                    <input type="text" class="form-control form-control-sm" name="nombre_persona_familia" id="nombre_persona_familia" onfocus="this.select();">
                </div>

                <div id="input_genero_familia" style="display: none;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="genero_familia">Genero</label>
                    <select class="form-control form-control-sm" name="genero_familia" id="genero_familia">
                        <option value="" >NINGUNO</option>
                        <option value="0">FEMENINO</option>
                        <option value="1">MASCULINO</option>
                    </select>
                </div>

                <div id="input_fecha_inicio_familia" style="display: none;" class="form-group col-12 col-sm-6 col-md-6" >
                    <label for="fecha_nacimiento_familia" class="form-control-label">Fecha nacimiento</label>
                    <input type="date" class="form-control form-control-sm" name="fecha_nacimiento_familia" id="fecha_nacimiento_familia">
                </div>

                <div id="input_email_familia" style="display: none;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="email_familia" class="form-control-label">Email</label>
                    <input type="text" class="form-control form-control-sm" name="email_familia" id="email_familia" onfocus="this.select();">
                </div>

                <div id="input_tipo_vehiculo_familia" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="tipo_vehiculo_familia">Tipo vehiculo<span style="color: red">*</span></label>
                    <select class="form-control form-control-sm" name="tipo_vehiculo_familia" id="tipo_vehiculo_familia">
                        <option value="" >NINGUNO</option>
                        <option value="0">CARRO</option>
                        <option value="1">MOTO</option>
                        <option value="2">MOTO ELECTRICA</option>
                        <option value="3">BICICLETA ELECTRICA</option>
                        <option value="4">OTROS</option>
                    </select>
                </div>

                <div id="input_tipo_mascota_familia" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="tipo_mascota_familia">Tipo mascota<span style="color: red">*</span></label>
                    <select class="form-control form-control-sm" name="tipo_mascota_familia" id="tipo_mascota_familia">
                        <option value="0">CANINO</option>
                        <option value="1">FELINO</option>
                        <option value="2">OTROS</option>
                    </select>
                </div>

                <div id="input_placa_persona_familia" style="display: none;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="placa_persona_familia" class="form-control-label">Placa</label>
                    <input type="text" class="form-control form-control-sm" name="placa_persona_familia" id="placa_persona_familia" onfocus="this.select();">
                </div>

                <div id="input_observacion_persona_familia" style="display: block;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="observacion_persona_familia" class="form-control-label">Observación</label>
                    <input type="text" class="form-control form-control-sm" name="observacion_persona_familia" id="observacion_persona_familia" onfocus="this.select();">
                </div>

                <div id="input_dias_familia" class="form-group col-12 col-sm-12 col-md-12 row" style="place-content: end;">
                    <label for="diaFamilia0">Días </label><br/>

                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaFamilia1" name="diaFamilia1">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaFamilia1">Lunes</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaFamilia2" name="diaFamilia2">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaFamilia2">Martes</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaFamilia3" name="diaFamilia3">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaFamilia3">Miercoles</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaFamilia4" name="diaFamilia4">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaFamilia4">Jueves</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaFamilia5" name="diaFamilia5">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaFamilia5">Viernes</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaFamilia6" name="diaFamilia6">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaFamilia6">Sabado</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaFamilia7" name="diaFamilia7">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaFamilia7">Domingo</label>
                    </div>
                </div>

                <div class="input-field">
                    <label class="active">Imagen</label>
                    <div class="input-images-familia" style="padding-top: .5rem;"></div>
                </div>

            </div>
            
            <div class="modal-footer">
                <span href="javascript:void(0)" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <button id="saveFamilia" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveFamiliaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </form>
    </div>
</div>