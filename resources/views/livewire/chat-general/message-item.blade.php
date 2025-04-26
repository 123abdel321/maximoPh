<div class="{{ $mensaje->user_id == $usuario_id ? 'mensaje-estilo-derecha' : 'mensaje-estilo-izquierda' }}">
    @if ($mensaje->user_id != $usuario_id)
        @include('livewire.chat-general.message-sender')
    @endif
    @include('livewire.chat-general.message-content', ['mensaje' => $mensaje])
    @include('livewire.chat-general.message-timestamp', ['mensaje' => $mensaje])
</div>
