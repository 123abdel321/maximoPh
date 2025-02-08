<style>

</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4" style="content-visibility: auto; overflow: auto; background-color: transparent;">
            <form id="entornoForm" class="card-body row">
                

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">
                            <p style="margin-bottom: 0; font-weight: 600;">Variables de entorno</p>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pasarela-tab" data-bs-toggle="tab" data-bs-target="#pasarela" type="button" role="tab" aria-controls="pasarela" aria-selected="false">
                            <p style="margin-bottom: 0; font-weight: 600;">Pasarela</p>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="terminos-tab" data-bs-toggle="tab" data-bs-target="#terminos" type="button" role="tab" aria-controls="terminos" aria-selected="false">
                            <p style="margin-bottom: 0; font-weight: 600;">Terminos & condiciones</p>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pazysalvo-tab" data-bs-toggle="tab" data-bs-target="#pazysalvo" type="button" role="tab" aria-controls="pazysalvo" aria-selected="false">
                            <p style="margin-bottom: 0; font-weight: 600;">Paz y Salvo</p>
                        </button>
                    </li>
                    <!-- <li class="nav-item" role="presentation">
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
                                    <option value="2">(NOMBRE INMUEBLE)(NOMBRE ZONA)-AÑO-MES</option>
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
                            </div> -->

                            <div class="form-group col-12 col-sm-4 col-md-3">
                                <label for="exampleFormControlSelect1">Concepto pago sin identificar</label>
                                <select name="id_concepto_pago_none" id="id_concepto_pago_none" class="form-control form-control-sm">
                                </select>
                            </div>

                            <div class="form-group col-12 col-sm-4 col-md-3">
                                <label for="exampleFormControlSelect1">Nit por defecto</label>
                                <select name="id_nit_por_defecto" id="id_nit_por_defecto" class="form-control form-control-sm">
                                </select>
                            </div>

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
                                <input class="form-check-input" type="checkbox" name="recausar_meses" id="recausar_meses" style="height: 20px;">
                                <label class="form-check-label" for="recausar_meses">
                                    Recausar meses
                                </label>
                            </div>

                            <div class="form-check form-switch col-12 col-sm-4 col-md-3">
                                <input class="form-check-input" type="checkbox" name="validar_fecha_entrega_causacion" id="validar_fecha_entrega_causacion" style="height: 20px;">
                                <label class="form-check-label" for="validar_fecha_entrega_causacion">
                                    Validar fecha entrega causación mensual
                                </label>
                            </div>

                            <div class="form-check form-switch col-12 col-sm-4 col-md-3">
                                <input class="form-check-input" type="checkbox" name="detallar_facturas" id="detallar_facturas" style="height: 20px;">
                                <label class="form-check-label" for="detallar_facturas">
                                    Detallar facturas
                                </label>
                            </div>

                        </div>

                        <br/>

                    </div>
                    <div class="tab-pane fade" id="pasarela" role="tabpanel" aria-labelledby="pasarela-tab">
                        <br/>

                        <div class="row" style="padding: 5px;">

                            <div style="text-align: center;">
                                <img style="width: 180px;" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/iconos_sistema/placetopay.png">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-6" >
                                <label for="example-text-input" class="form-control-label">Url base</label>
                                <input type="text" class="form-control form-control-sm" name="placetopay_url" id="placetopay_url">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-6" >
                                <label for="example-text-input" class="form-control-label">Login key</label>
                                <input type="text" class="form-control form-control-sm" name="placetopay_login" id="placetopay_login">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-6" >
                                <label for="example-text-input" class="form-control-label">Secret Key</label>
                                <input type="text" class="form-control form-control-sm" name="placetopay_trankey" id="placetopay_trankey">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-6">
                                <label for="exampleFormControlSelect1">Forma de pago</label>
                                <select name="placetopay_forma_pago" id="placetopay_forma_pago" class="form-control form-control-sm">
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane fade" id="terminos" role="tabpanel" aria-labelledby="terminos-tab">
                        <br/>
                        <div class="row" style="padding: 5px;">

                            <div class="mb-3 col-12">
                                <label for="exampleFormControlTextarea1" class="form-label">Terminos y condiciones</label>
                                <textarea class="form-control" id="terminos_condiciones" rows="3"></textarea>
                            </div>

                            <div class="mb-3 form-check form-switch col-12">
                                <input class="form-check-input" type="checkbox" name="aceptar_terminos" id="aceptar_terminos" style="height: 20px;">
                                <label class="form-check-label" for="aceptar_terminos">
                                    Obligatorio aceptar terminos & condiciones
                                </label>
                            </div>
                            
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pazysalvo" role="tabpanel" aria-labelledby="pazysalvo-tab">
                        <br/>
                        <div class="row" style="padding: 5px;">
                            <div class="form-group col-6" >
                                <label for="example-text-input" class="form-control-label">Nombre Administrador</label>
                                <input type="text" class="form-control form-control-sm" name="nombre_administrador" id="nombre_administrador">
                            </div>

                            <div class="justify-content-center col-6">
                                <label for="example-text-input" class="form-control-label">Firma digital</label>
                                <div style="with: 190px;">
                                    <img id="preview_firma_digital_paz_salvo" onclick="document.getElementById('firma_digital_paz_salvo_nueva').click();" src="/img/add-imagen.png" class="img-fluid border border-2 border-white" style="width: 180px; height: auto; cursor: pointer; border-radius: 5%;">
                                    <img id="firma_digital_paz_salvo" onclick="document.getElementById('firma_digital_paz_salvo_nueva').click();" src="" class="img-fluid border border-2 border-white" style="width: 180px; height: auto; cursor: pointer; border-radius: 5%;">
                                    <input type="file" name="firma_digital_paz_salvo_nueva" id="firma_digital_paz_salvo_nueva" onchange="readURLFirmaDigitalNueva(this);" style="display: none" />
                                </div>
                            </div>

                        </div>
                    </div>
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