<div class="modal fade" id="empresaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <!-- FORM RUT EMPRESA -->
        <form id="form-empresa-rut" class="modal-content" style="margin-top: 10px;" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="textEmpresaCreate" style="display: block;">Crear Empresa</h5>
                <h5 class="modal-title" id="textEmpresaUpdate" style="display: none;">Editar Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>

            <div class="row modal-body">
                {{ csrf_field() }}

                <div class="mb-3">
                    <label for="file_rut_empresa" class="form-label">Agregar rut</label>
                    <input class="form-control form-control-sm" type="file" name="file_rut_empresa" id="file_rut_empresa">
                </div>

            </div>

            <div class="modal-footer" style="place-content: space-between;">
                <span href="javascript:void(0)" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <span id="omitirEmpresa" href="javascript:void(0)" class="btn bg-gradient-primary btn-sm">
                    Omitir&nbsp;&nbsp;<i class="fas fa-forward"></i>
                </span>
                <span id="omitirEmpresaLoading" class="btn btn-primary btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            </div>

        </form>
        <!-- FORM INFORMACION EMPRESA -->
        <form id="form-empresa-create" class="modal-content" style="margin-top: 10px; display: none;" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="textEmpresaCreate" style="display: block;">Agregar Empresa</h5>
                <h5 class="modal-title" id="textEmpresaUpdate" style="display: none;">Editar Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            
            <div class="row modal-body">

                {{ csrf_field() }}

                <input type="text" class="form-control" name="id_empresa_up" id="id_empresa_up" style="display: none;">

                <div class="justify-content-center col-12 col-sm-6 col-md-6">
                    <div style="text-align: -webkit-center; height: 90px;">
                        <img id="default_avatar_porteria" onclick="document.getElementById('imagen_empresa_nueva').click();" src="/img/add-imagen.png" class="img-fluid border border-2 border-white" style="width: 80px; height: auto; cursor: pointer; border-radius: 5%;">
                        <img id="new_avatar_porteria" onclick="document.getElementById('imagen_empresa_nueva').click();" src="" class="img-fluid border border-2 border-white" style="width: 80px; height: auto; cursor: pointer; border-radius: 5%;">
                    </div>
                </div>

                <input type="file" name="imagen_empresa_nueva" id="imagen_empresa_nueva" onchange="readURLEmpresaNueva(this);" style="display: none" />

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Razon social</label>
                    <input type="text" class="form-control form-control-sm" name="razon_social_empresa_nueva" id="razon_social_empresa_nueva" onfocus="this.select();" required>
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Nombre completo</label>
                    <input type="text" class="form-control form-control-sm" name="nombre_completo_empresa_nueva" id="nombre_completo_empresa_nueva" onfocus="this.select();" required>
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Nit</label>
                    <input type="text" class="form-control form-control-sm" name="nit_empresa_nueva" id="nit_empresa_nueva" onfocus="this.select();" required>
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Email</label>
                    <input type="text" class="form-control form-control-sm" name="email_empresa_nueva" id="email_empresa_nueva" onfocus="this.select();" required>
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Telefono</label>
                    <input type="text" class="form-control form-control-sm" name="telefono_empresa_nueva" id="telefono_empresa_nueva" onfocus="this.select();">
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Dirección</label>
                    <input type="text" class="form-control form-control-sm" name="direccion_empresa_nueva" id="direccion_empresa_nueva" onfocus="this.select();">
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Numero de unidades</label>
                    <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="numero_unidades" id="numero_unidades" value="1" onchange="changePrecioSuscripcion()" onfocus="this.select();">
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">valor unidades</label>
                    <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="valor_unidades" id="valor_unidades" value="4,000" onchange="changePrecioSuscripcion()" onfocus="this.select();">
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Total mensualidad</label>
                    <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="total_mensualidad" id="total_mensualidad" value="4,000" disabled>
                </div>

            </div>

            <div class="modal-footer" style="place-content: space-between;">
                <span href="javascript:void(0)" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <button id="saveEmpresa" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveEmpresaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </form>
    </div>
</div>