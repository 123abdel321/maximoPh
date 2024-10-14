var import_inmuebles_table = null;
var channelImportadorInmuebles = pusher.subscribe('importador-inmuebles-'+localStorage.getItem("notificacion_code"));

function importinmueblesInit() {
    import_inmuebles_table = $('#importInmuebles').DataTable({
        pageLength: 20,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'inmuebles-cache-import',
        },
        'rowCallback': function(row, data, index){
            if (parseInt(data.estado)) {
                $('td', row).css('background-color', '#ffe5e5');
                return;
            }
        },
        columns: [
            {"data":'id', visible: false},
            {"data": function (row, type, set){
                if (!row.estado) {
                    return `<i class="fas fa-check-circle" style="color: #03b403; font-size: 14px;"></i>&nbsp;${row.id}`;
                }
                return `<i class="fas fa-minus-circle" style="color: red; font-size: 14px;"></i>&nbsp;${row.id}`;
            }},
            {"data":'nombre_inmueble'},
            {"data":'nombre_zona'},
            {"data":'area'},
            {"data":'coheficiente'},
            {"data":'nombre_concepto_facturacion'},
            {"data":'porcentaje_aumento', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'valor_aumento', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'numero_documento'},
            {"data":'nombre_nit'},
            {"data":'tipo_nit'},
            {"data":'porcentaje_administracion'},
            {"data":'valor_administracion', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'observacion'}
        ]
    });

    import_inmuebles_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            totalesInmueblesImport();
        }
    });

    var btnImportInmuebles = document.getElementById('actualizarPlantillaInmuebles');
    btnImportInmuebles.removeEventListener('click', handleInmuebleClick);
    btnImportInmuebles.addEventListener('click', handleInmuebleClick);

    $("#form-importador-inmuebles").submit(function(event) {
        event.preventDefault();
    
        $('#cargarPlantillaInmuebles').hide();
        $('#actualizarPlantillaInmuebles').hide();
        $('#cargarPlantillaInmueblesLoagind').show();
    
        import_inmuebles_table.rows().remove().draw();
    
        var ajxForm = document.getElementById("form-importador-inmuebles");
        var data = new FormData(ajxForm);
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "importinmuebles-importar");
        xhr.send(data);
        xhr.onload = function(res) {
    
            var data = res.currentTarget;
            if (data.responseURL == 'https://maximoph.com/login') {
                caduqueSession();
            }
            if (data.status > 299) {
                return;
            }
    
            var responseData = JSON.parse(res.currentTarget.response);
    
            if (responseData.success) {
                agregarToast('info', 'Cargando inmuebles', 'Se le notificará cuando la importación haya terminado!', true);
            } else {
                $('#cargarPlantillaInmuebles').show();
                $('#cargarPlantillaInmueblesLoagind').hide();
                agregarToast('error', 'Carga errada', 'errorsMsg');
            }
        };
        xhr.onerror = function (res) {
            $('#cargarPlantillaInmuebles').hide();
            $('#cargarPlantillaInmueblesLoagind').show();
        };
        return false;
    });
}

channelImportadorInmuebles.bind('notificaciones', function(data) {

    if (data.success) {
        $('#cargarPlantillaInmueblesLoagind').hide();

        if (data.accion == 1) {
            agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
            $('#cargarPlantillaInmuebles').show();
            $('#actualizarPlantillaInmuebles').show();
            import_inmuebles_table.ajax.reload(function(res) {
                if (res.success && res.data.length) {
                    totalesInmueblesImport();
                }
            });
        }

        if (data.accion == 2) {
            agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
            $('#cargarPlantillaInmuebles').show();
            import_inmuebles_table.ajax.reload(function(res) {
                if (res.success && res.data.length) {
                    totalesInmueblesImport();
                }
            });
        }
        
    } else {
        $('#cargarPlantillaInmuebles').show();
        $('#actualizarPlantillaInmuebles').hide();
        $('#cargarPlantillaInmueblesLoagind').hide();
        agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
    }
});

function handleInmuebleClick() {

    $('#cargarPlantillaInmuebles').hide();
    $('#actualizarPlantillaInmuebles').hide();
    $('#cargarPlantillaInmueblesLoagind').show();

    $.ajax({
        method: 'POST',
        url: base_url + 'inmuebles-cargar-import',
        headers: headers,
        data: JSON.stringify({actualizar_valores: $("input[type='checkbox']#actualizar_valores").is(':checked') ? '1' : '0'}),
        dataType: 'json',
    }).done((res) => {

        agregarToast('info', 'Importando inmuebles', 'Se le notificará cuando la importación haya terminado!', true);
    }).fail((err) => {

        $('#cargarPlantillaInmuebles').show();
        $('#cargarPlantillaInmueblesLoagind').hide();
        
        var errorsMsg = "";
        var mensaje = err.responseJSON.message;
        if(typeof mensaje  === 'object' || Array.isArray(mensaje)){
            for (field in mensaje) {
                var errores = mensaje[field];
                for (campo in errores) {
                    errorsMsg += "- "+errores[campo]+" <br>";
                }
            };
        } else {
            errorsMsg = mensaje
        }
        agregarToast('error', 'Importación errada', errorsMsg);
    });
}

function totalesInmueblesImport() {
    $.ajax({
        url: base_url + 'inmuebles-totales-import',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        if (res.data.errores + res.data.buenos > 0) {
            $('#totales_import_inmuebles').show();
        } else {
            $('#totales_import_inmuebles').hide();
        }
        if (res.data.buenos > 0) {
            $('#actualizarPlantillaInmuebles').show();
        }

        var countA = new CountUp('errores_inmuebles_import', 0, res.data.errores);
            countA.start();

        var countB = new CountUp('buenos_inmuebles_import', 0, res.data.buenos);
            countB.start();

        var countC = new CountUp('valores_inmuebles_import', 0, res.data.valores);
            countC.start();

    }).fail((err) => {
    });
}

$(document).on('click', '#descargarPlantillaInmuebles', function () {
    $.ajax({
        url: 'importinmuebles-exportar',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        window.open(res.url, "_blank");
    }).fail((err) => {
    });
});