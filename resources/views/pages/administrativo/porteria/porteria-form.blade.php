<div class="modal fade" id="porteriaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <form id="form-porteria" class="modal-content" style="margin-top: 10px;" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="textPorteriaCreate">Crear nuevo Registro</h5>
                <h5 class="modal-title" id="textPorteriaUpdate">Editar Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            
            <div class="row modal-body">

                {{ csrf_field() }}
                <input type="text" class="form-control" name="id_porteria_up" id="id_porteria_up" style="display: none;">

                <div class="justify-content-center col-12 col-sm-6 col-md-6">
                    <div style="text-align: -webkit-center; height: 90px;">
                        <img id="default_avatar_porteria" onclick="document.getElementById('imagen_porteria').click();" src="/img/add-imagen.png" class="img-fluid border border-2 border-white" style="height: 80px; cursor: pointer; border-radius: 5%;">
                        <img id="new_avatar_porteria" onclick="document.getElementById('imagen_porteria').click();" src="" class="img-fluid border border-2 border-white" style="height: 80px; cursor: pointer; border-radius: 5%;">
                    </div>
                </div>

                <input type="file" name="imagen_porteria" id="imagen_porteria" onchange="readURLPorteria(this);" style="display: none" />

                @if (auth()->user()->can('porteria eventos'))
                    <div class="form-group  col-12 col-sm-6 col-md-6">
                        <label>Cédula / Nit<span style="color: red">*</span></label>
                        <select name="id_nit_porteria" id="id_nit_porteria" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            La cédula / nit es requerida
                        </div>
                    </div>
                @endif

                <div class="form-group col-12 col-sm-6 col-md-6" style="align-self: center;">
                    <label for="exampleFormControlSelect1">Categorias<span style="color: red">*</span></label>
                    <select class="form-control form-control-sm" id="tipo_porteria_create" name="tipo_porteria_create">
                        @if (auth()->user()->can('porteria eventos'))
                            <option value="0">PROPIETARIO</option>
                        @endif
                        <!-- <option value="4">VISITANTE</option> -->
                        <!-- <option value="6">DOMICILIO</option> -->
                        <!-- <option value="5">PAQUETE</option> -->
                        <option value="3">VEHICULO</option>
                        <option value="1">INQUILINO</option>
                        <option value="2">MASCOTA</option>
                    </select>
                </div>

                <div id="input_documento_persona_porteria" style="display: block;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Documento</label>
                    <input type="text" class="form-control form-control-sm" name="documento_persona_porteria" id="documento_persona_porteria" onfocus="this.select();">
                </div>

                <div id="input_nombre_persona_porteria" style="display: block;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Nombre</label>
                    <input type="text" class="form-control form-control-sm" name="nombre_persona_porteria" id="nombre_persona_porteria" onfocus="this.select();">
                </div>

                <div id="input_genero_porteria" style="display: none;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="genero_porteria">Genero</label>
                    <select class="form-control form-control-sm" name="genero_porteria" id="genero_porteria">
                        <option value="" >NINGUNO</option>
                        <option value="0">FEMENINO</option>
                        <option value="1">MASCULINO</option>
                    </select>
                </div>

                <div id="input_fecha_inicio_porteria" style="display: none;" class="form-group col-12 col-sm-6 col-md-6" >
                    <label for="fecha_nacimiento_porteria" class="form-control-label">Fecha nacimiento</label>
                    <input type="date" class="form-control form-control-sm" name="fecha_nacimiento_porteria" id="fecha_nacimiento_porteria">
                </div>

                <div id="input_telefono_porteria" style="display: none;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="telefono_porteria" class="form-control-label">Telefono</label>
                    <input type="text" class="form-control form-control-sm" name="telefono_porteria" id="telefono_porteria" onfocus="this.select();">
                </div>

                <div id="input_email_porteria" style="display: none;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="email_porteria" class="form-control-label">Email</label>
                    <input type="text" class="form-control form-control-sm" name="email_porteria" id="email_porteria" onfocus="this.select();">
                </div>

                <div id="input_tipo_vehiculo_porteria" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="tipo_vehiculo_porteria">Tipo vehiculo<span style="color: red">*</span></label>
                    <select class="form-control form-control-sm" name="tipo_vehiculo_porteria" id="tipo_vehiculo_porteria">
                        <option value="" >NINGUNO</option>
                        <option value="0">CARRO</option>
                        <option value="1">MOTO</option>
                        <option value="2">MOTO ELECTRICA</option>
                        <option value="3">BICICLETA ELECTRICA</option>
                        <option value="4">OTROS</option>
                    </select>
                </div>

                <div id="input_tipo_mascota_porteria" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="tipo_mascota_porteria">Tipo mascota<span style="color: red">*</span></label>
                    <select class="form-control form-control-sm" name="tipo_mascota_porteria" id="tipo_mascota_porteria">
                        <option value="0">CANINO</option>
                        <option value="1">FELINO</option>
                        <option value="2">OTROS</option>
                    </select>
                </div>

                <div id="input_placa_persona_porteria" style="display: none;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="placa_persona_porteria" class="form-control-label">Placa</label>
                    <input type="text" class="form-control form-control-sm" name="placa_persona_porteria" id="placa_persona_porteria" onfocus="this.select();">
                </div>

                <div id="input_observacion_persona_porteria" style="display: block;" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="observacion_persona_porteria" class="form-control-label">Observación</label>
                    <input type="text" class="form-control form-control-sm" name="observacion_persona_porteria" id="observacion_persona_porteria" onfocus="this.select();">
                </div>

                <div id="input_dias_porteria" class="form-group col-12 col-sm-12 col-md-12 row" style="place-content: center;">
                    <label for="diaPorteria0">Días </label><br/>
                    <!-- <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaPorteria0" name="diaPorteria0" checked>
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria0">Hoy</label>
                    </div> -->
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaPorteria1" name="diaPorteria1">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria1">Lunes</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaPorteria2" name="diaPorteria2">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria2">Martes</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaPorteria3" name="diaPorteria3">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria3">Miercoles</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaPorteria4" name="diaPorteria4">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria4">Jueves</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaPorteria5" name="diaPorteria5">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria5">Viernes</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaPorteria6" name="diaPorteria6">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria6">Sabado</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaPorteria7" name="diaPorteria7">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria7">Domingo</label>
                    </div>
                </div>

            </div>
            
            <div class="modal-footer">
                <span href="javascript:void(0)" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <button id="savePorteria" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="savePorteriaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </form>
    </div>
</div>