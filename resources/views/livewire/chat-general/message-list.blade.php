<input id="input-numero-notificaciones-chat" class="form-control" value="{{ $numeroNotificaciones }}" type="text" style="display: none;">
<input id="id-mensaje-abierto" value="{{ $mensajeActivoId }}" style="display: none;">
<div class="" style="{{ $mensajeActivoId ? '' : 'display: none !important;' }} padding: 10px; background-color: #202c33; box-shadow: 0px 0px 0px rgba(50, 50, 93, 0.1), 3px 3px 2px rgb(0 0 0 / 21%); display: flex;">
    <i wire:click="volverChat()" style="font-size: 17px; color: white; align-content: center; padding: 10px; cursor: pointer;" class="fas fa-chevron-left"></i>
    @include('livewire.chat-general.chat-header')
</div>
<div id="mensaje-body" class="offcanvas-body wrapper" style="{{ $mensajeActivoId ? '' : 'display: none !important;' }}">
    @if ($mensajes)
        @foreach($mensajes->mensajes as $mensaje)
            @include('livewire.chat-general.message-item', ['mensaje' => $mensaje])
        @endforeach
    @endif
</div>
