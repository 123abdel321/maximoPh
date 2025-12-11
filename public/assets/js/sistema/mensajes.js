// let pondMensajes = null;
// let estadoBotones = false;

// const mensajeBody = document.getElementById("mensaje-body");
// const observer = new MutationObserver(() => {
//     scrollToBottom();
// });

// observer.observe(mensajeBody, { childList: true });

// function scrollToBottom() {
//     mensajeBody.scrollTop = mensajeBody.scrollHeight;
// }

// $(document).on('click', '#button-mensaje-chat', function () {
//     $('#input-mensaje-chat').val('');
// });

// $('#input-mensaje-chat').on('keydown', function(event) {
//     if (event.key === 'Enter') {
//         $('#button-mensaje-chat').click();
//     }
// });

// $(document).on('click', '#icon-open-actions', function () {
//     $("#container-actions").show();
//     $("#container-estados").hide();
//     setTimeout(function(){
//         scrollToBottom();
//     },400);
// });

// $(document).on('focus', '#input-search', function () {
//     $(".button-search").hide();
//     $(".button-clean-search").show();
// });

// $(document).on('blur', '#input-search', function () {
//     $(".button-search").show();
//     $(".button-clean-search").hide();
// });

// $(document).on('click', '#component-chat', function () {
//     $("#icon-open-actions").show();
//     $("#icon-close-actions").hide();
//     $("#container-actions").show();
//     $("#container-estados").hide();
// });

// $(document).on('click', '#icon-open-actions', function () {
//     $("#icon-open-actions").hide();
//     $("#icon-close-actions").show();
// });

// $(document).on('click', '#icon-close-actions', function () {
//     $("#icon-open-actions").show();
//     $("#icon-close-actions").hide();
// });

// $(document).on('click', '#button-action-estado-chat', function () {
//     $("#container-actions").hide();
//     $("#container-estados").show();
// });

// channelMensajeria.bind('notificaciones', function(data) {
//     var chatId = parseInt($("#id-mensaje-abierto").val());
//     console.log('channelMensajeria: ',data);

//     if (data.action == 'creacion_pqrsf') {//NUEVO PQRSF
//         if (data.permisos == 'mensajes pqrsf' && mensajePqrsf) {
//             Livewire.dispatch('cargarChats');
//             actualizarNumeroNotificaciones();
//             return;
//         }
//     }

//     if (data.action ==  'actualizar_entrega') {
//         if (chatId == data.chat_id) {
//             Livewire.dispatch('cargarMensajes', {chatId: chatId, observador: false});
//         }
//         actualizarNumeroNotificaciones();
//         return;
//     }

//     if (data.action ==  'creacion_mensaje') {
//         if (chatId == data.chat_id) {
//             if (data.user_id != id_usuario_logeado) {
//                 Livewire.dispatch('cargarMensajes', {chatId: chatId, observador: true});
//             }
//         } else {
//             // Livewire.dispatch('cargarMensajes', {chatId: chatId, observador: false});
//             Livewire.dispatch('cargarChats');
//         }
//         actualizarNumeroNotificaciones();
//         return;
//     }

//     if (data.action ==  'actualizar_estados') {
//         if (chatId == data.chat_id) {
//             Livewire.dispatch('cargarMensajes', {chatId: chatId, observador: false});
//         }
//     }    
// });

// channelMensajeriaPrivada.bind('notificaciones', function(data) {
//     console.log('channelMensajeriaPrivada: ',data);
//     var chatId = parseInt($("#id-mensaje-abierto").val());

//     if (data.action == 'creacion_turnos') {//NUEVO TURNO
//         if (data.permisos == 'mensajes turnos' && mensajeTurno) {
//             Livewire.dispatch('cargarChats');
//             actualizarNumeroNotificaciones();
//             return;
//         }
//     }

//     if (data.action ==  'actualizar_estados') {
//         if (chatId == data.chat_id) {
//             Livewire.dispatch('cargarMensajes', {chatId: chatId, observador: false});
//         }
//     }

//     if (data.action ==  'creacion_porteria') {
//         if (chatId == data.chat_id) {
//             Livewire.dispatch('cargarMensajes', {chatId: chatId, observador: false});
//         }
//     }
    
//     actualizarNumeroNotificaciones();
// });

// function actualizarNumeroNotificaciones() {
//     $.ajax({
//         url: base_url + 'notificaciones',
//         method: 'GET',
//         headers: headers,
//         dataType: 'json',
//     }).done((res) => {
//         if(res.success){
//             if (parseInt(res.total)) {
//                 $("#number_mensajes").text(res.total);
//                 $("#number_mensajes").show();
//             } else {
//                 $("#number_mensajes").text('');
//                 $("#number_mensajes").hide();
//             }
//         }
//     }).fail((res) => {
//     });
// }

// function initFilePondMensajes() {

//     pondMensajes = FilePond.create(document.querySelector('#chat-general-files'), {
//         allowImagePreview: true,
//         imagePreviewUpscale: true,
//         allowMultiple: true,
//         instantUpload: false,
//     });

//     $('.filepond--credits').remove();

//     pondMensajes.setOptions({
//         server: {
//             process: {
//                 url: '/archivos-cache',
//                 method: 'POST',
//                 headers: {
//                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                 },
//                 onload: (response) => {
//                 },
//                 onerror: (response) => {
//                     console.error('Error al subir la imagen: ', response);
//                 }
//             },
//             revert: {
//                 url: '/archivos-cache',
//                 method: 'DELETE',
//                 headers: {
//                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                 },
//             }
//         }
//     });

//     clearFilesInputMesanjes();
// }

// function clearFilesInputMesanjes() {
//     uploadedFilesPqrsf = [];
//     pondMensajes.off('removefile');
//     pondMensajes.removeFiles();
//     pondMensajes.on('removefile', (error, file) => {
//         if (error) {
//             console.error('Error al eliminar archivo:', error);
//             return;
//         }

//         const id = file.getMetadata('id');
//         const relationType = file.getMetadata('relation_type');

//         $.ajax({
//             url: base_url + 'archivo-general',
//             method: 'DELETE',
//             data: JSON.stringify({
//                 id: id,
//                 relationType: relationType
//             }),
//             headers: headers,
//             dataType: 'json',
//         }).done((res) => {
//         }).fail((res) => {
//             agregarToast('error', 'Eliminación errada', res.message);
//         });
//     });
// }

// $(document).on('click', '#iconNavbarChat', function () {
//     var chatId = parseInt($("#id-mensaje-abierto").val());
//     if (chatId) {
//         Livewire.dispatch('cargarMensajes', {chatId: chatId, observador: false});
//     }
//     actualizarNumeroNotificaciones();
// });

// $(document).on('click', '#butonActionActivoAction', function () {
//     actualizarEstadoRelationModule(0);
// });

// $(document).on('click', '.butonActionProcesoAction', function () {
//     actualizarEstadoRelationModule(1);
// });

// $(document).on('click', '.butonActionCerradoAction', function () {
//     actualizarEstadoRelationModule(2);
// });

// function actualizarEstadoRelationModule(estado) {
//     var chatId = parseInt($("#id-mensaje-abierto").val());
//     if (chatId) {
//         Livewire.dispatch('actualizarEstado', {chatId: chatId, estado: estado});
//         document.getElementById("icon-close-actions").click();
//     }
// }