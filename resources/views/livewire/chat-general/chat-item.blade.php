<div wire:click="cargarMensajes({{ $chat->id }})" id="component-chat" class="component-chat" style="padding: 10px; cursor: pointer; display: flex; justify-content: space-between; border-bottom: solid 1px #14222a;">
    <div class="" style="padding-left: 0px;">
        @include('livewire.chat-general.chat-avatar')
    </div>
    <div class="" style="align-self: center; margin-right: auto; padding-left: 20px;">
        @include('livewire.chat-general.chat-info', ['chat' => $chat])
    </div>
    <div class="" style="align-self: center; padding-right: 0px;">
        @include('livewire.chat-general.chat-timestamp', ['chat' => $chat])
    </div>
</div>
