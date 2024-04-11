var dataPorteria = [];
var diaPorteria = [
    "diaPorteria0",
    "diaPorteria1",
    "diaPorteria2",
    "diaPorteria3",
    "diaPorteria4",
    "diaPorteria5",
    "diaPorteria6",
    "diaPorteria7"
];
var searchValuePorteria = null;
var buscarDatosPorteria = false;

function porteriaInit() {
    loadItemsPorteria();
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});


$(document).on('click', '#generatePorteriaNueva', function () {
    clearFormConceptoFacturacion();
    $("#porteriaFormModal").modal('show');
});

$(document).on('change', '#tipo_porteria_create', function () {
    var tipoPorteria = $("#tipo_porteria_create").val();
    hideInputPorteria();
    if(parseInt(tipoPorteria) == 1) {
        $("#input_dias_porteria").show();
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_placa_persona_porteria").show();
        $("#input_nombre_persona_porteria").show();
    } else if (parseInt(tipoPorteria) == 2) {
        $("#input_tipo_mascota_porteria").show();
        $("#input_nombre_persona_porteria").show();
    } else if (parseInt(tipoPorteria) == 3) {
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_placa_persona_porteria").show();
    }
});

$(document).on('change', '#tipo_vehiculo_porteria', function () {
    var tipoVehiculo = $("#tipo_vehiculo_porteria").val();
    if(tipoVehiculo) {
        $("#input_placa_persona_porteria").show();
    } else {
        $("#input_placa_persona_porteria").hide();
    }
});

$("#form-porteria").submit(function(e) {
    e.preventDefault();

    var update = false;
    $("#savePorteria").hide();
    $("#savePorteriaLoading").show();

    if ($("#id_porteria_up").val()) update = true;

    var ajxForm = document.getElementById("form-porteria");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "porteria");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        $('#savePorteria').show();
        $('#savePorteriaLoading').hide();

        if (update) {
            var id = $("#id_porteria_up").val();
            document.getElementById('item_card_porteria_'+id).remove();
            var indexPorteria = dataPorteria.findIndex(item => item.id == id);
            dataPorteria.splice(indexPorteria,1);
        }

        dataPorteria.push(responseData.data);
        if (responseData.success) {
            createItemPorteria(responseData.data);
            agregarToast('exito', 'Datos cargados', 'Datos creados con exito!', true);
        } else {
            agregarToast('error', 'Carga errada', 'errorsMsg');
        }

        $("#porteriaFormModal").modal('hide');
    };
    xhr.onerror = function (res) {
        agregarToast('error', 'Carga errada', 'errorsMsg');
        $('#savePorteria').show();
        $('#savePorteriaLoading').hide();
    };
});

function searchPorteria (event) {
    if (event.keyCode == 20 || event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18) {
        return;
    }
    var botonPrecionado = event.key.length == 1 ? event.key : '';
    searchValuePorteria = $('#searchInputPorteria').val();
    searchValuePorteria = searchValuePorteria+botonPrecionado;
    if(event.key == 'Backspace') searchValuePorteria = searchValuePorteria.slice(0, -1);

    loadItemsPorteria();
}

function loadItemsPorteria() {
    $("#loading-porteria").show();
    document.getElementById('items-card-porteria').innerHTML = "";

    if (buscarDatosPorteria) {
        buscarDatosPorteria.abort();
    }

    buscarDatosPorteria = $.ajax({
        url: base_url + 'porteria',
        method: 'GET',
        data: {
            search: searchValuePorteria
        },
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $("#loading-porteria").hide();
        buscarDatosPorteria = false;
        if(res.success){
            dataPorteria = res.data;
            res.data.forEach(data => {
                createItemPorteria(data);
            });
        }
    }).fail((err) => {
        buscarDatosPorteria = false;
    });
}

function readURLPorteria(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            newImgProfile = e.target.result;
            $('#imagen_porteria').attr('src', e.target.result);
            $('#new_avatar_porteria').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_avatar_porteria').hide();
        $('#new_avatar_porteria').show();
    }
}

function createItemPorteria(porteria) {
    var tipoPorteria = '';
    var colorTipo = '';
    var imagen = bucketUrl + 'logos_empresas/no-photo.jpg';
    if (porteria.tipo_porteria == 1) {
        tipoPorteria = 'Persona';
        colorTipo = '#59bded';
    } else if (porteria.tipo_porteria == 2) {
        tipoPorteria = 'Mascota';
        colorTipo = '#4cd361';
    } else if (porteria.tipo_porteria == 3) {
        tipoPorteria = 'Vehiculo';
        colorTipo = '#f75c5c';
    }

    if (porteria.archivos.length > 0) {
        imagen = bucketUrl + porteria.archivos[0].url_archivo;
    }

    var html = `
        <div class="card card-item-porteria" style="margin-bottom: 10px; height: 100%; overflow: hidden;" onclick="editarItemPorteria(${porteria.id})">
            <img style="height: 140px; object-fit: cover; object-position: top;" class="card-img-top img-porteria" src="${imagen}" alt="name_unique">
            <div class="ribbon" style="background-color: ${colorTipo};">${tipoPorteria}</div>
            <div class="card-body" style="align-content: center; ">
                ${autorizado(porteria)}
                <p class="text-max-line-2" style="font-size: 12px; color: black; text-align: -webkit-center; font-weight: 600; margin-bottom: 0px;">${porteria.nombre}</p>
                ${placaVehiculo(porteria)}
                ${observacionPorteria(porteria)}
            </div>
        </div>
    `;

    var item = document.createElement('div');
    item.setAttribute("class", "col-12 col-sm-4 col-md-4 col-lg-3 col-xl-2");
    item.setAttribute("style", "padding-bottom: 20px;");
    item.setAttribute("id", "item_card_porteria_"+porteria.id);
    item.innerHTML = [
        html
    ].join('');
    document.getElementById('items-card-porteria').insertBefore(item, null);
}

