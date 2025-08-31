<div id="offcanvas-footer-mensajes container-chat" class="offcanvas-footer" style="{{ $mensajeActivoId ? '' : 'display: none !important;' }} padding: 10px; background-color: #202c33; display: flex; box-shadow: -2px -2px 2px rgb(0 0 0 / 20%), 0px 0px 0px rgb(0 0 0 / 10%);">
    @include('livewire.chat-general.footer-actions')
    <input id="input-mensaje-chat" wire:model="textoEscrito" class="form-control-no-upp" type="text" placeholder="Escribe un mensaje" style="background-color: #2a3942; border: 0px; color: #FFF; padding: 10px;">
    <i id="button-mensaje-chat" wire:click="enviarMensaje" class="fas fa-paper-plane icon-send"></i>
</div>
@include('livewire.chat-general.footer-collapse')