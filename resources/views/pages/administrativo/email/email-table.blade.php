<div class="row" style="padding: 4px;">

    <div class="form-group col-12 col-sm-6 col-md-3" style="align-self: center;">
        <label for="estado_email_filter">Estado</label>
        <select class="form-control form-control-sm" id="estado_email_filter" name="estado_email_filter">
            <option value="">TODOS</option>
            <option value="en_cola">EN COLA</option>
            <option value="enviado">ENVIADO</option>
            <option value="abierto">ABIERTO</option>
            <option value="rechazado">RECHAZADO</option>
        </select>
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3">
        <label>Cédula / Nit</label>
        <select name="id_nit_email_filter" id="id_nit_email_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
        </select>
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3">
        <label for="example-text-input" class="form-control-label">Fecha desde</label>
        <input name="fecha_desde_email" id="fecha_desde_email" class="form-control form-control-sm" type="date">
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3">
        <label for="example-text-input" class="form-control-label">Fecha hasta</label>
        <input name="fecha_hasta_email" id="fecha_hasta_email" class="form-control form-control-sm" type="date">
    </div>

</div>

<table id="emailTable" class="table table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Email</th>
            <th>Nombre nit</th>
            <th>Contexto</th>
            <th>Estado</th>
            <th>Creación registro</th>
            <th>Ultima actualización</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>