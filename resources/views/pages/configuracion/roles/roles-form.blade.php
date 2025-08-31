<div class="modal fade" id="rolFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textRolCreate" style="display: none;">Agregar rol</h5>
                <h5 class="modal-title" id="textRolUpdate" style="display: none;">Editar rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="rolForm" style="margin-top: 10px;">
                    <div class="row">

                        <input type="text" class="form-control" name="id_rol_up" id="id_rol_up" style="display: none;">

                        <div class="form-group col-12 col-sm-12 col-md-12">
                            <label for="example-text-input" class="form-control-label">Nombre Rol<span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombre_rol" id="nombre_rol" required>
                        </div>

                        @foreach ($componentes as $componente)
                            @foreach ($componente->hijos as $hijo)
                                @if (count($hijo->permisos) > 0)
                                    <div class="col-12 col-sm-6 col-md-6 row">
                                        <div class="col-12" style="margin-top: 5px;">
                                            <h6>{{$componente->nombre}} > {{$hijo->nombre}}</h6>
                                            @foreach ($hijo->permisos as $permisos)
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="permiso_{{explode(' ', $permisos->name)[0]}}_{{explode(' ', $permisos->name)[1]}}" id="permiso_{{explode(' ', $permisos->name)[0]}}_{{explode(' ', $permisos->name)[1]}}" style="height: 20px;">
                                                    <label class="form-check-label" for="{{explode(' ', $permisos->name)[0]}}_{{explode(' ', $permisos->name)[1]}}">{{explode(' ', $permisos->name)[1]}}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endforeach

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveRol"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateRol"type="button" class="btn bg-gradient-success btn-sm">Actualizar</button>
                <button id="saveRolLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>