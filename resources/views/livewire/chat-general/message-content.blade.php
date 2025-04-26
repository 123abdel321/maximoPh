@if (count($mensaje->archivos))
    @foreach ($mensaje->archivos as $archivo)
        @include('livewire.chat-general.message-attachment', ['archivo' => $archivo])
    @endforeach
@endif
<p class="{{ $mensaje->user_id == $usuario_id ? 'texto-mensaje-derecha' : 'texto-mensaje' }}">{!! $mensaje->content !!}</p>
