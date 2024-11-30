let searchTimeoutFamilia;
var $comboInmuebleEventosFilter = null;
var $comboNitFamiliaFilter = null;
var $comboFamiliaEventos = null;
var $comboInmuebleFamilia = null;
var searchValueFamilia = null;
var $comboNitFamilia = null;
var familia_table = null;
var diaFamilia = [
    "diaFamilia0",
    "diaFamilia1",
    "diaFamilia2",
    "diaFamilia3",
    "diaFamilia4",
    "diaFamilia5",
    "diaFamilia6",
    "diaFamilia7"
];
var semanaFamilia = [
    'none',
    'lun',
    'mar',
    'mie',
    'jue',
    'vie',
    'sab',
    'dom'
];

function familiaInit() {

    familia_table = $('#familiaTable').DataTable({
        pageLength: 15,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'familia',
            data: function ( d ) {
                d.tipo = $("#tipo_familia_filter").val(),
                d.id_nit = $("#id_nit_familia_filter").val(),
                d.search = $("#searchInputFamilia").val()
            }
        },
        'rowCallback': function(row, data, index){
            if (data.eventos.length) {
                $('td', row).css('background-color', '#cfebcf');
                return;
            }
        },
        columns: [
            {"data": function (row, type, set){
                var urlImg = `logos_empresas/no-photo.jpg`;
                var nameImg = 'none-img'
                if (row.archivos.length) {
                    urlImg = row.archivos[0].url_archivo;
                    nameImg = row.archivos[0].created_at;
                }
                
                return `<img
                    id="eventofamiliaimagen_${row.id}"
                    class="detalle-imagen-familia"
                    style="height: 40px; border-radius: 10%; cursor: pointer;"
                    href="javascript:void(0)"
                    src="${bucketUrl}${urlImg}"
                    alt="${nameImg}"
                />`;

            }, className: 'dt-body-center'},
            {"data":'nombre'},
            {"data":'documento'},
            {"data": function (row, type, set){
                if (row.tipo_porteria == 1) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #4048e4;">Familia</span>';
                }
                if (row.tipo_porteria == 2) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #4cd361;">Mascota</span>';
                }
                if (row.tipo_porteria == 3) {
                    var texto = 'CARRO';
                    if (row.tipo_vehiculo == 1) texto = 'MOTO';
                    if (row.tipo_vehiculo == 2) texto = 'MOTO ELECTRICA';
                    if (row.tipo_vehiculo == 2) texto = 'BICICLETA ELECTRICA';
                    if (row.tipo_vehiculo == 4) texto = 'OTROS';
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #c0bb12;">'+texto+'</span>';
                }
                return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #82198c; color: white;">Propietario</span>';
            }},
            {"data":'placa'},
            {"data": function (row, type, set){  
                const porteria = row;
                
                if (porteria.tipo_porteria == 1) {
                    if (porteria.dias) {
                        var dayNow = (dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));
                        var numeroDia = new Date(dayNow).getDay() + 1;
                        var diasText = '';
                        var dias = porteria.dias.split(',');

                        dias.forEach(dia => {
                            if (diasText) {
                                if (numeroDia == dia) diasText+=', <b style="color: #59bded;">'+semanaFamilia[dia]+'</b>';
                                else diasText+=', '+semanaFamilia[dia];
                                
                            } else { 
                                if (numeroDia == dia) diasText+= '<b style="color: #59bded;">'+semanaFamilia[dia]+'</b>';
                                else diasText+= semanaFamilia[dia];
                            }
                        });
                        return `<p style="font-size: 11px; text-align: -webkit-center; margin-bottom: 0px; margin-top: 5px;">${diasText}</p>`
                    }
                    if (porteria.hoy) {
                        return `<p style="font-size: 11px; text-align: -webkit-center; margin-bottom: 0px; margin-top: 5px;">${porteria.hoy}</p>`;
                    }
                }
                return ``;
            }},
            {"data": function (row, type, set){  
                const porteria = row;
                if (porteria.tipo_porteria == 3) {
                    return `<span class="badge badge-sm bg-gradient-success">AUTORIZADO</span>`;
                }
                var dayNow = (dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));
                    
                var numeroDia = new Date(dayNow).getDay() + 1;
                var hoyDia = new Date(porteria.hoy).getDay();
                
                if (porteria.hoy == dayNow) {
                    return `<span class="badge badge-sm bg-gradient-success">AUTORIZADO</span>`;
                }
                
                if (porteria.dias) {
                    var diasArray = porteria.dias.split(",");
                    if (diasArray.includes((numeroDia)+"")) {
                        return `<span class="badge badge-sm bg-gradient-success">AUTORIZADO</span>`;
                    }
                }
                
                return `<span class="badge badge-sm bg-gradient-danger">NO AUTORIZADO</span>`;
            }},
            {"data":'observacion'},
            {"data": function (row, type, set){  
                if (row.propietario) {
                    return row.propietario.nombre_completo;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.propietario) {
                    return row.propietario.apartamentos;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
                if(!row.created_by && !row.fecha_creacion) return '';
                if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                return html;
            }},
            {"data": function (row, type, set){
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                return html;
            }},
            {
                "data": function (row, type, set){
                    var html = '';
                    if (updateFamilia && !row.eventos.length) html+= '<span id="editfamilia_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-warning edit-familia" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (deleteFamilia && !row.eventos.length) html+= '<span id="deletefamilia_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-familia" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if (familia_table) {
        //EDITAR FAMILIA
        familia_table.on('click', '.edit-familia', function() {
            clearFormFamilia();

            $("#textFamiliaCreate").hide();
            $("#textFamiliaUpdate").show();
            $("#saveFamiliaLoading").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, familia_table);

            diaFamilia.forEach((dia, index) => {
                $('#'+dia).prop('checked', false);
            });

            if(data.propietario) {
                var dataPropietario = {
                    id: data.propietario.id,
                    text: data.propietario.nombre_completo
                };
                var newOption = new Option(dataPropietario.text, dataPropietario.id, false, false);
                $comboNitFamilia.append(newOption).trigger('change');
                $comboNitFamilia.val(dataPropietario.id).trigger('change');
            }

            if (data.inmueble) {
                var dataInmueble = {
                    id: data.inmueble.id,
                    text: data.inmueble.nombre
                };
                var newOption = new Option(dataInmueble.text, dataInmueble.id, false, false);
                $comboInmuebleFamilia.append(newOption).trigger('change');
                $comboInmuebleFamilia.val(dataInmueble.id).trigger('change');
            }

            var dayNow = (dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));
            var numeroDia = new Date(dayNow).getDay();
            if (data.hoy && numeroDia == new Date(data.hoy).getDay()) {
                $('#diaFamilia0').prop('checked', true);
            }

            hideInputFamilia();
            changeTipoFamilia(data.tipo_porteria);

            var tipoVehiculo = data.tipo_vehiculo;
            if (!tipoVehiculo && tipoVehiculo!=0) $("#input_placa_persona_familia").hide();
            else $("#input_placa_persona_familia").show();
            $("#id_familia_up").val(id);

            $("#email_familia").val(data.email);
            $("#genero_familia").val(data.genero).change();
            // $("#telefono_familia").val(data.telefono);
            $("#fecha_nacimiento_familia").val(data.fecha_nacimiento);
            $("#tipo_familia_create").val(data.tipo_porteria);
            $("#nombre_persona_familia").val(data.nombre);
            $("#documento_persona_familia").val(data.documento);
            $("#tipo_vehiculo_familia").val(data.tipo_vehiculo);
            $("#tipo_mascota_familia").val(data.tipo_mascota);
            $("#placa_persona_familia").val(data.placa);
            $("#observacion_persona_familia").val(data.observacion);

            if (data.dias) {
                var diasSeleccionado = data.dias.split(",");
                console.log('diasSeleccionado', diasSeleccionado);
                diasSeleccionado.forEach(dia => {
                    console.log('dia: ',dia);
                    $('#diaFamilia'+dia).prop('checked', true);
                });
            }
            $("#familiaFormModal").modal('show');
        });
        //ELIMINAR FAMILIA
        familia_table.on('click', '.drop-familia', function() {
            var trPorteria = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, familia_table);

            Swal.fire({
                title: 'Eliminar item de familia: '+data.nombre+'?',
                text: "No se podrá revertir!",
                type: 'warning',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Borrar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
                    $.ajax({
                        url: base_url + 'familia',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            familia_table.row(trPorteria).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Familia eliminada con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
        //DETALLE FAMILIA
        familia_table.on('click', '.detalle-imagen-familia', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, familia_table);

            if (data.propietario.logo_nit) {
                $("#preview_header_img_familia").attr("src",bucketUrl + data.propietario.logo_nit);
            } else if (data.usuario.avatar) {
                $("#preview_header_img_familia").attr("src",bucketUrl + data.usuario.avatar);
            } else {
                $("#preview_header_img_familia").attr("src", "/img/no-photo.jpg");
            }

            $("#textFamiliaPreview").text(data.propietario.nombre_completo);
            $("#familia-preview-ubicacion").text(data.propietario.apartamentos);

            var texto = 'CARRO';
            
            if (data.tipo_porteria == 0) texto = 'PROPIETARIO';
            if (data.tipo_porteria == 1) texto = 'FAMILIAR';
            if (data.tipo_porteria == 2) {
                texto = 'CANINO';
                if (data.tipo_mascota == 1) texto = 'FELINO';
                if (data.tipo_mascota == 2) texto = 'OTROS';
            };
            if (data.tipo_porteria == 3) {
                if (data.tipo_vehiculo == 1) texto = 'MOTO';
                if (data.tipo_vehiculo == 2) texto = 'MOTO ELECTRICA';
                if (data.tipo_vehiculo == 2) texto = 'BICICLETA ELECTRICA';
                if (data.tipo_vehiculo == 4) texto = 'OTROS';
                
            }
            if (data.tipo_porteria == 4) texto = 'VISITANTE';
            if (data.tipo_porteria == 5) texto = 'PAQUETE';
            if (data.tipo_porteria == 6) texto = 'DOMICILIO';

            $("#familia-preview-tipo").text(texto);
            $("#familia-preview-nombre").text(data.nombre ? data.nombre : data.placa);
            console.log('data: ',data);
            if (data.tipo_porteria == 1 || data.tipo_porteria == 2 || data.tipo_porteria == 3) {
                $("#familia-preview-autorizado").show();
                $("#familia-preview-noautorizado").hide();
            } else if (data.tipo_porteria == 4 || data.tipo_porteria == 0 || data.tipo_porteria == 5 || data.tipo_porteria == 6) {
                console.log('heee eca');
                var dayNow = (dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));
                var numeroDia = new Date(dayNow).getDay() + 1;
                var hoyDia = new Date(data.hoy).getDay();
                
                if (numeroDia == hoyDia) {
                    $("#familia-preview-autorizado").show();
                    $("#familia-preview-noautorizado").hide();
                }
        
                if (data.dias) {
                    var diasArray = data.dias.split(",");
                    if (diasArray.includes((numeroDia)+"")) {
                        $("#familia-preview-autorizado").show();
                        $("#familia-preview-noautorizado").hide();
                    }
                }
                
            }else {
                $("#familia-preview-autorizado").hide();
                $("#familia-preview-noautorizado").show();
            }
            
            if (data.archivos.length) {
                var texto = data.nombre;
                var img = bucketUrl+data.archivos[0].url_archivo;
                if (data.tipo_porteria == 3 || data.tipo_porteria == 4 && data.placa) texto = data.placa;

                $("#imagen-familia-preview").css("background-image", "url("+img+")");
                
                $("#familiaPreviewModal").modal('show');
            }
        });
    }

    $comboNitFamilia = $('#id_nit_familia').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        placeholder: "Seleccione una persona",
        language: {
            noResults: function() {
                return "No hay resultado";          
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: base_url_erp + 'nit/combo-nit',
            headers: headersERP,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboInmuebleFamilia = $('#id_inmueble_familia').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un inmueble",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/inmueble-combo-normal',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_nit: $('#id_nit_familia').val()
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            },
        }
    });

    $comboNitFamiliaFilter = $('#id_nit_familia_filter').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una persona",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";          
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: base_url_erp + 'nit/combo-nit',
            headers: headersERP,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $("#form-familia").submit(function(e) {
        e.preventDefault();
    
        var update = false;
        $("#saveFamilia").hide();
        $("#saveFamiliaLoading").show();
    
        if ($("#id_familia_up").val()) update = true;
    
        var ajxForm = document.getElementById("form-familia");
        var data = new FormData(ajxForm);
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "familia");
        xhr.send(data);
        xhr.onload = function(res) {

            var data = res.currentTarget;
            if (data.responseURL == 'https://maximoph.com/login') {
                caduqueSession();
                return;
            }
            // if (data.status > 299) {
            //     agregarToast('error', 'Ha ocurrido un error', 'Error '+data.status);
            //     return;
            // }

            var responseData = JSON.parse(res.currentTarget.response);
            $('#saveFamilia').show();
            $('#saveFamiliaLoading').hide();
    
            if (responseData.success) {
                agregarToast('exito', 'Datos cargados', 'Datos creados con exito!', true);
            } else {
                agregarToast('error', 'Carga errada', responseData.message);
                return;
            }
            
            familia_table.ajax.reload();
            $("#familiaFormModal").modal('hide');
        };
        xhr.onerror = function (res) {
            console.log(res);
            // agregarToast('error', 'Carga errada', 'errorsMsg');
            $('#saveFamilia').show();
            $('#saveFamiliaLoading').hide();
        };
    });

    familia_table.ajax.reload();
}

