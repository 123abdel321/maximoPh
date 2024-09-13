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
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputUsuarios" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.configuracion.usuarios.usuarios-table', ['roles' => $roles])

            </div>
        </div>
    </div>

    @include('pages.configuracion.usuarios.usuarios-sync')
    @include('pages.configuracion.usuarios.usuarios-form', ['roles' => $roles, 'usuario_nit' => $usuario_nit])
    
</div>

<script>
    var usuario_nit = JSON.parse('<?php echo $usuario_nit; ?>');
    var correoUsuarios = '<?php echo auth()->user()->can('usuarios correo'); ?>';
    var editarUsuarios = '<?php echo auth()->user()->can('usuarios update'); ?>';
    var eliminarUsuarios = '<?php echo auth()->user()->can('usuarios delete'); ?>';
</script>