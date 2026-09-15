<style>
    .accordion-usuarios > .accordion-item:first-of-type .accordion-button {
        background-color: #1c4587 !important;
        color: white;
    }

    .accordion-usuarios > .accordion-item:first-of-type .accordion-button.collapsed {
        background-color: #FFF !important;
        color: black;
    }

    .accordion-usuarios > .accordion-item:last-of-type .accordion-button {
        background-color: #1c4587 !important;
        color: white;
    }

    .accordion-usuarios > .accordion-item:last-of-type .accordion-button.collapsed {
        background-color: #FFF !important;
        color: black;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('usuarios create')
                        <button type="button" class="btn btn-primary btn-sm" id="createUsuarios">Agregar usuario</button>
                        <button type="button" class="btn btn-warning btn-sm" id="sincronizarInmueblesNitsUsuarios">Sincronizar Inmuebles nits &nbsp;<i class="fas fa-users"></i></button>
                    @endcan
                    @if ($esDios)
                        <button type="button" class="btn btn-primary btn-sm" id="asociarEmpresaUsuarios" style="display: none; margin-left: 5px;">Asociar empresas</button>
                        <button type="button" class="btn btn-dark btn-sm" id="volverUsuarios" style="display: none;"><i class="fas fa-step-backward back-icon-button"></i>&nbsp;Volver</button>
                    @endif
                    <button type="button" class="btn btn-sm badge btn-light" style="vertical-align: middle; height: 30px;" id="reloadUsuarios">
                        <i id="reloadUsuariosIconLoading" class="fa fa-refresh fa-spin" style="font-size: 16px; color: #2d3257; display: none;"></i>
                        <i id="reloadUsuariosIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                    </button>
                </div>
                <div class="col-12 col-md-6 col-sm-6 row">
                    @if($esDios)
                        <div id="div-id_empresa_filter_usuario" class="col-12 col-md-6 col-sm-6">
                            <select name="id_empresa_filter_usuario" id="id_empresa_filter_usuario" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" placeholder="Familia">
                            </select>
                        </div>
                    @endif
                    <div id="div-searchInputUsuarios" class="col-12 col-md-6 col-sm-6">
                        <input type="text" id="searchInputUsuarios" class="form-control form-control-sm search-table" placeholder="Buscar">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-sm-6" >
                    <h4 id="nombre_usuario_empresa" style="display: none; float: inline-end; color: white;"></h4>
                </div>
            </div>
        </div>
        

        <div id="tablas_usuarios_view" class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.configuracion.usuarios.usuarios-table', ['roles' => $roles])

            </div>
        </div>

        <div id="tablas_usuarios_empresas_view" class="card mb-4" style="content-visibility: auto; overflow: auto; display: none;">
            <div class="card-body">

                @include('pages.configuracion.usuarios.usuarios-empresa-table')

            </div>
        </div>
    </div>

    @include('pages.configuracion.usuarios.usuarios-sync')
    @include('pages.configuracion.usuarios.usuarios-empresa-form')
    @include('pages.configuracion.usuarios.usuarios-form', ['roles' => $roles, 'usuario_nit' => $usuario_nit])
    
</div>

<script>
    
    var correoUsuarios = @json(auth()->user()->can('usuarios correo'));
    var editarUsuarios = @json(auth()->user()->can('usuarios update'));
    var eliminarUsuarios = @json(auth()->user()->can('usuarios delete'));
    
    var esDios = @json($esDios);
    var usuario_nit = @json($usuario_nit);
</script>