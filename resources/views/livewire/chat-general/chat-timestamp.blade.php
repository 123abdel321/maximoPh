@if ($chat->total_mensajes)
    <b style="font-size: 13px; color: #00a884;; font-weight: 700; text-wrap-mode: nowrap; float: inline-end;">{{ $chat->ultimo_mensaje->formatted_created_at }}</b><br/>
    <b style="font-size: 13px; color: #ffffff; font-weight: bold; background-color: #00a884; padding: 0px 6px 0px 6px; border-radius: 50px; float: right;">{{ $chat->total_mensajes }}</b>
@else
    <b class="ultimo-mensaje" style="font-size: 13px; color: #a2b0ce; font-weight: 500; align-self: start; margin-top: 5px; text-wrap-mode: nowrap; float: inline-end;">{{ $chat->ultimo_mensaje->formatted_created_at }}</b>
@endif