$(document).on('click', '#generateFamiliaNueva', function () {
    clearFormFamilia();
    $("#familiaFormModal").modal('show');
});

function clearFormFamilia() {

    $('#imagen_familia').val('');

    $("#textFamiliaCreate").show();
    $("#textFamiliaUpdate").hide();

    $("#id_familia_up").val("");
    $("#genero_familia").val("").change();
    $("#nombre_persona_familia").val("");
    $("#fecha_nacimiento_familia").val("");
    // $("#telefono_familia").val("");
    $("#email_familia").val("");
    $("#documento_persona_familia").val("");
    $("#tipo_vehiculo_familia").val("");
    $("#tipo_mascota_familia").val(0);
    $("#placa_persona_familia").val("");
    $("#id_nit_familia").val('').change();
    $("#tipo_familia_create").val(1).change();
    $('#id_inmueble_familia').val('').change();

    $('#id_inmueble_familia').prop('disabled', true);

    $("#observacion_persona_familia").val("");

    diaFamilia.forEach((dia, index) => {
        $('#'+dia).prop('checked', true);
    });

    changeTipoFamilia(1);

    $('.input-images-familia').imageUploader('destroy');
    $('.input-images-familia').empty();
    $('.input-images-familia').imageUploader({
        imagesInputName: 'photos',
        preloadedInputName: 'old',
        // maxSize: 2 * 1024 * 1024,
        maxFiles: 1
    });
}

