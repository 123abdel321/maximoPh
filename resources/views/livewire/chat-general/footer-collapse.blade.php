<div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
    <div class="container accordion-body">
        <div id="container-actions" style="display: flex; gap: 10px;">
            @if ($mensajes)
                @include('livewire.chat-general.footer-action-buttons')
            @endif
            <div id="button-action-imagen-chat" class="button-action-chat">
                <i class="fas fa-image icon-action-chat springgreen"></i>
                <b style="color: white; font-weight: 400;">Archivos</b>
            </div>
        </div>
        <div id="container-estados" style="text-align: center;">
            <b class="nombre-container-estados">Acciones</b><br/>
            @if ($mensajes)
                @include('livewire.chat-general.footer-action-states')
            @endif
        </div>
    </div>
</div>
