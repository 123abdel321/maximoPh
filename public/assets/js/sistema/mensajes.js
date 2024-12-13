$(document).on('click', '#button-mensaje-chat', function () {
    $('#input-mensaje-chat').val('');
});

$('#input-mensaje-chat').on('keydown', function(event) {
    if (event.key === 'Enter') {
        $('#button-mensaje-chat').click();
    }
});

$(document).on('click', '#component-chat', function () {
    finalSroll();
});

$(document).on('focus', '#input-search', function () {
    $(".button-search").hide();
    $(".button-clean-search").show();
});

$(document).on('blur', '#component-chat', function () {
    $(".button-search").show();
    $(".button-clean-search").hide();
});

channelMensajeria.bind('notificaciones', function(data) {
    var chatId = parseInt($("#id-mensaje-abierto").val());

    if (data.action == 'creacion_pqrsf') {//NUEVO PQRSF
        if (data.permisos == 'mensajes pqrsf' && mensajePqrsf) {
            Livewire.dispatch('cargarChats');
        }
    }
    if (data.action ==  'actualizar_entrega') {
        
        if (chatId == data.chat_id) {
            Livewire.dispatch('cargarMensajes', {chatId: chatId, observador: false});
        }
    }
    if (data.action ==  'creacion_mensaje') {
        if (chatId == data.chat_id) {
            Livewire.dispatch('cargarMensajes', {chatId: chatId, observador: true});
        } else {
            Livewire.dispatch('cargarChats');
        }
    }
    if (data.action ==  'actualizar_estados') {
        if (chatId == data.chat_id) {
            Livewire.dispatch('cargarMensajes', {chatId: chatId, observador: false});
        }
    }
    actualizarNumeroNotificaciones();
    finalSroll();
});

channelMensajeriaPrivada.bind('notificaciones', function(data) {
    var chatId = parseInt($("#id-mensaje-abierto").val());

    if (data.action ==  'actualizar_estados') {
        if (chatId == data.chat_id) {
            Livewire.dispatch('cargarMensajes', {chatId: chatId, observador: false});
        }
    }

    actualizarNumeroNotificaciones();
    finalSroll();
});

function actualizarNumeroNotificaciones() {
    var totalNotificaiones = $("#input-numero-notificaciones-chat").val();
    if (parseInt(totalNotificaiones)) {
        $("#number_mensajes").text(totalNotificaiones);
        $("#number_mensajes").show();
    } else {
        $("#number_mensajes").text('');
        $("#number_mensajes").hide();
    }
}

function finalSroll() {
    setTimeout(function(){
        document.getElementById("mensaje-body").scrollTop = 999999;
    },500);
}