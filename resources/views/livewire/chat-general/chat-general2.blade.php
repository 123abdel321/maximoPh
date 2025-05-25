<link href="{{ asset('assets/css/sistema/chat-general.css') }}" rel="stylesheet" />

<div class="offcanvas offcanvas-end" tabindex="-1" id="chatMaximo" aria-labelledby="chatMaximoLabel" style="background-color: #111b21;">
    @include('livewire.chat-general.header')
    @include('livewire.chat-general.chat-list')
    @include('livewire.chat-general.message-list')
    @include('livewire.chat-general.footer')
</div>