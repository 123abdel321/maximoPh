<div class="row" style="padding: 4px;">
    <div class="form-group  col-12 col-sm-6 col-md-6">
        <label>Cédula / Nit</label>
        <select name="id_nit_usuario_filter" id="id_nit_usuario_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
        </select>
    </div>

    <div class="form-group col-12 col-sm-6 col-md-6">
        <label>Rol usuario</label>
        <select name="id_rol_usuario_filter" id="id_rol_usuario_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
            <option value='' >TODOS</option>
            @foreach ($roles as $rol)
                <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
            @endforeach
        </select>
    </div>
</div>

<table id="usuariosTable" class="table table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Usuario</th>
            <th>Rol</th>
            <th>Cedula / Nit</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Telefono</th>
            <th>Dirección</th>
            <th>Creación registro</th>
            <th>Ultima actualización</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>