$('#id_nit_familia').on('change', function(e) {
    var selectedValue = $(this).val();
    if (selectedValue) $('#id_inmueble_familia').prop('disabled', false);
    else $('#id_inmueble_familia').prop('disabled', true);
});

$(document).on('change', '#tipo_familia_create', function () {
    var tipoFamilia = $("#tipo_familia_create").val();
    hideInputFamilia();

    changeTipoFamilia(tipoFamilia);
});

function hideInputFamilia() {
    $("#input_dias_familia").hide();
    $("#input_tipo_mascota_familia").hide();
    $("#input_tipo_vehiculo_familia").hide();
    $("#input_placa_persona_familia").hide();
    $("#input_nombre_persona_familia").hide();
    $("#input_documento_persona_familia").hide();

    $("#input_genero_familia").hide();
    $("#input_fecha_inicio_familia").hide();
    // $("#input_telefono_familia").hide();
    $("#input_email_familia").hide();
}

function changeTipoFamilia(tipoFamilia) {

    if(parseInt(tipoFamilia) == 1) {
        $("#input_dias_familia").show();
        $("#input_tipo_vehiculo_familia").hide();
        $("#input_nombre_persona_familia").show();
        $("#input_documento_persona_familia").show();
        $("#input_genero_familia").show();
        $("#input_fecha_inicio_familia").show();
        $("#input_telefono_familia").show();
        $("#input_email_familia").show();
    } else if (parseInt(tipoFamilia) == 2) {
        $("#input_tipo_mascota_familia").show();
        $("#input_nombre_persona_familia").show();
        $("#input_documento_persona_familia").hide();
        $("#input_dias_familia").hide();
        $("#input_genero_familia").hide();
        $("#input_fecha_inicio_familia").hide();
        $("#input_telefono_familia").hide();
        $("#input_email_familia").hide();
        $("#input_tipo_vehiculo_familia").hide();
    } else if (parseInt(tipoFamilia) == 3) {
        $("#input_tipo_vehiculo_familia").show();
        $("#input_placa_persona_familia").show();
        $("#input_dias_familia").show();
    }
}

var dataImagenes = $('.input-images-familia').imageUploader({
    imagesInputName: 'photos',
    preloadedInputName: 'old',
    // maxSize: 2 * 1024 * 1024,
    maxFiles: 10
});

$(document).on('change', '#tipo_vehiculo_familia', function () {
    var tipoVehiculo = $("#tipo_vehiculo_familia").val();

    if (tipoVehiculo == '') $("#input_placa_persona_familia").hide();
    else $("#input_placa_persona_familia").show();
});

$(document).on('click', '#reloadFamilia', function () {
    $("#reloadFamiliaIconNormal").hide();
    $("#reloadFamiliaIconLoading").show();
    familia_table.ajax.reload(function (res) {
        $("#reloadFamiliaIconNormal").show();
        $("#reloadFamiliaIconLoading").hide();
    }); 
});

$(document).on('change', '#tipo_familia_filter', function () {
    familia_table.ajax.reload();
});

$(document).on('change', '#id_nit_familia_filter', function () {
    familia_table.ajax.reload();
});

$("#searchInputFamilia").on("input", function () {
    clearTimeout(searchTimeoutFamilia);
    searchTimeoutFamilia = setTimeout(function () {
        familia_table.ajax.reload();
    }, 300);
});

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});