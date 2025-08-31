@if ($mensajes->relation_type == 12 || $mensajes->relation_type == 14)
    @canany(['mensajes pqrsf', 'mensajes turnos'])
        <div id="button-action-estado-chat" class="button-action-chat">
            <i class="fas fa-exchange-alt icon-action-chat turquoise"></i>
            <b style="color: white; font-weight: 400;">Estados</b>
        </div>
    @endcanany
@endif
@if ($mensajes->relation_type == 14)
    @if ($mensajes->relation_module->id_usuario == $usuario_id)
        @if ($mensajes->relation_module->estado == 0 || $mensajes->relation_module->estado == 3)
            <div id="button-action-iniciar-turno" class="button-action-chat butonActionProcesoAction">
                <i class="fas fa-stopwatch icon-action-chat" style="color: #fb8b40;"></i>
                <b style="color: white; font-weight: 400;">Iniciar</b>
            </div>
        @endif
        @if ($mensajes->relation_module->estado == 1)
            <div id="button-action-finalizar-turno" class="button-action-chat butonActionCerradoAction">
                <i class="fas fa-stop icon-action-chat" style="color: #2dcea3;"></i>
                <b style="color: white; font-weight: 400;">Finalizar</b>
            </div>
        @endif
    @endif
@endif
