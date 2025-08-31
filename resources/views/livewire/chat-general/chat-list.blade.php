<div style="{{ $mensajeActivoId ? 'display: none !important;' : '' }} padding: 15px;">
    @include('livewire.chat-general.search-bar')
    @include('livewire.chat-general.filter-buttons')
</div>
<div id="chat-body" class="offcanvas-body wrapper" style="{{ $mensajeActivoId ? 'display: none !important;' : '' }} contain: content; padding: 0px;">
    @forelse($chats as $chat)
        @include('livewire.chat-general.chat-item', ['chat' => $chat])
    @empty
        <h3 style="height: 90%; place-content: center; place-self: center; color: #8b9c9c;">SIN RESULTADOS</h3>
    @endforelse
</div>
