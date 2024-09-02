<style>

</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4" style="content-visibility: auto; overflow: auto; background-color: transparent;">
            <form id="entornoForm" class="card-body row">
                

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Variables de entorno</button>
                    </li>
                    <!-- <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Profile</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Contact</button>
                    </li> -->
                </ul>
                <div class="tab-content" id="myTabContent" style="background-color: white;">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                        <br/>

                        <div class="row" style="padding: 5px;">

                            <div class="form-group col-12 col-sm-4 col-md-3" >
                                <label for="example-text-input" class="form-control-label">Área total M2</label>
                                <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="area_total_m2" id="area_total_m2">
                            </div>

                            <div class="form-group col-12 col-sm-4 col-md-3" >
                                <label for="example-text-input" class="form-control-label">Numero total unidades</label>
                                <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="numero_total_unidades" id="numero_total_unidades">
                            </div>

                            <div class="form-group col-12 col-sm-4 col-md-3" >
                                <label for="example-text-input" class="form-control-label">Valor total presupuesto</label>
                                <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="valor_total_presupuesto_year_actual" id="valor_total_presupuesto_year_actual">
                            </div>

                            <div class="form-group col-12 col-sm-4 col-md-3" >
                                <label for="example-text-input" class="form-control-label">Período facturación</label>
                                <input type="date" class="form-control form-control-sm" name="periodo_facturacion" id="periodo_facturacion">
                            </div>

                            <div class="form-group col-12 col-sm-4 col-md-3" >
                                <label for="example-text-input" class="form-control-label">Porcentaje intereses mora</label>
                                <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="porcentaje_intereses_mora" id="porcentaje_intereses_mora">
                            </div>

                            <div class="form-group col-12 col-sm-4 col-md-3" >
                                <label for="example-text-input" class="form-control-label">Redondeo intereses</label>
                                <input type="number" class="form-control form-control-sm" name="redondeo_intereses" id="redondeo_intereses">
                            </div>

                            <div class="form-group col-12 col-sm-4 col-md-3">
                                <label for="exampleFormControlSelect1">Doc. Ref. agrupado por</label>
                                <select class="form-control form-control-sm" name="documento_referencia_agrupado" id="documento_referencia_agrupado">
                                    <option value="0">AÑO-MES_CANTIDAD INMUEBLES</option>
                                    <option value="1">NOMBRE INMUEBLE-NOMBRE ZONA</option>
                                </select>
                            </div>

                            <div class="form-group col-12 col-sm-4 col-md-3" >
                                <label for="example-text-input" class="form-control-label">Factura text 1</label>
                                <input type="text" class="form-control form-control-sm" name="factura_texto1" id="factura_texto1">
                            </div>

                            <div class="form-group col-12 col-sm-4 col-md-3" >
                                <label for="example-text-input" class="form-control-label">Factura text 2</label>
                                <input type="text" class="form-control form-control-sm" name="factura_texto2" id="factura_texto2">
                            </div>

                            <div class="form-group col-12 col-sm-4 col-md-3" >
                                <label for="example-text-input" class="form-control-label">Días pronto pago</label>
                                <input type="number" class="form-control form-control-sm" name="dias_pronto_pago" id="dias_pronto_pago">
                            </div>

                            <div class="form-check form-switch col-12 col-sm-4 col-md-3">
                                <input class="form-check-input" type="checkbox" name="descuento_pago_parcial" id="descuento_pago_parcial" style="height: 20px;">
                                <label class="form-check-label" for="descuento_pago_parcial">
                                    Descuento pago parcial
                                </label>
                            </div>

                            <!-- <div class="form-group col-12 col-sm-4 col-md-3" >
                                <label for="example-text-input" class="form-control-label">Tasa interes pronto pago</label>
                                <input type="number" class="form-control form-control-sm" name="tasa_pronto_pago" id="tasa_pronto_pago">
                            </div>

                            <div class="form-group col-12 col-sm-4 col-md-3">
                                <label for="exampleFormControlSelect1">Cuenta pronto pago</label>
                                <select name="id_cuenta_pronto_pago" id="id_cuenta_pronto_pago" class="form-control form-control-sm">
                                </select>
                            </div> -->

                            <div class="form-check form-switch col-12 col-sm-4 col-md-3">
                                <input class="form-check-input" type="checkbox" name="validacion_estricta" id="validacion_estricta" style="height: 20px;">
                                <label class="form-check-label" for="validacion_estricta">
                                    Validar Facturación estricta
                                </label>
                            </div>

                            <div class="form-check form-switch col-12 col-sm-4 col-md-3">
                                <input class="form-check-input" type="checkbox" name="editar_valor_admon_inmueble" id="editar_valor_admon_inmueble" style="height: 20px;">
                                <label class="form-check-label" for="editar_valor_admon_inmueble">
                                    Editar valor inmuebles
                                </label>
                            </div>

                            <div class="form-check form-switch col-12 col-sm-4 col-md-3">
                                <input class="form-check-input" type="checkbox" name="editar_coheficiente_admon_inmueble" id="editar_coheficiente_admon_inmueble" style="height: 20px;">
                                <label class="form-check-label" for="editar_coheficiente_admon_inmueble">
                                    Editar coheficiente inmuebles
                                </label>
                            </div>

                            <div class="form-check form-switch col-12 col-sm-4 col-md-3">
                                <input class="form-check-input" type="checkbox" name="causacion_mensual_rapida" id="causacion_mensual_rapida" style="height: 20px;">
                                <label class="form-check-label" for="causacion_mensual_rapida">
                                    Causación mensual rapida
                                </label>
                            </div>

                            <div class="form-check form-switch col-12 col-sm-4 col-md-3">
                                <input class="form-check-input" type="checkbox" name="presupuesto_mensual" id="presupuesto_mensual" style="height: 20px;">
                                <label class="form-check-label" for="presupuesto_mensual">
                                    Presupuesto mensual
                                </label>
                            </div>                            

                        </div>

                        <br/>

                    </div>
                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">...</div>
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">...</div>
                </div>
                <div style="background-color: white;">
                    <button type="button" class="btn btn-primary btn-sm" id="updateEntorno">Actualizar datos</button>
                    <button id="updateEntornoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                        Cargando
                        <i class="fas fa-spinner fa-spin"></i>
                    </button>
                </div>
                <!--  -->
            </form>
        </div>

    </div>
</div>

<script>
    var variablesEntorno = JSON.parse('<?php echo $variables_entorno; ?>');
</script>