function autorizado(porteria) {
    if (porteria.tipo_porteria == 1) {
        var dayNow = (dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));
        var numeroDia = new Date(dayNow).getDay();
        var diasArray = porteria.dias.split(",");

        if (numeroDia == new Date(porteria.hoy).getDay()) {
            return `<span class="badge badge-sm bg-gradient-success status-autorizado-position">AUTORIZADO</span>`;
        }
        if (diasArray.includes((numeroDia+1)+"")) {
            return `<span class="badge badge-sm bg-gradient-success status-autorizado-position">AUTORIZADO</span>`;
        }
        return `<span class="badge badge-sm bg-gradient-danger status-autorizado-position">NO AUTORIZADO</span>`;
    }
    return ``;
}

function placaVehiculo(porteria) {
    if (porteria.placa) {
        return `<p style="font-size: 12px; color: black; text-align: -webkit-center; font-weight: 600; margin-bottom: 0px;">${porteria.placa}</p>`
    }
    return ``;
}

function observacionPorteria(porteria) {
    if (porteria.observacion) {
        return `<p class="text-max-line-2" style="margin-top: 5px; font-size: 12px; color: black; text-align: -webkit-center; margin-bottom: 0px;">${porteria.observacion}</p>`
    }
    return ``;
}

function clearFormConceptoFacturacion() {
    $('#new_avatar_porteria').hide();
    $('#default_avatar_porteria').show();
    $('#default_avatar_porteria').attr('src', '/img/add-imagen.png');

    $("id_porteria_up").val("");
    $("#tipo_porteria_create").val(1);
    $("#nombre_persona_porteria").val("");
    $("#tipo_vehiculo_porteria").val("");
    $("#tipo_mascota_porteria").val(0);
    $("#placa_persona_porteria").val("");
    $("#observacion_persona_porteria").val("");

    diaPorteria.forEach(dia => {
        $('#'+dia).prop('checked', false);
    });

    $("#input_dias_porteria").show();
    $("#input_tipo_vehiculo_porteria").show();
    $("#input_tipo_mascota_porteria").hide();
}

function hideInputPorteria() {
    $("#input_dias_porteria").hide();
    $("#input_tipo_mascota_porteria").hide();
    $("#input_tipo_vehiculo_porteria").hide();
    $("#input_placa_persona_porteria").hide();
    $("#input_nombre_persona_porteria").hide();
}

function editarItemPorteria(id) {
    hideInputPorteria();

    var indexPorteria = dataPorteria.findIndex(item => item.id == id);
    var itemPorteria = dataPorteria[indexPorteria];

    if (itemPorteria.archivos.length) {
        $('#default_avatar_porteria').attr('src', bucketUrl+itemPorteria.archivos[0].url_archivo);
    } else {
        $('#default_avatar_porteria').attr('src', '/img/add-imagen.png');
    }
    
    if(parseInt(itemPorteria.tipo_porteria) == 1) {
        $("#input_dias_porteria").show();
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_placa_persona_porteria").show();
        $("#input_nombre_persona_porteria").show();
    } else if (parseInt(itemPorteria.tipo_porteria) == 2) {
        $("#input_tipo_mascota_porteria").show();
        $("#input_nombre_persona_porteria").show();
    } else if (parseInt(itemPorteria.tipo_porteria) == 3) {
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_placa_persona_porteria").show();
    }

    $("#id_porteria_up").val(id);
    $("#tipo_porteria_create").val(itemPorteria.tipo_porteria);
    $("#nombre_persona_porteria").val(itemPorteria.nombre);
    $("#tipo_vehiculo_porteria").val(itemPorteria.tipo_vehiculo);
    $("#tipo_mascota_porteria").val(itemPorteria.tipo_mascota);
    $("#placa_persona_porteria").val(itemPorteria.placa);
    $("#observacion_persona_porteria").val(itemPorteria.observacion);

    var diasSeleccionado = itemPorteria.dias.split(",");

    diasSeleccionado.forEach(dia => {
        $('#diaPorteria'+dia).prop('checked', true);
    });

    $("#porteriaFormModal").modal('show');
}