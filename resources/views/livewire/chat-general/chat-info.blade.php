@if ($chat->total_mensajes)
    <b style="font-size: 14px; color: white; font-weight: 600; padding-right: 0px; padding-left: 0px;">
        {{ $chat->nombre }}
        @if ($chat->responsable && $chat->responsable->id != $usuario_id)
            - {{ $chat->responsable->firstname }} {{ $chat->responsable->lastname }}
        @endif
    </b><br/>
    <b class="ultimo-mensaje">{!! $chat->ultimo_mensaje->content !!}</b>
@else
    <b style="font-size: 14px; color: white; font-weight: 400; padding-right: 0px; padding-left: 0px;">
        {{ $chat->nombre }}
        @if ($chat->responsable && $chat->responsable->id != $usuario_id)
            - {{ $chat->responsable->firstname }} {{ $chat->responsable->lastname }}
        @endif
    </b><br/>
    <b class="ultimo-mensaje">{!! $chat->ultimo_mensaje->content !!}</b>
@endif
